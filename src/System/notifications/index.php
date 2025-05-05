<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];

// Mark notifications as read if requested
if (isset($_GET['mark_read'])) {
    $nid = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$nid, $userId]);
    header('Location: index.php');
    exit();
}

// Fetch notifications for user
$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Notifications</h2>
    <?php if ($notifications): ?>
    <table>
        <thead>
            <tr><th>Message</th><th>Date</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($notifications as $note): ?>
            <tr>
                <td><?= htmlspecialchars($note['message']) ?></td>
                <td><?= htmlspecialchars($note['created_at']) ?></td>
                <td><?= $note['is_read'] ? 'Read' : 'Unread' ?></td>
                <td>
                    <?php if (!$note['is_read']): ?>
                        <a href="?mark_read=<?= $note['id'] ?>">Mark as read</a>
                    <?php else: ?>
                        &mdash;
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No notifications found.</p>
    <?php endif; ?>
</div>
<?php
include '../includes/footer.php';
?>
