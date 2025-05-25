<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine login state and role
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';

// Redirect users based on login state
if ($isLoggedIn) {
    header('Location: /ASM/System/dashboard/index.php');
    exit;
} else {
    header('Location: /ASM/System/about.php');
    exit;
}
?>
