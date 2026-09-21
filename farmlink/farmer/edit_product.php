<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_role('farmer');
$page_title = 'Edit Produce';

$categories = ['Grains', 'Tubers', 'Vegetables', 'Fruits', 'Livestock', 'Poultry', 'Dairy', 'Other'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND farmer_id = ?");
$stmt->execute([$id, $user['id']]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Listing not found or you do not have permission to edit it.');
    header('Location: /farmer/my_products.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $product_name = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $unit = trim($_POST['unit'] ?? 'kg');
    $description = trim($_POST['description'] ?? '');

    if ($category === '') $errors[] = 'Please select a product category.';
    if ($product_name === '') $errors[] = 'Product name is required.';
    if (!is_numeric($price) || $price <= 0) $errors[] = 'Enter a valid price.';
    if (!ctype_digit($quantity) || (int)$quantity < 0) $errors[] = 'Enter a valid quantity.';

    $imageName = $product['image'];
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
        if ($imageName !== $product['image'] && !empty($_FILES['image']['name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
        }
        $stmt = $pdo->prepare("UPDATE products SET category=?, product_name=?, price=?, quantity=?, unit=?, description=?, image=? WHERE id=? AND farmer_id=?");
        $stmt->execute([$category, $product_name, $price, $quantity, $unit, $description, $imageName, $id, $user['id']]);
        flash_set('success', 'Listing updated successfully.');
        header('Location: /farmer/my_products.php');
        exit;
    }
    $product = array_merge($product, compact('category', 'product_name', 'price', 'quantity', 'unit', 'description'));
}

include __DIR__ . '/../includes/header.php';
?>

<div class="form-card wide">
  <h2 style="color:var(--green-900);">Edit Produce</h2>
  <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-row">
      <div class="form-group">
        <label for="category">Product Category</label>
        <select id="category" name="category" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= h($c) ?>" <?= $product['category'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="product_name">Product Name</label>
        <input type="text" id="product_name" name="product_name" value="<?= h($product['product_name']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="price">Price (₦)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?= h($product['price']) ?>" required>
      </div>
      <div class="form-group">
        <label for="quantity">Quantity Available</label>
        <input type="number" id="quantity" name="quantity" min="0" value="<?= h($product['quantity']) ?>" required>
      </div>
      <div class="form-group">
        <label for="unit">Unit</label>
        <select id="unit" name="unit">
          <?php foreach (['kg', 'bag', 'basket', 'crate', 'unit', 'litre'] as $u): ?>
            <option value="<?= $u ?>" <?= $product['unit'] === $u ? 'selected' : '' ?>><?= $u ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description"><?= h($product['description']) ?></textarea>
    </div>

    <?php if ($product['image']): ?>
      <div class="form-group">
        <label>Current Photo</label>
        <img src="/uploads/<?= h($product['image']) ?>" style="max-width:160px;border-radius:8px;">
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label for="image">Replace Photo (optional)</label>
      <input type="file" id="image" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
