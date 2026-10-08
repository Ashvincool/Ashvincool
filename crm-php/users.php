<?php
// Admin-only team management: add users, activate/deactivate.
require __DIR__ . '/bootstrap.php';
require_login();
if (!is_admin()) { http_response_code(403); exit('Admins only.'); }
$pdo = db(); $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF token'); }
    if (($_POST['do'] ?? '') === 'add') {
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pass = $_POST['pass'] ?? '';
        $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'sales';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) $msg = 'Need a name, valid email and a password of 10+ characters.';
        else try {
            $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)')->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);
            $msg = 'User added.';
        } catch (PDOException $e) { $msg = 'That email is already used.'; }
    } elseif (($_POST['do'] ?? '') === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === $_SESSION['uid']) $msg = "You can't deactivate yourself.";
        else $pdo->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    }
}
$users = $pdo->query('SELECT id,name,email,role,is_active FROM users ORDER BY id')->fetchAll();
$h = fn($s) => htmlspecialchars((string)$s);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Team — CRM</title>
<style>body{font:14px system-ui,sans-serif;background:#f5f6f8;margin:0;padding:24px;color:#1c2330}.w{max-width:760px;margin:auto;display:grid;gap:16px}
table,form{background:#fff;border:1px solid #e3e6ec;border-radius:12px;width:100%;border-collapse:collapse}th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #e3e6ec}
form{padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px}input,select{padding:9px;border:1px solid #e3e6ec;border-radius:8px;font:inherit}
button{padding:8px 14px;border:0;border-radius:8px;background:#2f5bea;color:#fff;font:inherit;cursor:pointer}td button{background:#eee;color:#222;padding:4px 10px}.m{color:#16a34a}</style></head><body><div class="w">
<div><a href="index.php">← Back to CRM</a></div><h2 style="margin:0">Team</h2>
<?php if ($msg): ?><div class="m"><?= $h($msg) ?></div><?php endif; ?>
<table><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
<?php foreach ($users as $u): ?><tr><td><?= $h($u['name']) ?></td><td><?= $h($u['email']) ?></td><td><?= $h($u['role']) ?></td><td><?= $u['is_active'] ? 'Active' : 'Disabled' ?></td>
<td><form method="post" style="all:unset"><input type="hidden" name="csrf" value="<?= $h(csrf()) ?>"><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button><?= $u['is_active'] ? 'Disable' : 'Enable' ?></button></form></td></tr><?php endforeach; ?></table>
<form method="post"><input type="hidden" name="csrf" value="<?= $h(csrf()) ?>"><input type="hidden" name="do" value="add">
<input name="name" placeholder="Name" required><input name="email" type="email" placeholder="Email" required>
<input name="pass" type="password" placeholder="Password (10+ chars)" minlength="10" required><select name="role"><option value="sales">Sales</option><option value="admin">Admin</option></select>
<button style="grid-column:1/-1">Add user</button></form></div></body></html>
