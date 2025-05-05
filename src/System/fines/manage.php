<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$error = '';
$success = '';

// Handle POST request to update fine status or payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic to update fine status or payment can be added here
    // For example, you might want to mark a fine as paid
}

// Fetch fines
$stmt = $pdo->query(
    "SELECT f.fine_id, u.username, a.asset_name AS name, f.amount, f.reason, f.date_issued, f.is_paid
     FROM fines f
     JOIN users u ON f.user_id = u.user_id
     JOIN borrow_requests br ON f.request_id = br.request_id
     JOIN assets a ON br.asset_id = a.asset_id
     ORDER BY f.date_issued DESC"
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
                <th>Amount</th>
                <th>Reason</th>
                <th>Date Issued</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($fines as $fine): ?>
            <tr>
                <td><?= htmlspecialchars($fine['fine_id']) ?></td>
                <td><?= htmlspecialchars($fine['username']) ?></td>
                <td><?= htmlspecialchars($fine['name']) ?></td>
                <td><?= htmlspecialchars($fine['amount']) ?></td>
                <td><?= htmlspecialchars($fine['reason']) ?></td>
                <td><?= htmlspecialchars($fine['date_issued']) ?></td>
                <td><?= $fine['is_paid'] ? 'Paid' : 'Unpaid' ?></td>
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