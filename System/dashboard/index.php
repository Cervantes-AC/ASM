<?php
// System/dashboard/index.php

require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Protect page — require login for any role
checkAuth();

$userRole = $_SESSION['role'];
$username = $_SESSION['full_name']; // You used 'username' before, but your DB has full_name

// Fetch stats based on role
if ($userRole === 'admin') {
    $totalAssets = $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
    $borrowedAssets = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status='approved'")->fetchColumn();
} else {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_requests WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$userId]);
    $borrowedAssets = $stmt->fetchColumn();
}

include '../../includes/header.php';
?>

<h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>

<?php if ($userRole === 'admin'): ?>
    <p>Total Assets: <?= $totalAssets ?></p>
    <p>Currently Borrowed Assets: <?= $borrowedAssets ?></p>
    <p><a href="../assets/list.php">Manage Assets</a></p>
    <p><a href="../users/list.php">Manage Users</a></p>
<?php else: ?>
    <p>You have <?= $borrowedAssets ?> active borrowed item(s).</p>
    <p><a href="../assets/list.php">Browse Assets</a></p>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
