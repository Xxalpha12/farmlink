<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('admin');
$page_title = 'Admin Dashboard';

$farmerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='farmer'")->fetchColumn();
$buyerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$gmv = $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders")->fetchColumn();

$recentUsers = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Admin Overview</h2>
<?php flash_render(); ?>

<div class="stat-cards">
  <div class="stat-card"><div class="num"><?= (int)$farmerCount ?></div><div class="label">Farmers</div></div>
  <div class="stat-card"><div class="num"><?= (int)$buyerCount ?></div><div class="label">Buyers</div></div>
  <div class="stat-card"><div class="num"><?= (int)$productCount ?></div><div class="label">Active Listings</div></div>
  <div class="stat-card"><div class="num"><?= (int)$orderCount ?></div><div class="label">Total Orders</div></div>
  <div class="stat-card"><div class="num"><?= (int)$pendingOrders ?></div><div class="label">Pending Orders</div></div>
  <div class="stat-card"><div class="num">₦<?= number_format($gmv, 0) ?></div><div class="label">Total Order Value</div></div>
</div>

<div class="action-row" style="margin-bottom:30px;">
  <a href="/admin/manage_users.php" class="btn btn-primary">Manage Users</a>
  <a href="/admin/manage_products.php" class="btn btn-outline" style="border-color:var(--green-600);color:var(--green-700);">Monitor Listings</a>
</div>

<h3 class="section-title">Recently Registered</h3>
<?php if (empty($recentUsers)): ?>
  <div class="empty-state">No users yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th></tr>
      <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= h($u['full_name']) ?></td>
          <td><?= ucfirst($u['role']) ?></td>
          <td><?= h($u['email']) ?></td>
          <td><?= h($u['phone']) ?></td>
          <td><span class="badge badge-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
