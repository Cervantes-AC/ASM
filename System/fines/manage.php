<?php
// System/fines/manage.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Handle status update by admin
if ($userRole === 'admin' && isset($_POST['fine_id'], $_POST['status'])) {
    $fine_id = (int)$_POST['fine_id'];
    $status = ($_POST['status'] === 'paid') ? 'paid' : 'unpaid';

    $stmt = $pdo->prepare("UPDATE fines SET status = ? WHERE fine_id = ?");
    $stmt->execute([$status, $fine_id]);
    header('Location: manage.php');
    exit;
}

// Fetch fines
if ($userRole === 'admin') {
    $stmt = $pdo->query("
        SELECT f.*, u.full_name, a.asset_name 
        FROM fines f 
        JOIN users u ON f.user_id = u.user_id
        JOIN assets a ON f.asset_id = a.asset_id
        ORDER BY f.date_issued DESC
    ");
    $fines = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT f.*, a.asset_name 
        FROM fines f 
        JOIN assets a ON f.asset_id = a.asset_id 
        WHERE f.user_id = ? 
        ORDER BY f.date_issued DESC
    ");
    $stmt->execute([$userId]);
    $fines = $stmt->fetchAll();
}

include '../../includes/header.php';
?>

<style>
    h2 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }
    a.button-link {
        display: inline-block;
        padding: 8px 16px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    a.button-link:hover {
        background-color: #2980b9;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
        vertical-align: middle;
    }
    table th {
        background-color: #f4f6f7;
        font-weight: 600;
    }
    select {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    form.inline {
        display: inline;
    }
    .status-paid {
        color: green;
        font-weight: 600;
    }
    .status-unpaid {
        color: red;
        font-weight: 600;
    }
    p.back-link {
        margin-top: 1rem;
    }
    p.back-link a {
        color: #3498db;
        text-decoration: none;
        font-weight: 600;
    }
    p.back-link a:hover {
        text-decoration: underline;
    }
</style>

<main>
    <h2>Fines Management</h2>

    <?php if ($userRole === 'admin'): ?>
        <a href="add.php" class="button-link">Add New Fine</a>
    <?php endif; ?>

    <?php if (empty($fines)): ?>
        <p>No fines found.</p>
    <?php else: ?>
        <table>
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
                        <td class="status-<?= htmlspecialchars($fine['status']) ?>">
                            <?= ucfirst(htmlspecialchars($fine['status'])) ?>
                        </td>
                        <td><?= htmlspecialchars($fine['date_issued']) ?></td>
                        <?php if ($userRole === 'admin'): ?>
                            <td>
                                <form method="post" class="inline" aria-label="Update fine status">
                                    <input type="hidden" name="fine_id" value="<?= (int)$fine['fine_id'] ?>">
                                    <select name="status" onchange="this.form.submit()" aria-label="Change status for fine #<?= (int)$fine['fine_id'] ?>">
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

    <p class="back-link"><a href="../dashboard/index.php">← Back to Dashboard</a></p>
</main>

<?php include '../../includes/footer.php'; ?>
