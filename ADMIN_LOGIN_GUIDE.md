# PulseTech Admin Login - Access Guide

## ✅ Admin Login Panel is Ready!

The admin login panel has been successfully configured at:
**http://localhost:5500/admin/login.php**

---

## 📋 Login Credentials

| Field | Value |
|-------|-------|
| **Username** | `admin` |
| **Password** | `admin@123` |

---

## 🚀 How to Access

1. **Open your browser** and go to: `http://localhost:5500/admin/login.php`
2. **Enter the credentials** shown above
3. **Click "Sign in"** to access the admin dashboard

---

## 📍 Admin Panel Features

Once logged in, you'll have access to:

- **Dashboard** - Overview of site statistics
- **Products** - Manage products for the shop
- **Portfolio** - Manage portfolio items/projects
- **Testimonials** - Manage customer testimonials
- **Brands** - Manage client/brand logos
- **Inquiries** - View contact form messages
- **Logout** - Exit the admin panel

---

## 🔐 Security Notes

- The login credentials are displayed on the login page for demo purposes
- Default credentials: `admin` / `admin@123`
- Change these credentials in the login.php file before deploying to production
- The fallback authentication system allows login even without a database connection

---

## 🛠️ Technical Details

- **PHP Version**: 8.4.14
- **Authentication Method**: Session-based with fallback support
- **Database**: Currently uses fallback authentication (database MySQL driver not installed)
- **Session Management**: Secure session regeneration on login

---

## 📞 Troubleshooting

If you encounter issues:

1. **Login page shows but won't authenticate**: Ensure the credentials match exactly (case-sensitive)
2. **Dashboard doesn't load**: Check that you're logged in (redirect will occur if not)
3. **Server not running**: Make sure the PHP development server is still running on port 5500

---

**Last Updated**: January 29, 2026
