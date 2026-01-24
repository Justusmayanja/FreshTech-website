# FreshTech / PulseTech Website

Modern PHP + MySQL site with an admin panel, migrations, and seed data. This README includes a quick setup for local development on Windows/XAMPP.

## Prerequisites
- XAMPP (Apache + MySQL + PHP)
- MySQL service running
- PHP has `pdo_mysql` enabled

## Quick Start (Windows/XAMPP)

1) Enable MySQL driver in XAMPP `php.ini`:
```
extension_dir="C:\xampp\php\ext"
extension=pdo_mysql
extension=mysqli
extension=openssl
```
Restart Apache and any CLI shells.

2) Start the local dev server:
```
cd C:\xampp\htdocs\dashboard\Justus\FreshTech-website
.\start-server.bat
```
Opens http://localhost:5500 and starts PHP using XAMPP's `php.exe` and `php.ini`.

3) Initialize the database:
```
php setup-db.php
php run-migrations.php
php run-seeders.php --reset
```
- Schema files: [database/001_init_schema.sql](database/001_init_schema.sql)
- Sample data: [database/002_seed_sample_data.sql](database/002_seed_sample_data.sql)

4) Verify setup:
- Visit: http://localhost:5500/verify-setup.php
- Or: `php test-db.php`

5) Admin login:
- URL: `http://localhost:5500/admin/login.php`
- Default: `admin` / `FreshTech2025!`
- Change password safely:
```
php update-admin-password.php admin NewStrongPass!
```
Script: [update-admin-password.php](update-admin-password.php)

## Project Docs
- Setup details: [SETUP_DATABASE.md](SETUP_DATABASE.md)
- Migration runner: [MIGRATIONS_QUICK_START.md](MIGRATIONS_QUICK_START.md)
- Full migration notes: [DATABASE_MIGRATIONS.md](DATABASE_MIGRATIONS.md)

## Configuration References
- Shared PDO: [db.php](db.php)
- Admin PDO config: [admin/includes/config.php](admin/includes/config.php)
- Server launcher: [start-server.bat](start-server.bat)
- Status endpoint: [config/db_status.php](config/db_status.php)

## Troubleshooting
- "Could not find driver": Verify `extension_dir` and `extension=pdo_mysql` in XAMPP `php.ini`.
- Access denied: Update credentials in [db.php](db.php) and [admin/includes/config.php](admin/includes/config.php) (XAMPP default is `root` / empty password).
- Tables missing: Re-run migrations and seeders.
- PowerShell cannot run `.bat`: Use `./start-server.bat`.
- Which `php.ini` is used:
```
php -i | findstr /I "Loaded Configuration File"
```

## License
Internal development project files. No external redistribution license included.
