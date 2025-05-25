<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in and if user has required permissions
if (!isset($_SESSION['user_id'])) {
    // Redirect to the main about page using an absolute path
    header('Location: /ASM/System/about.php');
    exit;
}

// If you want to check role, e.g. admin only
if ($_SESSION['role'] !== 'admin') {
    // Redirect unauthorized users to about page as well
    header('Location: /ASM/System/about.php');
    exit;
}
?>
