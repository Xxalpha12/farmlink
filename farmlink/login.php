<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
$page_title = 'Login';

if (is_logged_in()) {
    redirect_to_dashboard();
}

$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$old_email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Incorrect email or password.';
    } elseif ($user['status'] === 'suspended') {
        $errors[] = 'This account has been suspended. Contact the administrator.';
    } else {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
        ];
        redirect_to_dashboard();
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="form-card">
  <h2 style="text-align:center; color:var(--green-900);">Welcome Back</h2>
  <?php flash_render(); ?>
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= h($old_email) ?>" required autofocus>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Log In</button>
  </form>

  <div class="form-footer-link">New to Farm Link? <a href="/register.php">Create an account</a></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
