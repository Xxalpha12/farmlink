<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Home';

$stmt = $pdo->query("SELECT p.*, u.full_name AS farmer_name FROM products p
                      JOIN users u ON u.id = p.farmer_id
                      WHERE p.quantity > 0
                      ORDER BY p.created_at DESC LIMIT 8");
$featured = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h1>Linking Farmers Directly to Buyers</h1>
  <p>Farm Link removes the middleman. Farmers list their produce, buyers search and order directly &mdash; fair prices, faster reach, no wasted harvest.</p>
  <div class="hero-actions">
    <a href="/buyer/browse.php" class="btn btn-amber">Browse Produce</a>
    <?php if (!$user): ?>
      <a href="/register.php" class="btn btn-outline">Join as a Farmer</a>
    <?php endif; ?>
  </div>
</section>

<h2 class="section-title">Recently Listed Produce</h2>

<?php if (empty($featured)): ?>
  <div class="empty-state">No produce listed yet. Check back soon, or <a href="/register.php">register as a farmer</a> to be the first.</div>
<?php else: ?>
  <div class="grid">
    <?php foreach ($featured as $p): ?>
      <div class="card product-card">
        <img src="<?= $p['image'] ? '/uploads/' . h($p['image']) : 'https://placehold.co/400x260/2f6b40/ffffff?text=' . urlencode($p['product_name']) ?>" alt="<?= h($p['product_name']) ?>">
        <span class="cat-tag"><?= h($p['category']) ?></span>
        <h3><?= h($p['product_name']) ?></h3>
        <div class="price">₦<?= number_format($p['price'], 2) ?> / <?= h($p['unit']) ?></div>
        <div class="meta">By <?= h($p['farmer_name']) ?> &middot; <?= (int)$p['quantity'] ?> <?= h($p['unit']) ?> available</div>
        <a href="/buyer/product.php?id=<?= (int)$p['id'] ?>" class="btn btn-primary btn-small">View & Order</a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<h2 class="section-title">How It Works</h2>
<div class="grid">
  <div class="card">
    <h3>1. Farmers List Produce</h3>
    <p class="meta">Farmers register, then add produce with category, price, quantity and description.</p>
  </div>
  <div class="card">
    <h3>2. Buyers Search & Order</h3>
    <p class="meta">Buyers browse or search listings by name or category and place an order directly.</p>
  </div>
  <div class="card">
    <h3>3. Delivery is Tracked</h3>
    <p class="meta">Farmers update order status from Pending to Delivered, and buyers can track it.</p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
