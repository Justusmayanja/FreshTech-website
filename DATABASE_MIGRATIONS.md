# PulseTech Database Migrations

> New to this project? Start with the step-by-step setup guide: see [SETUP_DATABASE.md](SETUP_DATABASE.md) for installing prerequisites, starting the dev server, running migrations, seeding, and verifying.

Complete database setup and migration files for PulseTech Solutions.

## Prerequisites

- **Database**: `pulsetech_db` created in phpMyAdmin
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **User**: root (or your configured database user)

## Database Structure

### Tables Included

1. **admins** - Administrator user accounts
2. **products** - E-commerce products catalog
3. **orders** - Customer orders
4. **order_items** - Products in orders (order line items)
5. **portfolio** - Project showcase items
6. **testimonials** - Client testimonials/reviews
7. **services** - Service offerings
8. **blog_posts** - Blog articles
9. **contact_messages** - Contact form submissions
10. **inquiries** - Service inquiries (legacy)
11. **brands** - Client/brand logos
12. **migrations** - Migration tracking table

## Quick Setup

### Option 1: Using Migration Runner (Recommended)

1. **Run the migration script:**
   ```bash
   php run-migrations.php
   ```

2. **Expected output:**
   ```
   ╔════════════════════════════════════════════════════════════════╗
   ║   PulseTech Solutions - Database Migration Runner              ║
   ║   Database: pulsetech_db                                       ║
   ╚════════════════════════════════════════════════════════════════╝

   [✓] Connected to database: pulsetech_db
   [✓] Migrations tracking table ready

   Pending migrations to execute:
     → 001_init_schema.sql
     → 002_seed_sample_data.sql

   [✓] 001_init_schema.sql
   [✓] 002_seed_sample_data.sql

   ╔════════════════════════════════════════════════════════════════╗
   ║   Migration Summary                                              ║
   ╚════════════════════════════════════════════════════════════════╝

   Successful: 2
     ✓ 001_init_schema.sql
     ✓ 002_seed_sample_data.sql

   [✓] All migrations executed successfully!

   Your database is ready to use.
   ```

### Option 2: Manual Setup via phpMyAdmin

1. **Open phpMyAdmin** and select `pulsetech_db`
2. **Go to SQL tab**
3. **Copy-paste** the contents of `database/001_init_schema.sql` and execute
4. **Then** copy-paste `database/002_seed_sample_data.sql` and execute

## Migration Files

### 001_init_schema.sql
Creates all core tables with proper:
- Primary keys and auto-increment IDs
- Foreign key relationships
- Indexes for performance
- Default timestamps (created_at, updated_at)
- Character set: utf8mb4 (supports emojis, special characters)

**Tables Created:**
- admins
- products
- orders
- order_items
- portfolio
- testimonials
- services
- blog_posts
- contact_messages
- inquiries
- brands
- migrations

### 002_seed_sample_data.sql
Populates the database with sample data:
- 1 admin user (username: `admin`, password: `$2y$10$...`)
- 5 sample products
- 4 services
- 6 portfolio items
- 4 testimonials
- 4 brands

## Verify Installation

After running migrations, verify all tables exist:

```bash
php verify-setup.php
```

Or check manually in phpMyAdmin:
1. Select `pulsetech_db`
2. You should see all 12 tables listed

## Configuration

Update database credentials in these files if needed:

- `run-migrations.php` (lines 15-18)
- `db.php` (if using direct connections)
- Admin files in `admin/includes/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pulsetech_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## Sample Data Credentials

### Admin User
- **Username**: `admin`
- **Password**: Uses bcrypt hash (change on first login)

### Products
5 tech products with:
- Price in UGX (Ugandan Shilling)
- Stock quantities
- Discount badges
- Ratings

### Services
4 service categories:
- Website Development
- Graphic Design & Branding
- UI/UX Design
- E-commerce Solutions

## Database Schema Notes

### Relationships
```
orders
  ├── order_items (1:N)
  └── products (1:N)

portfolio
  └── independent

products
  ├── orders via order_items
  └── independent
```

### Indexes
- `admins.username` - UNIQUE (fast login)
- `products.category` - for filtering
- `orders.status` - for order tracking
- `contact_messages.email` - for quick lookup

### Timestamps
All tables include:
- `created_at` - Record creation time
- `updated_at` - Last modification time (auto-updated)

## Backup

Before running migrations, backup your database:

```bash
mysqldump -u root pulsetech_db > backup_pulsetech_db.sql
```

## Troubleshooting

### Migration fails with "table already exists"
- This is normal if you've already run migrations
- The `IF NOT EXISTS` clauses prevent errors
- Or drop the database and recreate it

### Connection refused error
- Ensure MySQL is running
- Check credentials in `run-migrations.php`
- Verify database `pulsetech_db` exists

### Permission denied
- Ensure you have write permissions in the project directory
- Check MySQL user has CREATE TABLE privilege

## Next Steps

1. ✅ Run migrations
2. ✅ Verify tables created
3. ✅ Login with admin credentials
4. ✅ Update sample data as needed
5. ✅ Test product listing, orders, etc.

## Support

For issues or questions about the migration:
- Check `database/` directory for SQL files
- Review error messages from `run-migrations.php`
- Verify MySQL connection settings

---

**Last Updated**: January 24, 2025
**Database Version**: 1.0
**Compatibility**: MySQL 5.7+, PHP 7.4+
