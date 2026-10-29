<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ../auth/login.php');
        exit;
    }
}
