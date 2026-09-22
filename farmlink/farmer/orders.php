<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');
$page_title = 'My Orders';

// Handle status update (Pending -> Delivered)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'] === 'delivered' ? 'delivered' : 'pending';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$newStatus, $orderId, $user['id']]);
    flash_set('success', 'Order status updated.');
    header('Location: /farmer/orders.php?tab=' . ($_GET['tab'] ?? 'pending'));
    exit;
}

$tab = $_GET['tab'] ?? 'pending';
$tab = in_array($tab, ['pending', 'delivered'], true) ? $tab : 'pending';

$stmt = $pdo->prepare("SELECT o.*, p.product_name, p.category, u.full_name AS buyer_name, u.phone AS buyer_phone
                        FROM orders o
                        JOIN products p ON p.id = o.product_id
                        JOIN users u ON u.id = o.buyer_id
                        WHERE o.farmer_id = ? AND o.status = ?
                        ORDER BY o.created_at DESC");
$stmt->execute([$user['id'], $tab]);
$orders = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Orders</h2>
<?php flash_render(); ?>

<div class="tabs">
  <a href="/farmer/orders.php?tab=pending" class="<?= $tab === 'pending' ? 'active' : '' ?>">Pending Deliveries</a>
  <a href="/farmer/orders.php?tab=delivered" class="<?= $tab === 'delivered' ? 'active' : '' ?>">Completed Deliveries</a>
</div>

<?php if (empty($orders)): ?>
  <div class="empty-state">No <?= $tab ?> orders.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Order ID</th><th>Product</th><th>Category</th><th>Qty</th><th>Total Price</th><th>Buyer</th><th>Status</th><?= $tab === 'pending' ? '<th>Action</th>' : '' ?></tr>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></td>
          <td><?= h($o['product_name']) ?></td>
          <td><?= h($o['category']) ?></td>
          <td><?= (int)$o['quantity'] ?></td>
          <td>₦<?= number_format($o['total_price'], 2) ?></td>
          <td><?= h($o['buyer_name']) ?><br><span class="meta" style="color:var(--ink-soft);font-size:0.8rem;"><?= h($o['buyer_phone']) ?></span></td>
          <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
          <?php if ($tab === 'pending'): ?>
          <td>
            <form method="POST" action="/farmer/orders.php?tab=pending">
              <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
              <input type="hidden" name="status" value="delivered">
              <button type="submit" class="btn btn-small btn-primary">Mark Delivered</button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
