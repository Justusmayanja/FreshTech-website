# Quick Start: Running Database Migrations

## Step 1: Navigate to Project Directory
```bash
cd D:\Justus\FreshTech-website
```

## Step 2: Run Migrations
```bash
php run-migrations.php
```

## Expected Output
You should see:
```
✓ Connected to database: pulsetech_db
✓ Migrations tracking table ready

Pending migrations to execute:
  → 001_init_schema.sql
  → 002_seed_sample_data.sql

✓ 001_init_schema.sql
✓ 002_seed_sample_data.sql

✓ All migrations executed successfully!
Your database is ready to use.
```

## What Gets Created

### Tables (12 total)
- admins (admin accounts)
- products (shop items)
- orders (customer orders)
- order_items (order line items)
- portfolio (project showcase)
- testimonials (client reviews)
- services (service offerings)
- blog_posts (blog articles)
- contact_messages (contact forms)
- inquiries (service inquiries)
- brands (client logos)
- migrations (tracking)

### Sample Data
- ✓ 1 admin user
- ✓ 5 products (tech accessories)
- ✓ 4 services
- ✓ 6 portfolio items
- ✓ 4 testimonials
- ✓ 4 brands

## Verify in phpMyAdmin

1. Open phpMyAdmin → Select `pulsetech_db`
2. You should see 12 tables listed
3. Click `admins` → Browse
4. You'll see the default admin user

## Admin Login

**URL**: `http://localhost:5500/admin`
- **Username**: `admin`
- **Password**: Check the bcrypt hash in the admins table

## Troubleshooting

### "Connection refused"
- Ensure MySQL is running
- Check username/password in `run-migrations.php`

### "Table already exists"
- This is fine! It means migrations already ran
- Run `php run-migrations.php` again to verify

### "Permission denied"
- Ensure you're in the project directory
- Check file permissions

## Files Included

```
database/
├── 001_init_schema.sql          ← Creates all tables
└── 002_seed_sample_data.sql     ← Adds sample data

run-migrations.php               ← Migration runner script
DATABASE_MIGRATIONS.md           ← Full documentation
MIGRATIONS_QUICK_START.md        ← This file
```

## Next Steps

1. ✅ Verify all tables created (in phpMyAdmin)
2. ✅ Update admin password in database
3. ✅ Update sample product data
4. ✅ Test login at `/admin`
5. ✅ Configure email settings
6. ✅ Test cart functionality at `/shop.php`

## Questions?

- Review `DATABASE_MIGRATIONS.md` for detailed info
- Check `database/*.sql` files for table structure
- Verify connection settings in `run-migrations.php`

---

**Time to Setup**: ~2 minutes
**Migrations**: 2 files
**Tables**: 12 created
**Sample Records**: ~20+ inserted
