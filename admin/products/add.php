<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db_available = isset($pdo) && $pdo instanceof PDO;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$db_available) {
        $errors[] = 'Database unavailable. Cannot add product at this time.';
    } else if (!verify_csrf($_POST['_csrf'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $old_price = (float)($_POST['old_price'] ?? 0);
        $discount = (int)($_POST['discount'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');

        if ($name === '') $errors[] = 'Name is required.';

        // handle upload if file provided
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $updir = __DIR__ . '/../../uploads';
            if (!is_dir($updir)) mkdir($updir, 0755, true);
            $fn = basename($_FILES['image']['name']);
            $target = $updir . '/' . time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', $fn);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_url = '/uploads/' . basename($target);
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO products (name, description, price_ugx, old_price_ugx, discount, rating, image_url, created_at) VALUES (:name,:desc,:price,:old_price,:discount,:rating,:image,NOW())');
                $stmt->execute([
                    'name'=>$name,'desc'=>$description,'price'=>$price,'old_price'=>$old_price,'discount'=>$discount,'rating'=>$rating,'image'=>$image_url
                ]);
                $_SESSION['flash_success'] = 'Product added.';
                header('Location: /admin/products/index.php'); exit;
            } catch (Exception $e) {
                $errors[] = 'Failed to add product. Please try again.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h2>Add Product</h2>
<?php if(!empty($errors)): ?><div class="alert error"><?=htmlspecialchars(implode(' ', $errors))?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?=htmlspecialchars(csrf_token())?>">
  <div class="form-row"><label>Name<input name="name" required></label></div>
  <div class="form-row"><label>Description<textarea name="description"></textarea></label></div>
  <div class="form-row"><label>Price (UGX)<input name="price" type="number" step="0.01"></label></div>
  <div class="form-row"><label>Old Price (UGX)<input name="old_price" type="number" step="0.01"></label></div>
  <div class="form-row"><label>Discount (%)<input name="discount" type="number"></label></div>
  <div class="form-row"><label>Rating<input name="rating" type="number" min="0" max="5"></label></div>
  <div class="form-row"><label>Image<input type="file" name="image" accept="image/*"></label></div>
  <div><button type="submit">Save</button> <a class="btn" href="/admin/products/index.php">Cancel</a></div>
</form>

<?php include __DIR__ . '/../includes/footer.php';
