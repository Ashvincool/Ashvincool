<?php
// One-time setup: creates the first admin user. Refuses to run once any user exists.
require __DIR__ . '/bootstrap.php';
$pdo = db();
$locked = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
$msg = '';
if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pass = $_POST['pass'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) {
        $msg = 'Enter a name, a valid email, and a password of at least 10 characters.';
    } else {
        $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)')
            ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), 'admin']);
        header('Location: login.php'); exit;
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CRM setup</title>
<style>body{font:15px system-ui,sans-serif;background:#f5f6f8;display:grid;place-items:center;min-height:100vh;margin:0}
form,.box{background:#fff;padding:28px;border-radius:14px;border:1px solid #e3e6ec;width:min(380px,92vw);display:grid;gap:12px}
h1{font-size:18px;margin:0}input{padding:10px;border:1px solid #e3e6ec;border-radius:8px;font:inherit}
button{padding:10px;border:0;border-radius:8px;background:#2f5bea;color:#fff;font:inherit;cursor:pointer}.e{color:#dc2626;font-size:13px}</style></head><body>
<?php if ($locked): ?>
<div class="box"><h1>Setup already done</h1><p>An admin exists. Delete <code>setup.php</code> from the server, then <a href="login.php">log in</a>.</p></div>
<?php else: ?>
<form method="post"><h1>Create the first admin</h1>
<?php if ($msg): ?><div class="e"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<input name="name" placeholder="Your name" required><input name="email" type="email" placeholder="Email" required>
<input name="pass" type="password" placeholder="Password (min 10 characters)" required minlength="10"><button>Create admin</button></form>
<?php endif; ?></body></html>
