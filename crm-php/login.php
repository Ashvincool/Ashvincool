<?php
require __DIR__ . '/bootstrap.php';
$err = '';
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sleep(1); // slows down password guessing
    $st = db()->prepare('SELECT id, name, role, password_hash FROM users WHERE email = ? AND is_active = 1');
    $st->execute([trim($_POST['email'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['pass'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['name'] = $u['name'];
        $_SESSION['role'] = $u['role'];
        header('Location: index.php'); exit;
    }
    $err = 'Wrong email or password.';
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Keshav Technosys CRM — Login</title>
<style>body{font:15px system-ui,sans-serif;background:#f5f6f8;display:grid;place-items:center;min-height:100vh;margin:0}
form{background:#fff;padding:28px;border-radius:14px;border:1px solid #e3e6ec;width:min(340px,92vw);display:grid;gap:12px}
h1{font-size:18px;margin:0}input{padding:10px;border:1px solid #e3e6ec;border-radius:8px;font:inherit}
button{padding:10px;border:0;border-radius:8px;background:#2f5bea;color:#fff;font:inherit;cursor:pointer}.e{color:#dc2626;font-size:13px}</style></head>
<body><form method="post"><h1>Keshav Technosys CRM</h1>
<?php if ($err): ?><div class="e"><?= htmlspecialchars($err) ?></div><?php endif; ?>
<input name="email" type="email" placeholder="Email" autocomplete="username" required autofocus>
<input name="pass" type="password" placeholder="Password" autocomplete="current-password" required>
<button>Log in</button></form></body></html>
