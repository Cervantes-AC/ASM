<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'] ?? null;
    $due_date = $_POST['due_date'] ?? null;

    if (!$asset_id || !$due_date) {
        $error = 'Please select an asset and due date.';
    } else {
        // Check if asset is available
        $stmt = $pdo->prepare('SELECT status FROM assets WHERE asset_id = ?'); // Changed 'id' to 'asset_id'
        $stmt->execute([$asset_id]);
        $asset = $stmt->fetch();

        if (!$asset || $asset['status'] !== 'available') {
            $error = 'Selected asset is not available for borrowing.';
        } else {
            // Insert transaction and update asset status
            $pdo->beginTransaction();

            $insert = $pdo->prepare('INSERT INTO borrow_requests (user_id, asset_id, date_borrowed, due_date, status) VALUES (?, ?, NOW(), ?, ?)');
            $insert->execute([$userId, $asset_id, $due_date, 'borrowed']); // Adjusted to match your borrow_requests table structure

            $update = $pdo->prepare('UPDATE assets SET status = ? WHERE asset_id = ?'); // Changed 'id' to 'asset_id'
            $update->execute(['borrowed', $asset_id]);

            $pdo->commit();
            $success = 'Asset borrowed successfully.';
        }
    }
}

// Fetch available assets to borrow
$assetsStmt = $pdo->query("SELECT * FROM assets WHERE status = 'available'");
$availableAssets = $assetsStmt->fetchAll();

include '../../includes/header.php';
include '../../includes/navbar.php';
?>
<div class="container">
    <h2>Request to Borrow Asset</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="post" action="">
        <label for="asset_id">Select Asset *</label>
        <select name="asset_id" id="asset_id" required>
            <option value="">-- Select an asset --</option>
            <?php foreach ($availableAssets as $asset): ?>
                <option value="<?= $asset['asset_id'] ?>" <?= (isset($_POST['asset_id']) && $_POST['asset_id'] == $asset['asset_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($asset['asset_name'] . ' (' . $asset['serial_number'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="due_date">Due Date *</label>
        <input type="date" name="due_date" id="due_date" required value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>" />

        <button type="submit">Request Borrow</button>
    </form>
</div>
<?php
include '../../includes/footer.php';
?>