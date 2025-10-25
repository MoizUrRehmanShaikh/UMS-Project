## Quick orientation

This repository is a small PHP-based University Management System intended to run under XAMPP/Apache + MySQL. There is no JS build step or composer usage — files are served directly by PHP. Open the app in your browser (e.g. http://localhost/ums_project/).

Keep changes minimal and follow the project's existing simple conventions (procedural PHP, mysqli prepared statements, sessions for auth).

## Quick start / dev server

- Use XAMPP (Apache + MySQL). The project expects MySQL on port 3307 by default (see `config/db.php`).
- Import `sql.sql` into your MySQL server to create the schema and sample data.
- If you need an admin account quickly, open `config/admin_setup.php` in the browser once (it prints credentials), then DELETE the file for security.

## Big-picture architecture

- Single-host, monolithic PHP app. No separate API server or SPA.
- Public pages (index, about, login, register) are in repo root.
- Admin pages live under `admin/` and use `../config/db.php` to connect.
- Role-specific dashboards live in `public/` (`student_dashboard.php`, `teacher_dashboard.php`).
- Database access is via `config/db.php` using procedural `mysqli` and prepared statements.

Data flow summary
- Registration (`register.php`) creates a `users` row and then a role-specific row in `students` or `teachers` inside a transaction. See `register.php` for exact SQL and bind order.
- Login (`login.php` / `admin/admin_login.php`) uses `password_verify()` against `password_hash()` values stored in `users.password_hash` and sets session variables: `user_id`, `username`, `role`.
- Role checks: pages enforce role via `if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'expected_role') { header('Location: ...'); exit(); }` pattern (see `admin/admin_dashboard.php`, `public/*`).

## Project-specific conventions and patterns

- Session-first: each page that relies on auth calls `session_start()` at the top.
- DB include: code uses `include 'config/db.php'` (root pages) or `include '../config/db.php'` (admin/public pages). Keep relative paths consistent when moving files.
- Prepared statements with `bind_param` are used throughout — continue this pattern rather than switching to different DB APIs.
- Passwords use `password_hash()` and `password_verify()`; do not revert to plain-text checks.
- Role values are strings: `admin`, `teacher`, `student` — keep these exact values.

## Integration points & important files

- `config/db.php` — contains DB host, user, password, db name and port (default port set to 3307). Update this carefully if changing environment.
- `config/admin_setup.php` — one-off admin bootstrap script. It is intentionally designed to be run once and removed; if present treat as sensitive.
- `sql.sql` — schema and initial data; source of truth for DB migrations in this repo.
- `register.php` — demonstrates transaction usage to write `users` and `students`/`teachers`.
- `login.php`, `admin/admin_login.php` — auth flows and role selection/verification.
- `admin/admin_dashboard.php` — shows how admin pages fetch counts and enforce admin-only access.

## Developer workflows & debugging

- No npm/composer tasks. Edit PHP files and reload in browser.
- Database location: if you get connection errors, check `config/db.php` port and XAMPP MySQL port mapping.
- To enable verbose errors during development, enable display_errors in php.ini or check Apache/PHP error logs (XAMPP control panel -> Logs).
- To add a test admin user quickly run `config/admin_setup.php` in browser then delete the file.

## Safety & security notes (practical)

- `config/admin_setup.php` prints credentials and must be deleted after use. Treat it as high-risk.
- The DB credentials in `config/db.php` are local defaults (`root`, empty pass). Do not commit production secrets; change before deploying.
- Be careful when editing SQL statements; `register.php` relies on the order and types used in `bind_param` (e.g., `INSERT INTO students (student_id, name, registration_number, department, year) VALUES (?, ?, ?, ?, ?)` uses `issss`).

## When editing or adding pages — concrete checklist

1. Start file with `<?php session_start(); include 'config/db.php'; ?>` (adjust relative path if placed in a subfolder).
2. Enforce role checks for protected pages using the existing `$_SESSION['role']` pattern.
3. Use prepared statements and `bind_param` for DB access; match types exactly (i, s, etc.).
4. Use `password_hash()` for storing passwords and `password_verify()` for checks.
5. If adding functionality that touches DB schema, update `sql.sql` accordingly and include a short comment explaining the migration.

## Example snippets (copy/paste safe)

- Role check for admin page:

```php
session_start();
include '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: admin_login.php');
    exit();
}
```

- Insert user pattern (see `register.php`): use transaction, insert `users`, then insert role-specific record using `$conn->insert_id`.

## Next steps / who to ask

If anything here is ambiguous, point me to the file you want changed or ask for a deeper dive into database schema (`sql.sql`) or specific flows (course assignments, enrollment). I can expand examples or add small tests if you want.
