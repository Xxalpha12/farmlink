<?php
/**
 * Session bootstrap + auth helper functions
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_role($role) {
    global $user;
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        header('Location: /login.php');
        exit;
    }
}

function require_login() {
    global $user;
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
    $user = current_user();
}

function redirect_to_dashboard() {
    $user = current_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    switch ($user['role']) {
        case 'farmer':
            header('Location: /farmer/dashboard.php');
            break;
        case 'buyer':
            header('Location: /buyer/browse.php');
            break;
        case 'admin':
            header('Location: /admin/dashboard.php');
            break;
    }
    exit;
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
