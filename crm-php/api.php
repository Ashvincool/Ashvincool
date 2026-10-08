<?php
require __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function out($data, int $code = 200): never { http_response_code($code); echo json_encode($data); exit; }

if (empty($_SESSION['user'])) out(['error' => 'Not logged in'], 401);

// Whitelist of tables and columns — the only names ever put into SQL.
const SCHEMA = [
    'companies'  => ['name','website','industry','phone','city','notes'],
    'contacts'   => ['first_name','last_name','email','phone','job_title','company_id','status','source','notes'],
    'deals'      => ['title','company_id','contact_id','stage','amount','close'],
    'activities' => ['type','subject','contact_id','deal_id','due','description','done'],
];
const NULLABLE = ['company_id','contact_id','deal_id','close','due'];
const STAGES = ['New','Qualified','Proposal','Negotiation','Won','Lost'];

$pdo = db();
$q = fn(string $c) => '`' . $c . '`';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $all = [];
    foreach (SCHEMA as $t => $cols) {
        $rows = $pdo->query("SELECT id, " . implode(',', array_map($q, $cols)) . ", created FROM $t ORDER BY created, id")->fetchAll();
        foreach ($rows as &$r) {
            foreach ($r as $k => $v) $r[$k] = $v ?? '';
            if (isset($r['amount'])) $r['amount'] = (float)$r['amount'];
            if (isset($r['done'])) $r['done'] = (bool)$r['done'];
        }
        $all[$t] = $rows;
    }
    out($all);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['error' => 'Method not allowed'], 405);
if (!hash_equals(csrf(), $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) out(['error' => 'Bad CSRF token'], 403);

$in = json_decode(file_get_contents('php://input'), true);
$table = $in['table'] ?? '';
if (!isset(SCHEMA[$table])) out(['error' => 'Unknown table'], 400);
$cols = SCHEMA[$table];

if (($in['action'] ?? '') === 'delete') {
    $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([(string)($in['id'] ?? '')]);
    out(['ok' => true]);
}

if (($in['action'] ?? '') === 'save') {
    $rec = $in['record'] ?? [];
    $id = (string)($rec['id'] ?? '');
    if (!preg_match('/^[a-z0-9]{1,24}$/', $id)) out(['error' => 'Bad id'], 400);

    $vals = [];
    foreach ($cols as $c) {
        $v = $rec[$c] ?? null;
        if ($c === 'done') $v = $v ? 1 : 0;
        elseif ($c === 'amount') $v = is_numeric($v) ? (float)$v : 0;
        elseif (in_array($c, NULLABLE, true)) $v = ($v === '' || $v === null) ? null : (string)$v;
        else $v = trim((string)$v);
        $vals[$c] = $v;
    }
    foreach (['name','first_name','title','subject'] as $req)
        if (array_key_exists($req, $vals) && $vals[$req] === '') out(['error' => "$req is required"], 422);
    if (isset($vals['stage']) && !in_array($vals['stage'], STAGES, true)) out(['error' => 'Bad stage'], 422);

    try {
        $exists = $pdo->prepare("SELECT 1 FROM $table WHERE id = ?");
        $exists->execute([$id]);
        if ($exists->fetchColumn()) {
            $set = implode(',', array_map(fn($c) => $q($c) . ' = ?', $cols));
            $pdo->prepare("UPDATE $table SET $set WHERE id = ?")->execute([...array_values($vals), $id]);
        } else {
            $names = implode(',', array_map($q, ['id', ...$cols]));
            $marks = implode(',', array_fill(0, count($cols) + 1, '?'));
            $pdo->prepare("INSERT INTO $table ($names) VALUES ($marks)")->execute([$id, ...array_values($vals)]);
        }
    } catch (PDOException $e) {
        out(['error' => 'Could not save (check linked records exist)'], 422);
    }
    out(['ok' => true]);
}
out(['error' => 'Unknown action'], 400);
