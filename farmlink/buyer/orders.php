<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('buyer');
$page_title = 'My Orders';

$stmt = $pdo->prepare("SELECT o.*, p.product_name, p.category, u.full_name AS farmer_name, u.phone AS farmer_phone
                        FROM orders o
                        JOIN products p ON p.id = o.product_id
                        JOIN users u ON u.id = o.farmer_id
                        WHERE o.buyer_id = ?
                        ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Orders</h2>
<?php flash_render(); ?>

<?php if (empty($orders)): ?>
  <div class="empty-state">You haven't placed any orders yet. <a href="/buyer/browse.php">Browse produce</a> to get started.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Order ID</th><th>Product</th><th>Category</th><th>Qty</th><th>Total</th><th>Farmer</th><th>Status</th><th>Ordered</th></tr>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></td>
          <td><?= h($o['product_name']) ?></td>
          <td><?= h($o['category']) ?></td>
          <td><?= (int)$o['quantity'] ?></td>
          <td>₦<?= number_format($o['total_price'], 2) ?></td>
          <td><?= h($o['farmer_name']) ?><br><span class="meta" style="color:var(--ink-soft);font-size:0.8rem;"><?= h($o['farmer_phone']) ?></span></td>
          <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
          <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
