<?php
// System/fines/manage.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];


// Handle status update by admin
if ($userRole === 'admin' && isset($_POST['fine_id'], $_POST['status'])) {
    $fine_id = (int)$_POST['fine_id'];
    $status = $_POST['status'] === 'paid' ? 'paid' : 'unpaid';

    $stmt = $pdo->prepare("UPDATE fines SET status = ? WHERE fine_id = ?");
    $stmt->execute([$status, $fine_id]);
    header('Location: manage.php');
    exit;
}

// Fetch fines
if ($userRole === 'admin') {
    $stmt = $pdo->query("SELECT f.*, u.full_name, a.asset_name 
                         FROM fines f 
                         JOIN users u ON f.user_id = u.user_id
                         JOIN assets a ON f.asset_id = a.asset_id
                         ORDER BY f.date_issued DESC");
    $fines = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT f.*, a.asset_name FROM fines f JOIN assets a ON f.asset_id = a.asset_id WHERE f.user_id = ? ORDER BY f.date_issued DESC");
    $stmt->execute([$userId]);
    $fines = $stmt->fetchAll();
}

include '../../includes/header.php';
?>

<h2>Fines Management</h2>

<?php if ($userRole === 'admin'): ?>
    <p><a href="add.php">Add New Fine</a></p>
<?php endif; ?>

<?php if (empty($fines)): ?>
    <p>No fines found.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <?php if ($userRole === 'admin'): ?><th>User</th><?php endif; ?>
                <th>Asset</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Date Issued</th>
                <?php if ($userRole === 'admin'): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fines as $fine): ?>
                <tr>
                    <?php if ($userRole === 'admin'): ?>
                        <td><?= htmlspecialchars($fine['full_name']) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($fine['asset_name']) ?></td>
                    <td><?= number_format($fine['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($fine['reason']) ?></td>
                    <td><?= ucfirst($fine['status']) ?></td>
                    <td><?= $fine['date_issued'] ?></td>
                    <?php if ($userRole === 'admin'): ?>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="fine_id" value="<?= $fine['fine_id'] ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="unpaid" <?= $fine['status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="paid" <?= $fine['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                </select>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="../dashboarrd/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>

