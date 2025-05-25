<?php
// System/dashboard/index.php

session_start();
require_once '../config/db.php';

// Determine login state and role
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role']       ?? 'guest';
$username   = $_SESSION['full_name']  ?? 'Guest';

// If logged in, fetch stats
if ($isLoggedIn) {
    if ($userRole === 'admin') {
        $totalAssets    = $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
        $borrowedAssets = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status='approved'")->fetchColumn();
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_requests WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$_SESSION['user_id']]);
        $borrowedAssets = $stmt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Asset Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        nav { background: #007bff; padding: .75rem; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; justify-content: center; gap: 1rem; }
        nav a { color: white; text-decoration: none; padding: .5rem 1rem; border-radius:4px; }
        nav a:hover { background: #0056b3; }
        main { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .stats { margin:1rem 0; padding:1rem; background:#e9f5ff; border-left:5px solid #007bff; }
        a.link { color: #007bff; text-decoration: none; }
        a.link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>
<main>
    <h1>
        <?php if ($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($username) ?>!
        <?php else: ?>
            Welcome to the Asset Management System
        <?php endif; ?>
    </h1>

    <?php if ($isLoggedIn): ?>

        <?php if ($userRole === 'admin'): ?>
            <div class="stats">
                <p><strong>Total Assets:</strong> <?= $totalAssets ?></p>
                <p><strong>Borrowed Assets:</strong> <?= $borrowedAssets ?></p>
            </div>
            <p><a class="link" href="assets/list.php">➤ Manage Assets</a></p>
            <p><a class="link" href="users/list.php">➤ Manage Users</a></p>
        <?php else: ?>
            <div class="stats">
                <p><strong>You have <?= $borrowedAssets ?> active borrowed item(s).</strong></p>
            </div>
            <p><a class="link" href="assets/list.php">➤ Browse Available Assets</a></p>
        <?php endif; ?>

    <?php else: ?>

        <p>This dashboard preview is available to everyone. Log in to access full features:</p>
        <ul>
            <li>View and request assets</li>
            <li>Manage your profile</li>
            <li>Admin: manage users, assets, and reports</li>
        </ul>
        <p><a class="link" href="../auth/login.php">➤ Login</a> to continue</p>

    <?php endif; ?>
</main>

</body>
</html>
