<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Run this once in your browser (e.g. http://localhost/farmlink/install.php)
 * after importing sql/schema.sql. It creates the admin account, then
 * you should delete this file for security.
 */
require_once __DIR__ . '/includes/db.php';

$done = false;
$error = '';

$check = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();

if ($check) {
    $error = 'An admin account already exists. Delete install.php now for security.';
} else {
    $hash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (role, full_name, email, phone, password_hash, status)
                            VALUES ('admin', 'Farm Link Administrator', 'admin@farmlink.com', '0000000000', ?, 'active')");
    $stmt->execute([$hash]);
    $done = true;
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Farm Link Setup</title>
<style>body{font-family:sans-serif;max-width:600px;margin:60px auto;padding:0 20px;color:#24291f;}
.box{background:#f7faf3;border:1px solid #cfe3d2;border-radius:10px;padding:24px;}
code{background:#eee;padding:2px 6px;border-radius:4px;}</style></head>
<body>
<h2>🌾 Farm Link — Setup</h2>
<?php if ($done): ?>
  <div class="box">
    <p><strong>Admin account created successfully.</strong></p>
    <p>Login email: <code>admin@farmlink.com</code><br>
    Password: <code>Admin@123</code></p>
    <p>Please change this password after your first login, and <strong>delete install.php</strong> from the server now.</p>
  </div>
<?php else: ?>
  <div class="box"><p><?= htmlspecialchars($error) ?></p></div>
<?php endif; ?>
</body></html>
