<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<style>
    nav {
        background-color: #007bff;
        padding: 0.75rem 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    nav ul li a:hover {
        background-color: #0056b3;
    }
</style>

<nav>
    <ul>
        <li><a href="/ASM/System/dashboard/index.php">Dashboard</a></li>
        <li><a href="/ASM/System/about.php">About Us</a></li>

        <?php if ($isLoggedIn): ?>
            <?php if ($userRole === 'member'): ?>
                <li><a href="/ASM/System/assets/list.php">Assets</a></li>
                <li><a href="/ASM/System/borrow/return.php">Return Item</a></li>
                <li><a href="/ASM/System/fines/manage.php">Fines</a></li>

            <?php elseif ($userRole === 'admin'): ?>
                <li><a href="/ASM/System/assets/list.php">Assets</a></li>
                <li><a href="/ASM/System/borrow/manage_requests.php">Borrow</a></li>
                <li><a href="/ASM/System/users/list.php">Manage Users</a></li>
                <li><a href="/ASM/System/fines/manage.php">Fines</a></li>
                <li><a href="/ASM/System/logs/view_logs.php">Logs</a></li>
            <?php endif; ?>

            <li><a href="/ASM/System/users/profile.php">Profile</a></li>
            <li><a href="/ASM/System/auth/logout.php">Logout</a></li>

        <?php else: ?>
            <li><a href="/ASM/System/auth/login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>
