<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');
$page_title = 'Farmer Dashboard';
$farmer_id = $user['id'];

$productCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE farmer_id = ?");
$productCount->execute([$farmer_id]);
$productCount = $productCount->fetchColumn();

$pendingCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = ? AND status = 'pending'");
$pendingCount->execute([$farmer_id]);
$pendingCount = $pendingCount->fetchColumn();

$deliveredCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = ? AND status = 'delivered'");
$deliveredCount->execute([$farmer_id]);
$deliveredCount = $deliveredCount->fetchColumn();

$revenue = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE farmer_id = ? AND status = 'delivered'");
$revenue->execute([$farmer_id]);
$revenue = $revenue->fetchColumn();

$recentProducts = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5");
$recentProducts->execute([$farmer_id]);
$recentProducts = $recentProducts->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Welcome back, <?= h($user['full_name']) ?></h2>
<?php flash_render(); ?>

<div class="stat-cards">
  <div class="stat-card"><div class="num"><?= (int)$productCount ?></div><div class="label">My Listings</div></div>
  <div class="stat-card"><div class="num"><?= (int)$pendingCount ?></div><div class="label">Pending Orders</div></div>
  <div class="stat-card"><div class="num"><?= (int)$deliveredCount ?></div><div class="label">Delivered Orders</div></div>
  <div class="stat-card"><div class="num">₦<?= number_format($revenue, 0) ?></div><div class="label">Revenue (Delivered)</div></div>
</div>

<div class="action-row" style="margin-bottom:30px;">
  <a href="/farmer/add_product.php" class="btn btn-primary">+ Add New Produce</a>
  <a href="/farmer/my_products.php" class="btn btn-outline" style="border-color:var(--green-600);color:var(--green-700);">Manage My Listings</a>
  <a href="/farmer/orders.php" class="btn btn-outline" style="border-color:var(--green-600);color:var(--green-700);">View Orders</a>
</div>

<h3 class="section-title">Recently Added Produce</h3>
<?php if (empty($recentProducts)): ?>
  <div class="empty-state">You haven't listed any produce yet. <a href="/farmer/add_product.php">Add your first listing</a>.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Product</th><th>Category</th><th>Price</th><th>Qty</th><th>Listed</th></tr>
      <?php foreach ($recentProducts as $p): ?>
        <tr>
          <td><?= h($p['product_name']) ?></td>
          <td><?= h($p['category']) ?></td>
          <td>₦<?= number_format($p['price'], 2) ?></td>
          <td><?= (int)$p['quantity'] ?> <?= h($p['unit']) ?></td>
          <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
