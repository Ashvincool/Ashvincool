<?php
require __DIR__ . '/bootstrap.php';
require_login(true);
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function out($data, int $code = 200): never { http_response_code($code); echo json_encode($data); exit; }

// Whitelist of tables and writable columns — the only names ever put into SQL.
// For deals the client sends "stage" (a name); it is stored as stage_id.
const SCHEMA = [
    'companies'  => ['name','website','industry','phone','city','notes'],
    'contacts'   => ['first_name','last_name','email','phone','job_title','company_id','status','source','notes'],
    'deals'      => ['title','company_id','contact_id','stage_id','amount','close'],
    'activities' => ['type','subject','contact_id','deal_id','due','description','done'],
];
const NULLABLE = ['company_id','contact_id','deal_id','close','due'];

$pdo = db();
$q = fn(string $c) => '`' . $c . '`';
$stages = $pdo->query('SELECT id, name, position, is_won, is_lost FROM pipeline_stages ORDER BY position')->fetchAll();
$stageByName = array_column($stages, null, 'name');
$stageById = array_column($stages, null, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $all = ['stages' => array_column($stages, 'name'), 'me' => ['id' => $_SESSION['uid'], 'name' => $_SESSION['name'], 'role' => $_SESSION['role']]];
    $all['users'] = array_column($pdo->query('SELECT id, name FROM users')->fetchAll(), 'name', 'id');
    foreach (SCHEMA as $t => $cols) {
        $rows = $pdo->query("SELECT id, " . implode(',', array_map($q, $cols)) . ", owner_id, created FROM $t ORDER BY created, id")->fetchAll();
        foreach ($rows as &$r) {
            foreach ($r as $k => $v) $r[$k] = $v ?? '';
            if (isset($r['amount'])) $r['amount'] = (float)$r['amount'];
            if (isset($r['done'])) $r['done'] = (bool)$r['done'];
            if (isset($r['stage_id'])) { $r['stage'] = $stageById[$r['stage_id']]['name'] ?? ''; unset($r['stage_id']); }
        }
        $all[$t] = $rows;
    }
    out($all);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['error' => 'Method not allowed'], 405);
if (!hash_equals(csrf(), $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) out(['error' => 'Bad CSRF token'], 403);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$table = $in['table'] ?? '';
if (!isset(SCHEMA[$table])) out(['error' => 'Unknown table'], 400);
$cols = SCHEMA[$table];

if (($in['action'] ?? '') === 'delete') {
    // Admins can delete anything; others only records they own (or unowned ones).
    $sql = "DELETE FROM $table WHERE id = ?" . (is_admin() ? '' : ' AND (owner_id = ? OR owner_id IS NULL)');
    $args = [(string)($in['id'] ?? '')];
    if (!is_admin()) $args[] = $_SESSION['uid'];
    $st = $pdo->prepare($sql); $st->execute($args);
    if (!$st->rowCount() && !is_admin()) out(['error' => 'Only the owner or an admin can delete this'], 403);
    out(['ok' => true]);
}

if (($in['action'] ?? '') === 'save') {
    $rec = $in['record'] ?? [];
    $id = (string)($rec['id'] ?? '');
    if (!preg_match('/^[a-z0-9]{1,24}$/', $id)) out(['error' => 'Bad id'], 400);

    if ($table === 'deals') {
        $s = $stageByName[(string)($rec['stage'] ?? '')] ?? null;
        if (!$s) out(['error' => 'Bad stage'], 422);
        $rec['stage_id'] = $s['id'];
    }
    $vals = [];
    foreach ($cols as $c) {
        $v = $rec[$c] ?? null;
        if ($c === 'done') $v = $v ? 1 : 0;
        elseif ($c === 'amount') $v = is_numeric($v) ? (float)$v : 0;
        elseif ($c === 'stage_id') $v = (int)$v;
        elseif (in_array($c, NULLABLE, true)) $v = ($v === '' || $v === null) ? null : (string)$v;
        else $v = trim((string)$v);
        $vals[$c] = $v;
    }
    foreach (['name','first_name','title','subject'] as $req)
        if (array_key_exists($req, $vals) && $vals[$req] === '') out(['error' => "$req is required"], 422);

    try {
        $exists = $pdo->prepare("SELECT 1 FROM $table WHERE id = ?");
        $exists->execute([$id]);
        if ($exists->fetchColumn()) {
            $set = implode(',', array_map(fn($c) => $q($c) . ' = ?', $cols));
            $pdo->prepare("UPDATE $table SET $set WHERE id = ?")->execute([...array_values($vals), $id]);
        } else {
            $names = implode(',', array_map($q, ['id', ...$cols, 'owner_id']));
            $marks = implode(',', array_fill(0, count($cols) + 2, '?'));
            $pdo->prepare("INSERT INTO $table ($names) VALUES ($marks)")->execute([$id, ...array_values($vals), $_SESSION['uid']]);
        }
        if ($table === 'deals') {   // stamp closed_at when a deal reaches Won/Lost, clear it otherwise
            $s = $stageById[$vals['stage_id']];
            if ($s['is_won'] || $s['is_lost'])
                $pdo->prepare('UPDATE deals SET closed_at = COALESCE(closed_at, ?) WHERE id = ?')->execute([date('Y-m-d H:i:s'), $id]);
            else
                $pdo->prepare('UPDATE deals SET closed_at = NULL WHERE id = ?')->execute([$id]);
        }
    } catch (PDOException $e) {
        out(['error' => 'Could not save (check linked records exist)'], 422);
    }
    out(['ok' => true]);
}
out(['error' => 'Unknown action'], 400);
