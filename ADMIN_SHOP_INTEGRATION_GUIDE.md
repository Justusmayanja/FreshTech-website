# Admin & Public Site Integration Guide

## Overview
The PulseTech admin panel and public website are now fully integrated to work as a unified system. Changes made in the admin panel automatically reflect on the public site, and customer orders from the public site automatically appear in the admin panel.

## System Architecture

```
Public Site (shop.php, cart.php, checkout.php)
        ↓
    DATABASE (Shared)
        ↑
Admin Panel (admin/shop/index.php, admin/orders/index.php)
```

## Database Tables

### 1. Products Table
**Purpose:** Stores all product information used by both public shop and admin shop management.

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price_ugx DECIMAL(15, 2) NOT NULL,
    old_price_ugx DECIMAL(15, 2),
    category VARCHAR(100),
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Shared Access:**
- **Admin Side:** `/admin/shop/index.php` - Manage products (add, edit, delete)
- **Public Side:** `/shop.php` - Display products for sale

---

### 2. Orders Table
**Purpose:** Stores all customer orders placed from the public shop.

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    notes TEXT,
    payment_method ENUM('mobile_money', 'cod') DEFAULT 'mobile_money',
    transaction_id VARCHAR(255),
    total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Flow:**
1. Customer fills out checkout form at `/checkout.php`
2. Order is inserted into `orders` table with status `pending`
3. Admin sees order immediately in `/admin/orders/`

---

### 3. Order Items Table
**Purpose:** Stores individual line items for each order (which products were ordered, quantities, prices).

```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(15, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

**Purpose:** When user orders 3 items, 3 rows are created in this table (one per item).

---

## Integration Points

### 1. Product Synchronization

#### Admin Creates/Edits Product
```
Admin Panel (admin/shop/index.php)
    ↓
INSERT/UPDATE products table
    ↓
Public Shop (shop.php) reads updated data
    ↓
Customer sees new product on public site
```

**Example Workflow:**
1. Admin goes to `/admin/shop/`
2. Admin clicks "Add Product"
3. Admin fills form: Name="Phone Case Pro", Price=85000, Stock=50
4. Admin submits form
5. Product is inserted into `products` table
6. Public site at `/shop.php` immediately shows this product
7. Customer can view and order the product

#### Key Files:
- **Admin:** `admin/shop/index.php` - Handles add/edit/delete forms
- **Public:** `shop.php` - Queries products table: `SELECT * FROM products WHERE stock_quantity > 0`

---

### 2. Order Synchronization

#### Customer Places Order
```
Public Site (checkout.php)
    ↓
Customer fills order form (name, email, address, etc.)
    ↓
INSERT into orders table + INSERT order_items rows
    ↓
Stock reduced: UPDATE products SET stock_quantity = stock_quantity - quantity
    ↓
Customer sees "Thank You" page (thank-you.php)
    ↓
Admin sees order in `/admin/orders/` within seconds
```

**Example Workflow:**
1. Customer adds 2 Phone Cases (Price: 85,000 each) to cart
2. Customer clicks "Checkout"
3. Customer fills: Name, Email, Address, Phone Number
4. Customer submits order
5. **Database Action:** 
   - New row in `orders` (order_number: FT-20260129-ABC123, total: 170,000, status: pending)
   - 2 rows in `order_items` (product_name: Phone Case, qty: 2, unit_price: 85,000, subtotal: 170,000)
   - `products` table updated: stock_quantity reduced by 2 for Phone Case product
6. Admin immediately sees this order in `/admin/orders/`
7. Admin can click "View" to see order details and all items purchased
8. Admin can update order status (pending → processing → completed)

#### Key Files:
- **Public:** `checkout.php` - Handles order submission and database insertion
- **Admin:** `admin/orders/index.php` - Displays all orders with filtering and status management
- **Admin:** `admin/orders/get-order-details.php` - API endpoint that returns order items for modal display

---

## Public Site Flow

### Shop Page (`/shop.php`)
1. Fetches all products from `products` table where `stock_quantity > 0`
2. Displays products in grid layout
3. User can add products to cart (stored in PHP session)

### Cart Page (`/cart.php`)
1. Shows items from session
2. User can update quantities or remove items
3. User can proceed to checkout

### Checkout Page (`/checkout.php`)
1. User enters delivery details (name, email, phone, address)
2. User selects payment method (mobile_money or cash_on_delivery)
3. On submission:
   - Creates order in `orders` table
   - Creates order_items entries for each product
   - Reduces stock in `products` table
   - Redirects to thank-you.php with order number

### Thank You Page (`/thank-you.php`)
1. Shows order number and confirmation message
2. Session cart is cleared

---

## Admin Panel Flow

### Orders Page (`/admin/orders/`)
1. Displays all orders from `orders` table
2. Shows summary cards: Total Orders, Pending, Processing, Completed, Revenue
3. Filter buttons to view orders by status
4. Click "View" on any order to see:
   - Order number, customer name, email, phone, address
   - All items in the order with quantities and prices
   - Total amount
   - Status dropdown (can change: pending → processing → completed → cancelled)
5. Changing status updates the `orders` table immediately

### Shop Page (`/admin/shop/`)
1. Displays all products from `products` table
2. Can add new product (appears on public site immediately)
3. Can edit existing product (changes reflected on public site immediately)
4. Can delete product (removed from public site immediately)
5. Can upload product images

---

## Real-Time Synchronization

### How Changes Appear Instantly

1. **No cache needed** - Both sites query the database directly
2. **No delays** - Changes in one system appear in the other immediately
3. **Database is the source of truth** - All data lives in one central database

### Example Timeline

**11:00 AM:** Admin adds "Wireless Earbuds" to shop
- Admin fills form and clicks Save
- Product inserted into `products` table
- **11:00 AM + 1 second:** Customer opens `/shop.php` and sees "Wireless Earbuds"

**11:05 AM:** Customer orders "Wireless Earbuds"
- Customer completes checkout form
- Order inserted into `orders` table with 1 entry in `order_items`
- **11:05 AM + 1 second:** Admin refreshes `/admin/orders/` and sees the new order

---

## Stock Management

### Automatic Stock Reduction
When a customer places an order, the system automatically reduces stock:

```php
// In checkout.php
UPDATE products 
SET stock_quantity = stock_quantity - 2 
WHERE id = 5 AND stock_quantity >= 2;
```

**Result:**
- Product stock is reduced instantly
- If stock reaches 0, product no longer shows on `/shop.php`
- Admin can see current stock in `/admin/shop/`

**Example:**
- Admin adds "Phone Case" with stock_quantity = 10
- Customer orders 3 units
- Stock becomes 7
- If another customer orders 7 units, stock becomes 0
- "Phone Case" no longer appears on public shop

---

## Order Statuses

Admin can manage order workflow:

1. **Pending** (Yellow) - Order received, awaiting admin action
2. **Processing** (Blue) - Admin is fulfilling the order
3. **Completed** (Green) - Order shipped/delivered
4. **Cancelled** (Red) - Order cancelled

Admin can change status at any time by clicking order and selecting new status from dropdown.

---

## Integration Verification

### To Verify Everything Works:

**Test 1: Product Synchronization**
1. Go to `/admin/shop/` and add a new product
2. Go to `/shop.php` - new product should appear immediately
3. Edit the product in admin
4. Refresh `/shop.php` - changes should show
5. Delete product in admin
6. Refresh `/shop.php` - product should be gone

**Test 2: Order Synchronization**
1. Go to `/shop.php` and add item to cart
2. Go to `/cart.php` and click Checkout
3. Fill order form and submit
4. You'll be redirected to `/thank-you.php`
5. Go to `/admin/orders/` - your order should appear
6. Click "View" to see order details and items you ordered
7. Change order status - page updates immediately

**Test 3: Stock Management**
1. Add product with stock_quantity = 5 in admin
2. Place order for 3 units from public site
3. Check admin shop - stock should now show 2 remaining
4. Try to order 3 more units - should fail (insufficient stock)

---

## Files Modified/Created

### New Files Created:
1. `database/003_orders_schema.sql` - Orders and order_items tables
2. `admin/orders/get-order-details.php` - API endpoint for order details

### Modified Files:
1. `checkout.php` - Fixed stock_quantity field name (was `stock`)
2. `admin/orders/index.php` - Complete rebuild with live database integration
3. `admin/shop/index.php` - Already functional, shares products with public site
4. `shop.php` - Already functional, fetches from shared products table

---

## Troubleshooting

### Orders not appearing in admin?
- Check database connection
- Verify `orders` and `order_items` tables exist
- Check that order was successfully inserted (thank-you page should show)

### Products not appearing on public site?
- Check that `stock_quantity > 0`
- Verify database connection
- Check that products table was created

### Stock not reducing after order?
- Check that `checkout.php` is executing the UPDATE statement
- Verify `stock_quantity` field name is correct (not `stock`)

### Images not showing?
- Verify `/uploads/` directory exists and is writable
- Check that image paths are correct in database

---

## Summary

The PulseTech system now operates as a unified e-commerce platform where:

✅ **Admin controls inventory:** Add/edit/delete products in one place  
✅ **Changes are instant:** Products appear on public site immediately  
✅ **Orders are real-time:** Customer orders appear in admin panel instantly  
✅ **Stock is automatic:** Inventory decreases when orders are placed  
✅ **Admin manages fulfillment:** Track order status from pending to completed  
✅ **Database is single source of truth:** No syncing, no caching issues  

This creates a seamless experience for both administrators and customers.
