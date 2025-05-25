<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /ASM/System/about.php');
    exit;
}

// Allow only 'member' or 'admin'
if ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'admin') {
    header('Location: /ASM/System/about.php');
    exit;
}
?>
