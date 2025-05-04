<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$today = date('Y-m-d');

// Fetch overdue transactions where returned = 0 and due_date < today
$stmt = $pdo->prepare(
    'SELECT t.id, u.username, a.name, a.serial_number, t.due_date
     FROM transactions t
     JOIN users u ON t.user_id = u.id
     JOIN assets a ON t.asset_id = a.id
     WHERE t.returned = 0 AND t.due_date < ?
     ORDER BY t.due_date ASC'
);
$stmt->execute([$today]);
$overdueLoans = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Overdue Borrowed Assets</h2>
    <?php if ($overdueLoans): ?>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>User</th>
                <th>Asset</th>
                <th>Serial Number</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($overdueLoans as $loan): ?>
            <tr>
                <td><?= htmlspecialchars($loan['id']) ?></td>
                <td><?= htmlspecialchars($loan['username']) ?></td>
                <td><?= htmlspecialchars($loan['name']) ?></td>
                <td><?= htmlspecialchars($loan['serial_number']) ?></td>
                <td><?= htmlspecialchars($loan['due_date']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No overdue borrowed assets.</p>
    <?php endif; ?>
</div>
<?php
include '../includes/footer.php';
?>
