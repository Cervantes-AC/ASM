<?php
require_once '../config/db.php';
session_start(); // Ensure the session is started

// Determine login state and user info
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? $_SESSION['role'] : null;
$fullName   = $isLoggedIn ? $_SESSION['full_name'] : 'Guest';

// Initialize stats variables
$totalAssets = 0;
$borrowedAssets = 0;

// Fetch stats if logged in
if ($isLoggedIn) {
    if ($userRole === 'admin') {
        $totalAssets = (int) $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
        $borrowedAssets = (int) $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'approved'")->fetchColumn();
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

<body>

<main style="max-width: 800px; margin: 40px auto; padding: 25px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif; color: #333;">

    <h1 style="font-size: 2rem; margin-bottom: 30px; color: #2c3e50;">
        <?php if ($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($fullName) ?>!
        <?php else: ?>
            Welcome to the Asset Management System
        <?php endif; ?>
    </h1>

    <?php if ($isLoggedIn): ?>

        <?php if ($userRole === 'admin'): ?>
            <div class="stats" style="background: #e8f0fe; border: 1px solid #a2c4fc; padding: 20px; border-radius: 6px; color: #1a237e;">
                <p style="font-size: 1.15rem; margin: 10px 0;"><strong>Total Assets:</strong> <?= $totalAssets ?></p>
                <p style="font-size: 1.15rem; margin: 10px 0;"><strong>Borrowed Assets (All):</strong> <?= $borrowedAssets ?></p>
                <p style="margin-top: 15px;">You can manage assets, users, logs, and view reports.</p>
            </div>

        <?php elseif ($userRole === 'member'): ?>
            <div class="stats" style="background: #e0f7fa; border: 1px solid #4dd0e1; padding: 20px; border-radius: 6px; color: #006064;">
                <p style="font-size: 1.15rem; margin: 10px 0;"><strong>Your Active Borrowed Assets:</strong> <?= $borrowedAssets ?></p>
                <p style="margin-top: 15px;">You may borrow available assets and view your fines and history.</p>
            </div>

        <?php else: ?>
            <div class="stats" style="background: #fff3e0; border: 1px solid #ffb74d; padding: 20px; border-radius: 6px; color: #e65100;">
                <p>You are logged in with limited access. Please contact admin for role upgrade.</p>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <p style="margin-bottom: 15px;">This dashboard preview is available to everyone. Log in to access full features:</p>
        <ul style="margin-left: 20px; margin-bottom: 20px; color: #555;">
            <li>View and request assets</li>
            <li>Manage your profile</li>
            <li>Admin: manage users, assets, and reports</li>
        </ul>
        <p>
          <a class="link" href="../auth/login.php" style="color: #2e7d32; font-weight: bold; text-decoration: none;">
            ➤ Login
          </a> to continue
        </p>

    <?php endif; ?>
</main>


</body>
</html>
