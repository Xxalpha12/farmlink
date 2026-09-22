<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');
$page_title = 'Add Produce';

$categories = ['Grains', 'Tubers', 'Vegetables', 'Fruits', 'Livestock', 'Poultry', 'Dairy', 'Other'];
$errors = [];
$old = ['category' => '', 'product_name' => '', 'price' => '', 'quantity' => '', 'unit' => 'kg', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['category'] = trim($_POST['category'] ?? '');
    $old['product_name'] = trim($_POST['product_name'] ?? '');
    $old['price'] = trim($_POST['price'] ?? '');
    $old['quantity'] = trim($_POST['quantity'] ?? '');
    $old['unit'] = trim($_POST['unit'] ?? 'kg');
    $old['description'] = trim($_POST['description'] ?? '');

    if ($old['category'] === '') $errors[] = 'Please select a product category.';
    if ($old['product_name'] === '') $errors[] = 'Product name is required.';
    if (!is_numeric($old['price']) || $old['price'] <= 0) $errors[] = 'Enter a valid price.';
    if (!ctype_digit($old['quantity']) || (int)$old['quantity'] <= 0) $errors[] = 'Enter a valid quantity.';

    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Image must be JPG, PNG or WEBP.';
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Image must be under 3MB.';
        } else {
            $imageName = 'prod_' . uniqid() . '.' . $ext;
        }
    }

    if (!$errors) {
        if ($imageName) {
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
        }
        $stmt = $pdo->prepare("INSERT INTO products (farmer_id, category, product_name, price, quantity, unit, description, image)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'], $old['category'], $old['product_name'], $old['price'],
            $old['quantity'], $old['unit'], $old['description'], $imageName,
        ]);
        flash_set('success', 'Produce listed successfully.');
        header('Location: /farmer/my_products.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="form-card wide">
  <h2 style="color:var(--green-900);">Add New Produce</h2>
  <p class="meta" style="color:var(--ink-soft);margin-top:-8px;">Listed under: <strong><?= h($user['full_name']) ?></strong></p>

  <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-row">
      <div class="form-group">
        <label for="category">Product Category</label>
        <select id="category" name="category" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= h($c) ?>" <?= $old['category'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="product_name">Product Name</label>
        <input type="text" id="product_name" name="product_name" value="<?= h($old['product_name']) ?>" placeholder="e.g. Garri, Yam, Tomatoes" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="price">Price (₦)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?= h($old['price']) ?>" required>
      </div>
      <div class="form-group">
        <label for="quantity">Quantity Available</label>
        <input type="number" id="quantity" name="quantity" min="1" value="<?= h($old['quantity']) ?>" required>
      </div>
      <div class="form-group">
        <label for="unit">Unit</label>
        <select id="unit" name="unit">
          <?php foreach (['kg', 'bag', 'basket', 'crate', 'unit', 'litre'] as $u): ?>
            <option value="<?= $u ?>" <?= $old['unit'] === $u ? 'selected' : '' ?>><?= $u ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" placeholder="Describe quality, origin, harvest date etc."><?= h($old['description']) ?></textarea>
    </div>

    <div class="form-group">
      <label for="image">Product Photo (optional)</label>
      <input type="file" id="image" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary btn-block">List Produce</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
