# Integration Architecture Diagrams

## 1. High-Level System Overview

```
                    ┌──────────────────────────────────┐
                    │     CUSTOMER EXPERIENCE          │
                    └──────────────┬───────────────────┘
                                   │
                    ┌──────────────▼───────────────────┐
                    │      PUBLIC WEBSITE              │
                    │  http://localhost:5500/shop.php  │
                    │                                  │
                    │  ├─ shop.php (product listing)   │
                    │  ├─ cart.php (shopping cart)     │
                    │  └─ checkout.php (order)         │
                    └──────────────┬───────────────────┘
                                   │ INSERT/UPDATE/SELECT
                                   │
                    ┌──────────────▼───────────────────┐
                    │      DATABASE (MySQL)           │
                    │                                  │
                    │  ├─ products table               │
                    │  ├─ orders table                 │
                    │  └─ order_items table            │
                    └──────────────▲───────────────────┘
                                   │ INSERT/UPDATE/SELECT
                                   │
                    ┌──────────────┴───────────────────┐
                    │     ADMIN PANEL                 │
                    │   /admin/                        │
                    │                                  │
                    │  ├─ admin/shop/ (manage products)│
                    │  └─ admin/orders/ (manage orders)│
                    └──────────────────────────────────┘
                              │
                              └─ ADMIN USERS
```

---

## 2. Product Flow Diagram

### Admin Adds Product → Appears on Public Site

```
TIMELINE:
─────────────────────────────────────────────

Admin at /admin/shop/
    │
    ├─ Click "Add Product"
    │
    ├─ Fill form:
    │  • Name: "Wireless Speaker"
    │  • Price: 150,000 UGX
    │  • Stock: 25 units
    │  • Category: Audio
    │  • Image: uploaded.jpg
    │
    └─ Click "Save"
            │
            ▼
    ╔═════════════════════════════════╗
    ║ INSERT INTO products TABLE      ║
    ║ → Product ID: 42                ║
    ║ → name, price, stock, image     ║
    ╚═════════════════════════════════╝
            │
            ├─ IMMEDIATELY (within 1 second)
            │
            ▼
    Customer refreshes /shop.php
            │
            ├─ SELECT FROM products WHERE stock_quantity > 0
            │
            ▼
    "Wireless Speaker" appears in grid
    ✅ Product now for sale!
```

### Admin Edits Product → Changes Show Immediately

```
Admin at /admin/shop/ → finds "Wireless Speaker"
    │
    ├─ Click "Edit"
    │
    ├─ Change price: 150,000 → 120,000
    │
    ├─ Change stock: 25 → 35
    │
    └─ Click "Save"
            │
            ▼
    ╔═════════════════════════════════╗
    ║ UPDATE products TABLE           ║
    ║ WHERE id = 42                   ║
    ║ SET price = 120000, stock = 35  ║
    ╚═════════════════════════════════╝
            │
            ├─ IMMEDIATELY
            │
            ▼
    Customer on /shop.php sees:
    • New price: UGX 120,000
    • Still in stock (35 units)
    ✅ Changes live immediately!
```

---

## 3. Order Flow Diagram

### Customer Places Order → Appears in Admin

```
CUSTOMER JOURNEY:
─────────────────────────────────────────────

Customer on /shop.php
    │
    ├─ Sees "Wireless Speaker" (UGX 150,000)
    │
    ├─ Clicks "Add to Cart"
    │  └─ Added to PHP SESSION: $_SESSION['cart'][42] = [qty: 1, price: 150000]
    │
    ├─ Also adds "Phone Case" (UGX 85,000)
    │  └─ Cart now has 2 items, total: 235,000
    │
    ├─ Clicks "Cart" icon
    │
    ├─ Reviews cart at /cart.php
    │
    ├─ Clicks "Proceed to Checkout"
    │
    └─ Fills /checkout.php form:
       • Name: "John Mucunguzi"
       • Email: "john@example.com"
       • Phone: "+256701234567"
       • Address: "123 Buganda Rd, Kampala"
       • Payment: "Mobile Money"
       • Txn ID: "MTN-ABC123"
            │
            ▼
    ╔═════════════════════════════════════════════╗
    ║ DATABASE OPERATIONS (TRANSACTION)           ║
    ║                                             ║
    ║ 1. INSERT INTO orders                       ║
    ║    order_number: FT-20260129-XYZ789         ║
    ║    customer_name: John Mucunguzi            ║
    ║    email, phone, address: (as filled)       ║
    ║    total_amount: 235000                     ║
    ║    status: 'pending'                        ║
    ║    → Returns: order_id = 1005               ║
    ║                                             ║
    ║ 2. INSERT INTO order_items (2 rows)         ║
    ║    [1] order_id: 1005                       ║
    ║        product_id: 42 (Wireless Speaker)    ║
    ║        quantity: 1                          ║
    ║        unit_price: 150000                   ║
    ║        subtotal: 150000                     ║
    ║                                             ║
    ║    [2] order_id: 1005                       ║
    ║        product_id: 5 (Phone Case)           ║
    ║        quantity: 1                          ║
    ║        unit_price: 85000                    ║
    ║        subtotal: 85000                      ║
    ║                                             ║
    ║ 3. UPDATE products (stock reduction)        ║
    ║    WHERE id = 42: stock_quantity -= 1       ║
    ║    WHERE id = 5: stock_quantity -= 1        ║
    ║                                             ║
    ║ IF ALL SUCCEED → COMMIT                     ║
    ║ IF ANY FAILS → ROLLBACK (entire order)      ║
    ╚═════════════════════════════════════════════╝
            │
            ├─ SUCCESS
            │
            ▼
    Redirect to /thank-you.php
    Show: Order #FT-20260129-XYZ789
    Show: Total: UGX 235,000
    ✅ Order placed!
            │
            ├─ SAME INSTANT
            │
            ▼
    ADMIN SEES IN /admin/orders/:
    ┌─────────────────────────────────┐
    │ NEW ORDER APPEARS AT TOP         │
    │                                 │
    │ Order: FT-20260129-XYZ789       │
    │ Customer: John Mucunguzi        │
    │ Amount: UGX 235,000             │
    │ Status: Pending [⏱️ YELLOW]      │
    │ Date: Jan 29, 2026 2:45 PM      │
    │                                 │
    │ [View] [Edit] [Delete]          │
    └─────────────────────────────────┘
    ✅ Admin sees order immediately!
            │
            ├─ Admin clicks [View]
            │
            ▼
    Modal shows:
    ┌──────────────────────────────────────────┐
    │ ORDER DETAILS: FT-20260129-XYZ789        │
    │                                          │
    │ Status: [Pending ▼] (dropdown)           │
    │                                          │
    │ CUSTOMER INFO:                           │
    │ Name: John Mucunguzi                     │
    │ Email: john@example.com                  │
    │ Phone: +256701234567                     │
    │ Address: 123 Buganda Rd, Kampala         │
    │                                          │
    │ ITEMS ORDERED:                           │
    │ ┌────────────────────────────────────┐   │
    │ │ Product      │ Qty │ Price │ Total │   │
    │ ├────────────────────────────────────┤   │
    │ │ Wireless Spk │  1  │150,000│150,000│   │
    │ │ Phone Case   │  1  │ 85,000│ 85,000│   │
    │ └────────────────────────────────────┘   │
    │                                          │
    │ TOTAL: UGX 235,000                       │
    │                                          │
    │ [Update Status] [Print] [Delete]         │
    └──────────────────────────────────────────┘
    ✅ Admin sees all order details!
            │
            ├─ Admin changes status: Pending → Processing
            │
            ▼
    ╔═════════════════════════════════════════════╗
    ║ UPDATE orders                               ║
    ║ WHERE id = 1005                             ║
    ║ SET status = 'processing'                   ║
    ╚═════════════════════════════════════════════╝
            │
            └─ Page refreshes, status now shows
               [Processing ▶️ BLUE]
    ✅ Order managed!
```

---

## 4. Stock Management Flow

### Order → Stock Decreases → Product Availability

```
STOCK LIFECYCLE:
─────────────────────────────────────────────

BEFORE ORDER:
┌──────────────────────────────────────┐
│ ADMIN /admin/shop/                   │
├──────────────────────────────────────┤
│ Product: Wireless Speaker            │
│ Stock: 25 units ✅                   │
└──────────────────────────────────────┘
          │
          ├─ Also visible on PUBLIC SITE
          │
          ▼
┌──────────────────────────────────────┐
│ PUBLIC /shop.php                     │
├──────────────────────────────────────┤
│ Shows: Wireless Speaker              │
│ Price: UGX 150,000                   │
│ "In Stock" ✅                        │
└──────────────────────────────────────┘
          │
          ├─ Customer orders 1 unit
          │
          ▼
    ╔═════════════════════════════════════════╗
    ║ UPDATE products                         ║
    ║ SET stock_quantity = stock_quantity - 1 ║
    ║ WHERE id = 42 AND stock_quantity >= 1   ║
    ║                                         ║
    ║ Result: 25 - 1 = 24 ✅                  ║
    ╚═════════════════════════════════════════╝
          │
          ▼

AFTER ORDER:
┌──────────────────────────────────────┐
│ ADMIN /admin/shop/                   │
├──────────────────────────────────────┤
│ Product: Wireless Speaker            │
│ Stock: 24 units ✅ (UPDATED!)        │
└──────────────────────────────────────┘
          │
          ├─ Also updated on PUBLIC SITE
          │
          ▼
┌──────────────────────────────────────┐
│ PUBLIC /shop.php                     │
├──────────────────────────────────────┤
│ Shows: Wireless Speaker              │
│ Price: UGX 150,000                   │
│ "In Stock (24 available)" ✅         │
└──────────────────────────────────────┘

─ SCENARIO: Stock runs out ─

When customer tries to order last unit:
Customer orders 1 unit (stock = 1)
    ↓
UPDATE stock_quantity = 1 - 1 = 0
    ↓
Product stock reaches 0
    ↓
PRODUCT NO LONGER SHOWS on /shop.php
    ↓
Product still in admin but marked as "Out of Stock"
```

---

## 5. Data Flow: Create, Read, Update Operations

```
CREATE (Add Product):
────────────────────────

Admin /admin/shop/
    ├─ Form POST
    │
    ▼
admin/shop/index.php
    ├─ Validate input
    │
    ▼
    ├─ INSERT INTO products
    │
    ▼
    └─ Redirect to /admin/shop/
            │
            ▼
    public /shop.php automatically
    shows new product
    (no change needed)


READ (View Product):
────────────────────

Customer /shop.php
    │
    ├─ Page loads
    │
    ▼
shop.php
    ├─ SELECT * FROM products WHERE stock > 0
    │
    ▼
    └─ Displays all products


UPDATE (Edit Product):
──────────────────────

Admin /admin/shop/
    ├─ Click Edit
    │
    ├─ Form POST with new data
    │
    ▼
admin/shop/index.php
    ├─ Validate input
    │
    ▼
    ├─ UPDATE products WHERE id = X
    │
    ▼
    └─ Changes live immediately
            │
            ▼
    Customer /shop.php shows
    updated price/stock
    (no change needed)


DELETE (Remove Product):
────────────────────────

Admin /admin/shop/
    ├─ Click Delete
    │
    ├─ Confirm dialog
    │
    ├─ POST with product_id
    │
    ▼
admin/shop/index.php
    ├─ DELETE FROM products WHERE id = X
    │
    ▼
    └─ Product removed from database
            │
            ▼
    Customer /shop.php
    immediately no longer
    shows this product
```

---

## 6. Status Update Flow

### Admin Changes Order Status

```
ADMIN WORKFLOW:
───────────────────────────────────────────

Admin at /admin/orders/
    │
    ├─ Sees order: FT-20260129-XYZ789
    │  Status: [Pending 🟨]
    │
    ├─ Clicks [View]
    │
    ▼
Modal opens with status dropdown:
    │
    ├─ Current: [Pending ▼]
    │  Options:
    │    • Pending (yellow)
    │    • Processing (blue)
    │    • Completed (green)
    │    • Cancelled (red)
    │
    ├─ Admin selects: Processing
    │
    ▼
JavaScript submits:
    ├─ POST /admin/orders/index.php
    ├─ action: update_status
    ├─ order_id: 1005
    ├─ status: processing
    │
    ▼
PHP backend:
    ├─ UPDATE orders SET status = 'processing'
    │  WHERE id = 1005
    │
    ▼
    ├─ Redirect to /admin/orders/
    │
    ▼
Status now shows: [Processing 🟦]

✅ Status updated in database!
```

---

## 7. Real-Time Synchronization Guarantee

```
NO DELAYS:
──────────────────────────────────────────

Admin inserts product at 2:30:15 PM
    ├─ MySQL processes: 2:30:15.002
    │
    ├─ Admin/shop/ redirects: 2:30:15.100
    │
    └─ Data in database: ✅ LIVE

Customer refreshes shop.php at 2:30:16 PM
    ├─ /shop.php queries database: 2:30:16.001
    │
    ├─ Gets latest product: ✅ YES
    │
    └─ Displays immediately: ✅ YES

WHY NO DELAY?
─────────────
• No cache invalidation needed
• No sync service needed
• No message queue needed
• Direct database access
• Database is single source of truth


EDGE CASE: Multiple browsers
─────────────────────────────

Admin adds product in Chrome
    ├─ Inserted at 2:30:15
    │
Customer has Firefox open
    ├─ Sees product immediately
    │  (when Firefox queries at any time ≥ 2:30:15)
    │
Another admin in Safari
    ├─ Sees product immediately
    │  (when Safari queries at any time ≥ 2:30:15)

No special coordination needed!
```

---

## 8. Error Handling & Rollback

### Transaction Safety

```
ORDER PLACEMENT - ALL OR NOTHING:
──────────────────────────────────────────

Customer submits order
    │
    ▼
BEGIN TRANSACTION
    │
    ├─ Step 1: INSERT INTO orders → ✅ Success
    │
    ├─ Step 2: INSERT INTO order_items (product 1) → ✅ Success
    │
    ├─ Step 3: INSERT INTO order_items (product 2) → ✅ Success
    │
    ├─ Step 4: UPDATE products (product 1) stock -= 1 → ✅ Success
    │
    ├─ Step 5: UPDATE products (product 2) stock -= 1 → ✅ Success
    │
    ├─ Step 6: Check if stock available? → ✅ YES
    │
    ▼
COMMIT TRANSACTION ✅
    │
    └─ Order placed successfully!
       Thank you page shows


ERROR SCENARIO - ROLLBACK:
──────────────────────────────────────────

Customer orders but stock insufficient
    │
    ▼
BEGIN TRANSACTION
    │
    ├─ Step 1: INSERT INTO orders → ✅ Success
    │
    ├─ Step 2: INSERT INTO order_items → ✅ Success
    │
    ├─ Step 3: UPDATE products (product 1) stock -= 1 → ✅ Success
    │
    ├─ Step 4: UPDATE products (product 2) stock -= 1 → ✅ Success
    │
    ├─ Step 5: Check if stock available? → ❌ NO!
    │  (Threw exception: "Insufficient stock")
    │
    ▼
ROLLBACK TRANSACTION
    │
    ├─ Undo: DELETE from orders
    │
    ├─ Undo: DELETE from order_items
    │
    ├─ Undo: Restore stock for product 1
    │
    ├─ Undo: Restore stock for product 2
    │
    ▼
Order not placed ✅
    │
    └─ Error shown to customer
       "Insufficient stock for product X"
       Nothing changed in database
```

---

## Summary

This integration creates a **bulletproof, real-time synchronized e-commerce system** where:

✅ Products are managed in one place (admin)  
✅ They automatically appear for sale (public site)  
✅ Customers order from public site  
✅ Orders immediately appear in admin  
✅ Admin manages order fulfillment  
✅ Stock is automatically managed  
✅ All operations are transaction-safe  
✅ No delays or synchronization issues  

**The database is the single source of truth.**
