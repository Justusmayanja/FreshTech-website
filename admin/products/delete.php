<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /admin/products/index.php'); exit; }
if (!verify_csrf($_POST['_csrf'] ?? '')) { $_SESSION['flash_error']='Invalid CSRF'; header('Location: /admin/products/index.php'); exit; }
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: /admin/products/index.php'); exit; }

$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute(['id'=>$id]);
$_SESSION['flash_success'] = 'Product deleted.';
header('Location: /admin/products/index.php');
exit;
