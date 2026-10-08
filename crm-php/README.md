# Keshav Technosys CRM — PHP + MySQL version

Shared team CRM: MySQL storage, per-user logins, owners on every record.
Needs PHP 8.1+ and MySQL 5.7+/MariaDB 10.3+ (PDO). Works on shared hosting.

## Install
1. Create an **empty** MySQL database and import `schema.sql` (phpMyAdmin → Import). It creates 6 tables.
2. Copy `config.sample.php` to `config.php` and enter your DB details.
3. Upload this folder (e.g. `public_html/crm/`) and open `/crm/setup.php` to create the first admin. Then **delete `setup.php`** from the server.
4. Log in at `/crm/login.php`. Admins get a **Team** link to add or disable users.
5. Use HTTPS on the live site.

## Database
| Table | Purpose |
|---|---|
| `users` | team logins (role `admin` or `sales`, can be disabled) |
| `pipeline_stages` | deal stages with order, win probability, won/lost flags |
| `companies`, `contacts` | accounts and people (status, source, notes) |
| `deals` | linked to company/contact and a stage; `closed_at` is set automatically on Won/Lost |
| `activities` | calls, emails, meetings, notes, tasks with due date and done flag |

Every record has `owner_id` (who created it), `created` and `updated_at`. Sales users can delete only their own records; admins can delete any.
