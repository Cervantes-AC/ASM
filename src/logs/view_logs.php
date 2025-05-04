<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$stmt = $pdo->query(
    "SELECT t.id, u.username, a.name, a.serial_number, t.action, t.transaction_date 
     FROM transactions t
     JOIN users u ON t.user_id = u.id
     JOIN assets a ON t.asset_id = a.id
     ORDER BY t.transaction_date DESC LIMIT 100"
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
                <th>Asset</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['id']) ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['name']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['transaction_date']) ?></td>
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
