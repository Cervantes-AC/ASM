<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'] ?? null;

    if (!$asset_id) {
        $error = 'Please select an asset to reserve.';
    } else {
        // Check if asset is available
        $stmt = $pdo->prepare('SELECT status FROM assets WHERE id = ?');
        $stmt->execute([$asset_id]);
        $asset = $stmt->fetch();

        if (!$asset || $asset['status'] !== 'available') {
            $error = 'Asset is not available for reservation.';
        } else {
            // Update asset status to reserved
            $stmt = $pdo->prepare('UPDATE assets SET status = "reserved" WHERE id = ?');
            $stmt->execute([$asset_id]);

            // Insert reservation log as transaction (optional)
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, asset_id, action, returned) VALUES (?, ?, "reserve", 0)');
            $stmt->execute([$userId, $asset_id]);

            $success = 'Asset reserved successfully.';
        }
    }
}

// Fetch available assets for reservation
$assetsStmt = $pdo->query("SELECT * FROM assets WHERE status = 'available'");
$availableAssets = $assetsStmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Reserve Asset</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="">
        <label for="asset_id">Select Asset *</label>
        <select name="asset_id" id="asset_id" required>
            <option value="">-- Select an asset --</option>
            <?php foreach ($availableAssets as $asset): ?>
                <option value="<?= $asset['id'] ?>"><?= htmlspecialchars($asset['name'] . ' (' . $asset['serial_number'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Reserve</button>
    </form>
</div>
<?php
include '../includes/footer.php';
?>
