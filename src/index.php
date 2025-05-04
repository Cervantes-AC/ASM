<?php
require_once 'includes/auth_check.php';

// Redirect user based on role (example dashboard)

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        $dashboard = 'users/list.php';
        break;
    case 'staff':
        $dashboard = 'assets/list.php';
        break;
    case 'member':
        $dashboard = 'borrow/request.php';
        break;
    default:
        $dashboard = 'auth/login.php';
}

header("Location: $dashboard");
exit();
?>
