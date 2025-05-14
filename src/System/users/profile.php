<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

include '../../includes/header.php';
include '../../includes/navbar.php';
?>
<div class="container">
    <h2>My Profile</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
    <p><strong>Member Since:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    <a href="manage.php">Manage Account</a>
</div>
<?php
include '../../includes/footer.php';
?>
