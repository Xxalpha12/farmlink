<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
$page_title = 'Browse Produce';

$categories = ['Grains', 'Tubers', 'Vegetables', 'Fruits', 'Livestock', 'Poultry', 'Dairy', 'Other'];

$stmt = $pdo->query("SELECT p.*, u.full_name AS farmer_name FROM products p
                      JOIN users u ON u.id = p.farmer_id
                      WHERE p.quantity > 0
                      ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Browse Farm Produce</h2>
<?php flash_render(); ?>

<div class="search-bar">
  <input type="text" id="liveSearch" placeholder="Search produce by name...">
  <select id="categoryFilter">
    <option value="">All Categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= h($c) ?>"><?= h($c) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<?php if (empty($products)): ?>
  <div class="empty-state">No produce available right now. Please check back later.</div>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <div class="card product-card" data-product-card data-name="<?= h(strtolower($p['product_name'])) ?>" data-category="<?= h($p['category']) ?>">
        <img src="<?= $p['image'] ? '/uploads/' . h($p['image']) : 'https://placehold.co/400x260/2f6b40/ffffff?text=' . urlencode($p['product_name']) ?>" alt="<?= h($p['product_name']) ?>">
        <span class="cat-tag"><?= h($p['category']) ?></span>
        <h3><?= h($p['product_name']) ?></h3>
        <div class="price">₦<?= number_format($p['price'], 2) ?> / <?= h($p['unit']) ?></div>
        <div class="meta">By <?= h($p['farmer_name']) ?> &middot; <?= (int)$p['quantity'] ?> <?= h($p['unit']) ?> available</div>
        <a href="/buyer/product.php?id=<?= (int)$p['id'] ?>" class="btn btn-primary btn-small">View & Order</a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="empty-state" id="noResults" style="display:none;">No produce matches your search.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
