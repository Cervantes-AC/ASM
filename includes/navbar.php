<?php
// includes/navbar.php
session_start();
$userRole = $_SESSION['role'] ?? 'guest';
?>
<nav>
    <ul>
        <li><a href="../System/dashboard/index.php">Dashboard</a></li>
        <li><a href="../System/assets/list.php">Assets</a></li>
        <li><a href="../System/borrow/request.php">Borrow</a></li>
        <li><a href="../System/users/profile.php">Profile</a></li>

        <?php if ($userRole === 'admin'): ?>
            <li><a href="../System/users/list.php">Manage Users</a></li>
            <li><a href="../System/fines/manage.php">Fines</a></li>
            <li><a href="../System/logs/view_logs.php">Logs</a></li>
        <?php endif; ?>

        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>
