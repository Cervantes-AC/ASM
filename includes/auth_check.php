<?php
session_start();

/**
 * Check if user is logged in.
 * If not, redirect to login page.
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit();
    }
}

/**
 * Check if logged-in user has one of the allowed roles.
 * @param array|string $allowedRoles Single role or array of roles.
 * Redirects to dashboard or access denied page if not authorized.
 */
function checkRole($allowedRoles) {
    if (!isset($_SESSION['role'])) {
        header('Location: /auth/login.php');
        exit();
    }

    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // Optional: redirect to dashboard or show access denied
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied.";
        exit();
    }
}
