<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('admin');
$page_title = 'Monitor Listings';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    flash_set('success', 'Listing removed.');
    header('Location: /admin/manage_products.php');
    exit;
}

$stmt = $pdo->query("SELECT p.*, u.full_name AS farmer_name FROM products p
                      JOIN users u ON u.id = p.farmer_id
                      ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Monitor Listings</h2>
<?php flash_render(); ?>

<?php if (empty($products)): ?>
  <div class="empty-state">No listings yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Product</th><th>Category</th><th>Farmer</th><th>Price</th><th>Qty</th><th>Listed</th><th>Action</th></tr>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= h($p['product_name']) ?></td>
          <td><?= h($p['category']) ?></td>
          <td><?= h($p['farmer_name']) ?></td>
          <td>₦<?= number_format($p['price'], 2) ?></td>
          <td><?= (int)$p['quantity'] ?> <?= h($p['unit']) ?></td>
          <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Remove this listing?');">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="btn btn-small btn-danger">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
