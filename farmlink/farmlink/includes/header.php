<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$page_title = $page_title ?? 'Farm Link';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?> · Farm Link</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="nav-wrap">
    <a href="/index.php" class="logo">🌾 Farm<span>Link</span></a>
    <nav class="main-nav">
      <a href="/index.php">Home</a>
      <a href="/buyer/browse.php">Browse Produce</a>
      <?php if (!$user): ?>
        <a href="/login.php">Login</a>
        <a href="/register.php" class="btn-nav">Register</a>
      <?php else: ?>
        <?php if ($user['role'] === 'farmer'): ?>
          <a href="/farmer/dashboard.php">My Dashboard</a>
        <?php elseif ($user['role'] === 'buyer'): ?>
          <a href="/buyer/orders.php">My Orders</a>
        <?php elseif ($user['role'] === 'admin'): ?>
          <a href="/admin/dashboard.php">Admin Panel</a>
        <?php endif; ?>
        <span class="nav-user">Hi, <?= h($user['full_name']) ?></span>
        <a href="/logout.php" class="btn-nav">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="site-main">
