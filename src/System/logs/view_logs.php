<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Fetch log entries
$stmt = $pdo->query(
    "SELECT l.log_id, u.username, l.action, l.description, l.timestamp 
     FROM logs l
     JOIN users u ON l.user_id = u.user_id
     ORDER BY l.timestamp DESC LIMIT 100"
);
$logs = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Transaction Logs</h2>
    <?php if ($logs): ?>
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['log_id']) ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['description']) ?></td>
                <td><?= htmlspecialchars($log['timestamp']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No transaction logs found.</p>
    <?php endif; ?>
</div>
<?php
include '../includes/footer.php';
?>