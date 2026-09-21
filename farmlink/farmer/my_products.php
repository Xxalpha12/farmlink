<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');
$page_title = 'My Products';

$stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Products</h2>
<?php flash_render(); ?>
<div class="action-row" style="margin-bottom:20px;">
  <a href="/farmer/add_product.php" class="btn btn-primary">+ Add New Produce</a>
</div>

<?php if (empty($products)): ?>
  <div class="empty-state">No listings yet. <a href="/farmer/add_product.php">Add your first product</a>.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Product</th><th>Category</th><th>Price</th><th>Qty</th><th>Description</th><th>Action</th></tr>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= h($p['product_name']) ?></td>
          <td><?= h($p['category']) ?></td>
          <td>₦<?= number_format($p['price'], 2) ?></td>
          <td><?= (int)$p['quantity'] ?> <?= h($p['unit']) ?></td>
          <td><?= h(mb_strimwidth($p['description'] ?? '', 0, 40, '…')) ?></td>
          <td class="action-row">
            <a href="/farmer/edit_product.php?id=<?= (int)$p['id'] ?>" class="btn btn-small btn-outline" style="border-color:var(--green-600);color:var(--green-700);">Edit</a>
            <a href="/farmer/delete_product.php?id=<?= (int)$p['id'] ?>" class="btn btn-small btn-danger" data-confirm="Delete this listing? This cannot be undone.">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
