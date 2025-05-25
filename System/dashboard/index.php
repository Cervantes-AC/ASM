<?php
require_once '../config/db.php';
session_start(); // Make sure the session is started

// Determine login state and role
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? $_SESSION['role'] : null;
$fullName   = $isLoggedIn ? $_SESSION['full_name'] : 'Guest';

// Initialize stats variables
$totalAssets = 0;
$borrowedAssets = 0;

// If logged in, fetch stats
if ($isLoggedIn) {
    if ($userRole === 'admin') {
        $totalAssets = (int) $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
        $borrowedAssets = (int) $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status='approved'")->fetchColumn();
    } elseif ($userRole === 'member') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_requests WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$_SESSION['user_id']]);
        $borrowedAssets = (int) $stmt->fetchColumn();
    }
}

// Include header after session started
include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Asset Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .stats {
            margin: 1rem 0;
            padding: 1rem;
            background: #e9f5ff;
            border-left: 5px solid #007bff;
        }
        a.link {
            color: #007bff;
            text-decoration: none;
        }
        a.link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<main>
    <h1>
        <?php if ($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($fullName) ?>!
        <?php else: ?>
            Welcome to the Asset Management System
        <?php endif; ?>
    </h1>

    <?php if ($isLoggedIn): ?>

        <?php if ($userRole === 'admin'): ?>
            <div class="stats">
                <p><strong>Total Assets:</strong> <?= $totalAssets ?></p>
                <p><strong>Borrowed Assets (All):</strong> <?= $borrowedAssets ?></p>
                <p>You can manage assets, users, logs, and view reports.</p>
            </div>

        <?php elseif ($userRole === 'member'): ?>
            <div class="stats">
                <p><strong>Your Active Borrowed Assets:</strong> <?= $borrowedAssets ?></p>
                <p>You may borrow available assets and view your fines and history.</p>
            </div>

        <?php else: ?>
            <div class="stats">
                <p>You are logged in with limited access. Please contact admin for role upgrade.</p>
            </div>
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
