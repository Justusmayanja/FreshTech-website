<?php
session_start();

// Test cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $_SESSION['cart'] = $_SESSION['cart'] ?? [];
    $pid = (int)($_POST['product_id'] ?? 0);
    
    if ($pid > 0) {
        $_SESSION['cart'][$pid] = [
            'id' => $pid,
            'name' => $_POST['product_name'] ?? 'Product',
            'quantity' => ($_SESSION['cart'][$pid]['quantity'] ?? 0) + 1
        ];
    }
}

$cart_count = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $cart_count += $item['quantity'] ?? 1;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Cart Test Page</h1>
        
        <div class="bg-blue-100 p-4 rounded mb-6">
            <p class="text-lg"><strong>Cart Items:</strong> <?php echo $cart_count; ?></p>
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700 font-bold">✓ Product added! Refresh the page to update.</p>
            </div>
        <?php endif; ?>
        
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Test Products</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="border rounded p-4">
                        <h3 class="font-bold">Product <?php echo $i; ?></h3>
                        <p class="text-gray-600 mb-4">Price: UGX 50,000</p>
                        
                        <form method="POST" class="w-full">
                            <input type="hidden" name="add_to_cart" value="1">
                            <input type="hidden" name="product_id" value="<?php echo $i; ?>">
                            <input type="hidden" name="product_name" value="Product <?php echo $i; ?>">
                            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 cursor-pointer">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="cart.php" class="text-blue-600 underline">View Cart</a> | 
            <a href="shop.php" class="text-blue-600 underline">Back to Shop</a>
        </div>
    </div>
</body>
</html>
