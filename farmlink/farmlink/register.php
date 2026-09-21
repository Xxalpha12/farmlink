<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
$page_title = 'Register';

if (is_logged_in()) {
    redirect_to_dashboard();
}

$errors = [];
$old = ['role' => 'buyer', 'full_name' => '', 'company_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['role'] = $_POST['role'] ?? 'buyer';
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['company_name'] = trim($_POST['company_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!in_array($old['role'], ['farmer', 'buyer'], true)) $errors[] = 'Invalid account type.';
    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($old['phone'] === '') $errors[] = 'Phone number is required.';
    if ($old['role'] === 'farmer' && $old['company_name'] === '') $errors[] = 'Company/Farm name is required for farmers.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (role, full_name, company_name, email, phone, password_hash)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $old['role'],
            $old['full_name'],
            $old['role'] === 'farmer' ? $old['company_name'] : null,
            $old['email'],
            $old['phone'],
            $hash,
        ]);
        flash_set('success', 'Account created successfully. Please log in.');
        header('Location: /login.php');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="form-card wide">
  <h2 style="text-align:center; color:var(--green-900);">Create Your Farm Link Account</h2>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" id="registerForm" novalidate>
    <div class="role-toggle">
      <label><input type="radio" name="role" value="buyer" <?= $old['role'] === 'buyer' ? 'checked' : '' ?>><span>🛒 I'm a Buyer</span></label>
      <label><input type="radio" name="role" value="farmer" <?= $old['role'] === 'farmer' ? 'checked' : '' ?>><span>🌾 I'm a Farmer</span></label>
    </div>

    <div class="form-group">
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" value="<?= h($old['full_name']) ?>" required>
    </div>

    <div class="form-group" id="companyGroup">
      <label for="company_name">Company / Farm Name <span style="font-weight:400;color:var(--ink-soft)">(farmers only)</span></label>
      <input type="text" id="company_name" name="company_name" value="<?= h($old['company_name']) ?>">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($old['email']) ?>" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" value="<?= h($old['phone']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
  </form>

  <div class="form-footer-link">Already have an account? <a href="/login.php">Log in</a></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
