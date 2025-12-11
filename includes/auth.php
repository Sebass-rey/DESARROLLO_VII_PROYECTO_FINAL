<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_rol'] ?? '') === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /legalsmart/login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: /legalsmart/index.php");
        exit;
    }
}
