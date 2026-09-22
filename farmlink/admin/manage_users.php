<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('admin');
$page_title = 'Manage Users';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $target = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role != 'admin'");
    $target->execute([$targetId]);
    $target = $target->fetch();

    if ($target) {
        if ($action === 'suspend') {
            $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$targetId]);
            flash_set('success', $target['full_name'] . ' has been suspended.');
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$targetId]);
            flash_set('success', $target['full_name'] . ' has been reactivated.');
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$targetId]);
            flash_set('success', $target['full_name'] . ' account deleted.');
        }
    } else {
        flash_set('error', 'User not found.');
    }
    header('Location: /admin/manage_users.php?role=' . urlencode($_GET['role'] ?? 'farmer'));
    exit;
}

$roleFilter = $_GET['role'] ?? 'farmer';
$roleFilter = in_array($roleFilter, ['farmer', 'buyer'], true) ? $roleFilter : 'farmer';

$stmt = $pdo->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM products p WHERE p.farmer_id = u.id) AS listing_count
    FROM users u WHERE u.role = ? ORDER BY u.created_at DESC");
$stmt->execute([$roleFilter]);
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Manage Users</h2>
<?php flash_render(); ?>

<div class="tabs">
  <a href="/admin/manage_users.php?role=farmer" class="<?= $roleFilter === 'farmer' ? 'active' : '' ?>">Farmers</a>
  <a href="/admin/manage_users.php?role=buyer" class="<?= $roleFilter === 'buyer' ? 'active' : '' ?>">Buyers</a>
</div>

<?php if (empty($users)): ?>
  <div class="empty-state">No <?= $roleFilter ?>s registered yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <tr>
        <th>Name</th><th>Email</th><th>Phone</th>
        <?php if ($roleFilter === 'farmer'): ?><th>Listings</th><?php endif; ?>
        <th>Status</th><th>Joined</th><th>Action</th>
      </tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= h($u['full_name']) ?><?= $u['company_name'] ? '<br><span class="meta" style="color:var(--ink-soft);font-size:0.8rem;">' . h($u['company_name']) . '</span>' : '' ?></td>
          <td><?= h($u['email']) ?></td>
          <td><?= h($u['phone']) ?></td>
          <?php if ($roleFilter === 'farmer'): ?><td><?= (int)$u['listing_count'] ?></td><?php endif; ?>
          <td><span class="badge badge-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td class="action-row">
            <form method="POST">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <?php if ($u['status'] === 'active'): ?>
                <input type="hidden" name="action" value="suspend">
                <button type="submit" class="btn btn-small btn-outline" style="border-color:var(--amber-600);color:var(--amber-600);">Suspend</button>
              <?php else: ?>
                <input type="hidden" name="action" value="activate">
                <button type="submit" class="btn btn-small btn-primary">Activate</button>
              <?php endif; ?>
            </form>
            <form method="POST" onsubmit="return confirm('Permanently delete this account and all associated data?');">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit" class="btn btn-small btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
