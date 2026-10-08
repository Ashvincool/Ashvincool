# Keshav Technosys CRM — PHP + MySQL version

Same UI as `../crm`, but data is stored in MySQL and access needs a login, so a whole team can share it.
Works on any shared hosting with PHP 8.1+ and MySQL/MariaDB (PDO).

## Install
1. Create a MySQL database and import `schema.sql` (phpMyAdmin → Import).
2. Copy `config.sample.php` to `config.php` and enter your DB details.
3. Make a password hash and paste it as `admin_hash`:
   `php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT);"`
4. Upload this folder to your host (e.g. `public_html/crm/`) and open `/crm/login.php`.
5. Use HTTPS on the live site.

Files: `login.php` (login/logout), `index.php` (UI), `api.php` (JSON API, login + CSRF protected, table/column whitelist), `bootstrap.php` (config, DB, session).
