<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // Redirect logged-in users to the dashboard
    header('Location: ./dashboard/index.php');
    exit;
} else {
    // Redirect guests to the about page or login
    header('Location: ./about.php'); // or use './auth/login.php' if preferred
    exit;
}
