<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch some summary data based on the role
if ($role == 'admin') {
    // Admin-specific data: total assets, total users, etc.
    $stmt = $pdo->query('SELECT COUNT(*) AS total_assets FROM assets');
    $total_assets = $stmt->fetch()['total_assets'];
    
    $stmt = $pdo->query('SELECT COUNT(*) AS total_users FROM users');
    $total_users = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query('SELECT COUNT(*) AS overdue_requests FROM borrow_requests WHERE status = "overdue"');
    $overdue_requests = $stmt->fetch()['overdue_requests'];
} elseif ($role == 'staff') {
    // Staff-specific data: borrowed assets, overdue items, etc.
    $stmt = $pdo->prepare('SELECT COUNT(*) AS borrowed_assets FROM borrow_requests WHERE user_id = ? AND status = "borrowed"');
    $stmt->execute([$user_id]);
    $borrowed_assets = $stmt->fetch()['borrowed_assets'];
    
    $stmt = $pdo->prepare('SELECT COUNT(*) AS overdue_assets FROM borrow_requests WHERE user_id = ? AND status = "overdue"');
    $stmt->execute([$user_id]);
    $overdue_assets = $stmt->fetch()['overdue_assets'];
} else {
    // Member-specific data: borrowed assets
    $stmt = $pdo->prepare('SELECT COUNT(*) AS borrowed_assets FROM borrow_requests WHERE user_id = ? AND status = "borrowed"');
    $stmt->execute([$user_id]);
    $borrowed_assets = $stmt->fetch()['borrowed_assets'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome to the Dashboard</h1>
            <p>Hello, <?php echo $_SESSION['full_name']; ?>! You are logged in as <?php echo $role; ?>.</p>
        </header>
        
        <div class="dashboard">
            <?php if ($role == 'admin'): ?>
                <div class="card">
                    <h3>Total Assets</h3>
                    <p><?php echo $total_assets; ?></p>
                </div>
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="card">
                    <h3>Overdue Requests</h3>
                    <p><?php echo $overdue_requests; ?></p>
                </div>
            <?php elseif ($role == 'staff'): ?>
                <div class="card">
                    <h3>Borrowed Assets</h3>
                    <p><?php echo $borrowed_assets; ?></p>
                </div>
                <div class="card">
                    <h3>Overdue Assets</h3>
                    <p><?php echo $overdue_assets; ?></p>
                </div>
            <?php else: ?>
                <div class="card">
                    <h3>Borrowed Assets</h3>
                    <p><?php echo $borrowed_assets; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <footer>
            <a href="logout.php">Logout</a>
        </footer>
    </div>
</body>
</html>
