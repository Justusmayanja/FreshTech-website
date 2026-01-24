# FreshTech / PulseTech Database Setup Guide (Windows/XAMPP)

This guide walks you through setting up the MySQL database and starting the local dev server after cloning the repo.

## Prerequisites
- XAMPP (Apache + MySQL + PHP) installed on Windows
- MySQL service running in XAMPP Control Panel
- PHP has PDO MySQL enabled (fix below if needed)

## 1) Clone and open the project
```powershell
cd C:\xampp\htdocs\dashboard\Justus
git clone https://github.com/Justusmayanja/FreshTech-website.git
cd FreshTech-website
```

## 2) Ensure PHP can connect to MySQL (pdo_mysql)
Most connection errors come from a missing driver ("could not find driver").

- Edit global XAMPP config: `C:\xampp\php\php.ini`
  - Set:
    ```ini
    extension_dir="C:\xampp\php\ext"
    extension=pdo_mysql
    extension=mysqli
    extension=openssl
    ```
  - Save and restart Apache (and any CLI shells).
- Verify the driver:
  ```powershell
  php -m | findstr /I pdo_mysql
  ```
  You should see `pdo_mysql` listed.

Note: The project includes a local `php.ini` too. The dev server script uses your XAMPP PHP and `php.ini` for consistency.

## 3) Start the local PHP dev server
```powershell
cd C:\xampp\htdocs\dashboard\Justus\FreshTech-website
.\start-server.bat
```
- This opens `http://localhost:5500` and starts PHP using XAMPP's `php.exe` and `php.ini`.

## 4) Create the database (if missing)
```powershell
php setup-db.php
```
- Creates `pulsetech_db` if not present and checks required tables.

## 5) Run migrations (create tables)
```powershell
php run-migrations.php
```
- Creates all core tables and a migrations tracking table.
- See detailed schema in [database/001_init_schema.sql](database/001_init_schema.sql).

## 6) Seed sample data (optional but recommended)
```powershell
php run-seeders.php --reset
```
- Inserts sample records (products, services, portfolio, testimonials, brands).
- See data in [database/002_seed_sample_data.sql](database/002_seed_sample_data.sql).

## 7) Verify setup
- Open: `http://localhost:5500/verify-setup.php`
  - You should see Database ✓ and non-zero counts for sample data.
- Quick CLI check:
  ```powershell
  php test-db.php
  ```

## 8) Admin login
- URL: `http://localhost:5500/admin/login.php`
- Default user: `admin`
- Default password: `FreshTech2025!` (you can change it)
- To set your own password safely:
  ```powershell
  php update-admin-password.php admin NewStrongPass!
  ```
  - Script: [update-admin-password.php](update-admin-password.php)

## Configuration references
- Shared DB connection: [db.php](db.php)
- Admin config (PDO): [admin/includes/config.php](admin/includes/config.php)
- Server launcher: [start-server.bat](start-server.bat)
- Status endpoint: [config/db_status.php](config/db_status.php)
- Quick start doc: [MIGRATIONS_QUICK_START.md](MIGRATIONS_QUICK_START.md)
- Full migrations doc: [DATABASE_MIGRATIONS.md](DATABASE_MIGRATIONS.md)

## Troubleshooting
- **Could not find driver**: Ensure `extension_dir` is `C:\xampp\php\ext` and `extension=pdo_mysql` is enabled; restart Apache/CLI.
- **Access denied for user**: Update credentials in [db.php](db.php) and [admin/includes/config.php](admin/includes/config.php). Default XAMPP is `root` with empty password.
- **Tables missing**: Re-run `php run-migrations.php`, then `php run-seeders.php --reset`.
- **PowerShell cannot run .bat**: Use `./start-server.bat` or `.\nstart-server.bat` (current directory execution).
- **Port 5500 busy**: Edit [start-server.bat](start-server.bat) to change the port, e.g., `localhost:5501`, and update links accordingly.
- **Which php.ini is used?**:
  ```powershell
  php -i | findstr /I "Loaded Configuration File"
  ```
  Ensure it points to `C:\xampp\php\php.ini`.

---
Your environment should now be ready. If you run into anything else, check `verify-setup.php` for detailed pass/fail messages and counts.
