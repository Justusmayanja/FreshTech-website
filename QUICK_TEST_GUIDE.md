# Quick Test Guide: Admin & Public Site Integration

## Test 1: Add Product in Admin, See on Public Shop (2 minutes)

### Step 1: Go to Admin Shop
1. Open: `http://localhost:5500/admin/`
2. Log in with admin credentials
3. Click "Shop" in sidebar

### Step 2: Add a Test Product
1. Click "Add Product" button
2. Fill in details:
   - **Name:** Test Wireless Speaker
   - **Description:** High-quality wireless speaker
   - **Category:** Audio
   - **Price:** 150000
   - **Old Price:** 200000
   - **Stock:** 25
   - **Image:** (optional) Upload an image
3. Click "Save"

### Step 3: Verify on Public Site
1. Open: `http://localhost:5500/shop.php`
2. You should immediately see "Test Wireless Speaker" in the products grid
3. Price should show: UGX 150,000 (old: 200,000)

✅ **Success:** Product appears on public site!

---

## Test 2: Place Order, See in Admin Orders (5 minutes)

### Step 1: Add to Cart on Public Shop
1. Stay on `/shop.php` or go there
2. Find any product (or use the one from Test 1)
3. Click "Add to Cart"
4. Confirm you see "Product added to cart!"

### Step 2: Proceed to Checkout
1. Click shopping cart icon in navbar (shows number of items)
2. On cart page, review items
3. Click "Proceed to Checkout"

### Step 3: Fill Checkout Form
1. **Full Name:** Test Customer
2. **Email:** test@example.com
3. **Phone:** +256701234567
4. **Address:** 123 Main St, Kampala
5. **Payment Method:** Select "Mobile Money"
6. **Transaction ID:** txn-123456
7. Click "Place Order"

### Step 4: Order Confirmation
1. You'll be redirected to thank-you page
2. Copy the order number shown (e.g., FT-20260129-ABC123)

### Step 5: Check Admin Orders
1. Go to: `http://localhost:5500/admin/`
2. Click "Orders" in sidebar
3. **You should see your order at the top!**
4. Order number should match
5. Customer name, email, amount should all be correct

### Step 6: View Order Details
1. Click "View" button on your order
2. Modal opens showing:
   - Order number
   - Customer information
   - All items you ordered (with quantities)
   - Total amount
   - Status (default: Pending)

✅ **Success:** Order appears in admin panel instantly!

---

## Test 3: Change Order Status (1 minute)

### Step 1: In Admin Orders
1. Find the order from Test 2
2. Click "View"

### Step 2: Change Status
1. Find the "Status" dropdown in the modal
2. Change from "Pending" to "Processing"
3. Page will refresh

### Step 3: Verify
1. Order status should now show "Processing" (blue badge)
2. Status is updated in database

✅ **Success:** Admin can manage order workflow!

---

## Test 4: Stock Decreases After Order (3 minutes)

### Step 1: Check Stock Before Order
1. Go to `/admin/shop/`
2. Find a product with available stock
3. Note the stock number (e.g., "Stock: 25")

### Step 2: Place Order for That Product
1. Go to `/shop.php`
2. Find the same product
3. Add it to cart (quantity: 3)
4. Checkout and place order

### Step 3: Check Stock After Order
1. Go to `/admin/shop/`
2. Find the same product
3. **Stock should be reduced by 3!**
4. Example: Was 25, now should be 22

✅ **Success:** Stock management is automatic!

---

## Test 5: Edit Product, See Changes Immediately (3 minutes)

### Step 1: Go to Admin Shop
1. `/admin/shop/`
2. Find "Test Wireless Speaker" (from Test 1)

### Step 2: Edit the Product
1. Click "Edit" button
2. Change the price from 150000 to 120000
3. Change stock from 25 to 35
4. Click "Save"

### Step 3: Check Public Site
1. Go to `/shop.php`
2. Find "Test Wireless Speaker"
3. **Price should now show: UGX 120,000**
4. **Product is still visible (stock increased)**

✅ **Success:** Product changes reflect immediately!

---

## Test 6: Filter Orders by Status (2 minutes)

### Step 1: Go to Admin Orders
1. `/admin/orders/`

### Step 2: Click Filter Buttons
1. Click "Pending" - shows only pending orders
2. Click "Processing" - shows only processing orders
3. Click "Completed" - shows only completed orders
4. Click "All Orders" - shows everything

### Step 3: View Counts
1. Summary cards at top show counts for each status
2. Total Revenue shows sum of all order amounts

✅ **Success:** Order filtering works!

---

## Complete Integration Features

After passing all tests above, you have verified:

✅ Product Management
- ✓ Admin can add products
- ✓ Products appear on public site immediately
- ✓ Admin can edit products
- ✓ Changes appear on public site immediately
- ✓ Admin can delete products

✅ Order Management
- ✓ Customers can place orders from public site
- ✓ Orders appear in admin panel immediately
- ✓ Admin can view order details
- ✓ Admin can change order status
- ✓ Orders can be filtered by status

✅ Stock Management
- ✓ Stock decreases when orders are placed
- ✓ Products with 0 stock don't appear on public site
- ✓ Admin can see current stock levels

✅ Database Integration
- ✓ All data is shared between admin and public site
- ✓ Changes are instant (no delays)
- ✓ Database is the single source of truth

---

## Troubleshooting Tips

### "Product added to cart" but no item shown?
- Clear browser cache and reload
- Check that session is working

### Order not appearing in admin?
- Refresh the admin orders page
- Check database connection
- Verify checkout form was submitted successfully

### Stock not decreasing?
- Check that order was placed successfully (thank-you page appeared)
- Verify product has stock before ordering
- Refresh admin shop page

### Image not showing?
- Check `/uploads/` directory exists
- Verify image was actually uploaded (check form submission)
- Try uploading a different image

---

## Next Steps

After verifying the integration works:

1. **Customize product categories** - Based on your actual product types
2. **Set up payment gateway** - Integrate real payment (MTN, Airtel, etc.)
3. **Add email notifications** - Send order confirmation to customers
4. **Create order tracking** - Let customers check order status with order number
5. **Add product reviews** - Let customers review products
6. **Inventory alerts** - Notify admin when stock is low

---

**Integration Status: ✅ COMPLETE**

The PulseTech admin panel and public shop are now fully integrated!
