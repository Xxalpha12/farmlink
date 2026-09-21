<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND farmer_id = ?");
$stmt->execute([$id, $user['id']]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Listing not found or you do not have permission to delete it.');
} else {
    $del = $pdo->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
    $del->execute([$id, $user['id']]);
    if ($product['image'] && file_exists(__DIR__ . '/../uploads/' . $product['image'])) {
        unlink(__DIR__ . '/../uploads/' . $product['image']);
    }
    flash_set('success', 'Listing deleted.');
}

header('Location: /farmer/my_products.php');
exit;
