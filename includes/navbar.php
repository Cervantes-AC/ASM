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
        padding: 0.5rem 1.5rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    nav .logo {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        color: white;
        font-weight: 700;
        font-size: 1.3rem;
        user-select: none;
    }

    nav .logo img {
        height: 40px;
        width: auto;
        object-fit: contain;
        border-radius: 4px;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.4);
    }

    nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 1.2rem;
        align-items: center;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    nav ul li a:hover,
    nav ul li a:focus {
        background-color: #0056b3;
        color: #e0eaff;
        outline: none;
    }

    /* Responsive tweak for smaller widths */
    @media (max-width: 600px) {
        nav {
            flex-direction: column;
            align-items: flex-start;
        }
        nav ul {
            flex-wrap: wrap;
            gap: 0.7rem;
            margin-top: 0.5rem;
        }
    }
</style>

<nav>
    <a href="/ASM/System/dashboard/index.php" class="logo" aria-label="CMU-SSC Home">
        <img src="/ASM/includes/logo.jpg" alt="CMU-SSC Logo" />
        CMU-SSC
    </a>

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
