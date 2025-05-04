<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'] ?? null;

    if (!$transaction_id) {
        $error = 'Invalid transaction.';
    } else {
        // Fetch transaction and check
        $stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ? AND returned = 0');
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            $error = 'Transaction not found or already returned.';
        } else {
            $pdo->beginTransaction();

            // Mark transaction as returned
            $updateTran = $pdo->prepare('UPDATE transactions SET returned = 1, action="return" WHERE id = ?');
            $updateTran->execute([$transaction_id]);

            // Update asset status to available
            $updateAsset = $pdo->prepare('UPDATE assets SET status = "available" WHERE id = ?');
            $updateAsset->execute([$transaction['asset_id']]);

            $pdo->commit();
            $success = 'Asset returned successfully.';
        }
    }
}

// Fetch borrowed assets for user (not returned)
$stmt = $pdo->prepare(
    'SELECT t.id as transaction_id, a.name, a.serial_number, t.due_date
     FROM transactions t
     JOIN assets a ON t.asset_id = a.id
     WHERE t.user_id = ? AND t.returned = 0 AND t.action = "borrow"'
);
$stmt->execute([$userId]);
$borrowedAssets = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Return Borrowed Assets</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if ($borrowedAssets): ?>
    <form method="post" action="">
        <label for="transaction_id">Select asset to return *</label>
        <select name="transaction_id" id="transaction_id" required>
            <option value="">-- Select --</option>
            <?php foreach($borrowedAssets as $asset): ?>
                <option value="<?= $asset['transaction_id'] ?>">
                    <?= htmlspecialchars($asset['name'] . ' (' . $asset['serial_number'] . ') - Due: ' . $asset['due_date']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Return Asset</button>
    </form>
    <?php else: ?>
        <p>You have no borrowed assets to return.</p>
    <?php endif; ?>
</div>
<?php
include '../includes/footer.php';
?>
