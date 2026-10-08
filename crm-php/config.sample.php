<?php
// Copy this file to config.php and fill in your values. Never commit config.php.
return [
    'db_dsn'  => 'mysql:host=localhost;dbname=keshav_crm;charset=utf8mb4',
    'db_user' => 'your_db_user',
    'db_pass' => 'your_db_password',
    // Generate with:  php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT);"
    'admin_user' => 'admin',
    'admin_hash' => 'PASTE_PASSWORD_HASH_HERE',
];
