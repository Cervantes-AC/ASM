<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$error = '';
$success = '';

// Fetch fines - assuming fines are logged as special transactions or separate table

// For simplicity, this sample assumes fines stored as transactions with action 'fine'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // You can add logic to update fine status or payment
}

$stmt = $pdo->query(
    "SELECT t.id, u.username, a.name, a.serial_number, t.transaction_date, t.action
     FROM transactions t
     JOIN users u ON t.user_id = u.id
     JOIN assets a ON t.asset_id = a.id
     WHERE t.action LIKE 'fine%'
     ORDER BY t.transaction_date DESC"
);
$fines = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Manage Fines</h2>
    <?php if ($fines): ?>
    <table>
        <thead>
            <tr>
                <th>Fine ID</th>
                <th>User</th>
                <th>Asset</th>
                <th>Date</th>
                <th>Fine Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($fines as $fine): ?>
            <tr>
                <td><?= htmlspecialchars($fine['id']) ?></td>
                <td><?= htmlspecialchars($fine['username']) ?></td>
                <td><?= htmlspecialchars($fine['name']) ?></td>
                <td><?= htmlspecialchars($fine['transaction_date']) ?></td>
                <td><?= htmlspecialchars($fine['action']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No fines recorded.</p>
    <?php endif; ?>
</div>
<?php
include '../includes/footer.php';
?>
