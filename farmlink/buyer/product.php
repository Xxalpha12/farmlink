<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
$page_title = 'Produce Details';
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, u.full_name AS farmer_name, u.phone AS farmer_phone, u.company_name
                        FROM products p JOIN users u ON u.id = p.farmer_id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Product not found.');
    header('Location: /buyer/browse.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        flash_set('error', 'Please log in as a buyer to place an order.');
        header('Location: /login.php');
        exit;
    }
    if ($user['role'] !== 'buyer') {
        $errors[] = 'Only buyer accounts can place orders.';
    }

    $quantity = (int)($_POST['quantity'] ?? 0);
    $address = trim($_POST['delivery_address'] ?? '');

    if ($quantity < 1) $errors[] = 'Enter a valid quantity.';
    if ($quantity > $product['quantity']) $errors[] = 'Only ' . $product['quantity'] . ' ' . $product['unit'] . ' available.';
    if ($address === '') $errors[] = 'Delivery address is required.';

    if (!$errors) {
        $total = $quantity * $product['price'];
        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare("INSERT INTO orders (buyer_id, product_id, farmer_id, quantity, unit_price, total_price, delivery_address)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$user['id'], $product['id'], $product['farmer_id'], $quantity, $product['price'], $total, $address]);

            $upd = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
            $upd->execute([$quantity, $product['id'], $quantity]);

            if ($upd->rowCount() === 0) {
                throw new Exception('Not enough stock available.');
            }
            $pdo->commit();
            flash_set('success', 'Order placed successfully! You can track it in "My Orders".');
            header('Location: /buyer/orders.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not place order: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="detail-grid">
  <div>
    <img src="<?= $product['image'] ? '/uploads/' . h($product['image']) : 'https://placehold.co/500x340/2f6b40/ffffff?text=' . urlencode($product['product_name']) ?>" alt="<?= h($product['product_name']) ?>">
  </div>
  <div>
    <span class="cat-tag"><?= h($product['category']) ?></span>
    <h2 style="margin:8px 0;"><?= h($product['product_name']) ?></h2>
    <p class="price" style="font-size:1.4rem;">₦<?= number_format($product['price'], 2) ?> / <?= h($product['unit']) ?></p>
    <p class="meta">Sold by <strong><?= h($product['company_name'] ?: $product['farmer_name']) ?></strong> &middot; <?= h($product['farmer_phone']) ?></p>
    <p class="meta"><?= (int)$product['quantity'] ?> <?= h($product['unit']) ?> in stock</p>
    <p><?= nl2br(h($product['description'])) ?></p>

    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

    <?php if ($product['quantity'] < 1): ?>
      <div class="alert alert-error">This produce is currently out of stock.</div>
    <?php elseif ($user && $user['role'] === 'farmer'): ?>
      <div class="alert alert-error">Farmer accounts cannot place orders.</div>
    <?php else: ?>
      <form method="POST" class="card" style="margin-top:16px;">
        <div class="form-group">
          <label for="orderQuantity">Quantity (<?= h($product['unit']) ?>)</label>
          <input type="number" id="orderQuantity" name="quantity" min="1" max="<?= (int)$product['quantity'] ?>"
                 data-unit-price="<?= h($product['price']) ?>" value="1" required>
        </div>
        <div class="form-group">
          <label for="delivery_address">Delivery Address</label>
          <textarea id="delivery_address" name="delivery_address" placeholder="Where should this order be delivered?" required></textarea>
        </div>
        <p>Total: <strong id="orderTotal">₦<?= number_format($product['price'], 2) ?></strong></p>
        <button type="submit" class="btn btn-primary btn-block"><?= $user ? 'Place Order' : 'Log In to Order' ?></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
