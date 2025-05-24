<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

$userId = $_SESSION['user_id'];

// Clear all read notifications
if (isset($_GET['clear_read'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND status = 'read'");
    $stmt->execute([$userId]);
    header('Location: index.php');
    exit;
}

// Mark notification as read if requested
if (isset($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE notifications SET status = 'read' WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $userId]);
    header('Location: index.php');
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY status ASC, date_created DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include '../../includes/header.php';
?>

<style>
.notification-list {
    list-style: none;
    padding: 0;
}
.notification-list li {
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    background-color: #f9f9f9;
}
.notification-list li.unread {
    background-color: #d9ecff;
    font-weight: bold;
}
.notification-list small {
    color: #666;
}
.actions {
    margin-top: 15px;
}
</style>

<h2>Your Notifications</h2>

<?php if (empty($notifications)): ?>
    <p>No notifications.</p>
<?php else: ?>
    <ul class="notification-list">
    <?php foreach ($notifications as $notif): ?>
        <li class="<?= $notif['status'] === 'unread' ? 'unread' : '' ?>">
            <strong><?= ucfirst($notif['type']) ?>:</strong> <?= htmlspecialchars($notif['message']) ?><br>
            <small><?= $notif['date_created'] ?></small><br>
            <?php if ($notif['status'] === 'unread'): ?>
                <a href="?mark_read=<?= $notif['notification_id'] ?>">Mark as read</a>
            <?php else: ?>
                <em>Read</em>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>

    <div class="actions">
        <a href="?clear_read=1" onclick="return confirm('Are you sure you want to clear all read notifications?')">Clear All Read Notifications</a>
    </div>
<?php endif; ?>

<p><a href="../dashboard/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
