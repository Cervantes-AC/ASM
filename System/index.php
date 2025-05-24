<?php
session_start();

// Check if user is logged in by checking session variables
if (isset($_SESSION['user_id'])) {
    // User logged in — redirect to dashboard
    header('Location: /dashboard/index.php');
    exit;
} else {
    // Not logged in — redirect to login page
    header('Location: ./auth/login.php');
    exit;
}
