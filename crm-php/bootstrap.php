<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

function cfg(): array {
    static $c;
    if (!$c) {
        $f = getenv('CRM_CONFIG') ?: __DIR__ . '/config.php';
        if (!is_file($f)) { http_response_code(500); exit('Missing config.php — copy config.sample.php to config.php.'); }
        $c = require $f;
    }
    return $c;
}

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = cfg();
        $pdo = new PDO($c['db_dsn'], $c['db_user'] ?? null, $c['db_pass'] ?? null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}
