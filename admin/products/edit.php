<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db_available = isset($pdo) && $pdo instanceof PDO;

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) { header('Location: /admin/products/index.php'); exit; }

if (!$db_available) {
    $_SESSION['flash_error'] = 'Database unavailable. Cannot edit product at this time.';
    header('Location: /admin/products/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
$stmt->execute(['id'=>$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: /admin/products/index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? '')) { $errors[] = 'Invalid CSRF token.'; }
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $old_price = (float)($_POST['old_price'] ?? 0);
    $discount = (int)($_POST['discount'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? $product['image_url']);

    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $updir = __DIR__ . '/../../uploads';
        if (!is_dir($updir)) mkdir($updir, 0755, true);
        $fn = basename($_FILES['image']['name']);
        $target = $updir . '/' . time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', $fn);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_url = '/uploads/' . basename($target);
        }
    }

    if ($name === '') $errors[] = 'Name required.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('UPDATE products SET name=:name, description=:desc, price_ugx=:price, old_price_ugx=:old_price, discount=:discount, rating=:rating, image_url=:image, updated_at=NOW() WHERE id=:id');
            $stmt->execute(['name'=>$name,'desc'=>$description,'price'=>$price,'old_price'=>$old_price,'discount'=>$discount,'rating'=>$rating,'image'=>$image_url,'id'=>$id]);
            $_SESSION['flash_success'] = 'Product updated.';
            header('Location: /admin/products/index.php'); exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to update product. Please try again.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h2>Edit Product</h2>
<?php if(!empty($errors)): ?><div class="alert error"><?=htmlspecialchars(implode(' ', $errors))?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?=htmlspecialchars(csrf_token())?>">
  <input type="hidden" name="id" value="<?=htmlspecialchars($product['id'])?>">
  <div class="form-row"><label>Name<input name="name" value="<?=htmlspecialchars($product['name'])?>" required></label></div>
  <div class="form-row"><label>Description<textarea name="description"><?=htmlspecialchars($product['description'])?></textarea></label></div>
  <div class="form-row"><label>Price (UGX)<input name="price" type="number" step="0.01" value="<?=htmlspecialchars($product['price_ugx'] ?? '')?>"></label></div>
  <div class="form-row"><label>Old Price (UGX)<input name="old_price" type="number" step="0.01" value="<?=htmlspecialchars($product['old_price_ugx'] ?? '')?>"></label></div>
  <div class="form-row"><label>Discount (%)<input name="discount" type="number" value="<?=htmlspecialchars($product['discount'] ?? '')?>"></label></div>
  <div class="form-row"><label>Rating<input name="rating" type="number" min="0" max="5" value="<?=htmlspecialchars($product['rating'] ?? '')?>"></label></div>
  <div class="form-row"><label>Current Image<?php if(!empty($product['image_url'])): ?> <img src="<?=htmlspecialchars($product['image_url'])?>" style="height:48px;display:block;margin:8px 0;"><?php endif; ?><input type="file" name="image" accept="image/*"></label></div>
  <div><button type="submit">Save</button> <a class="btn" href="/admin/products/index.php">Cancel</a></div>
</form>

<?php include __DIR__ . '/../includes/footer.php';
