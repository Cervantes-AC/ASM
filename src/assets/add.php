<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_name = trim($_POST['name'] ?? '');
    $asset_description = trim($_POST['description'] ?? ''); // Added description field
    $serial_number = trim($_POST['serial_number'] ?? '');
    $status = $_POST['status'] ?? 'available';

    if (empty($asset_name) || empty($serial_number)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if serial_number already exists
        $stmt = $pdo->prepare('SELECT asset_id FROM assets WHERE serial_number = ?');
        $stmt->execute([$serial_number]);
        if ($stmt->fetch()) {
            $error = 'Asset with this Serial Number already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO assets (asset_name, asset_description, serial_number, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$asset_name, $asset_description, $serial_number, $status]);
            $success = 'Asset added successfully.';
            // Optionally redirect to list page
            header('Location: list.php');
            exit();
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Add New Asset</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="">
        <label for="name">Asset Name *</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />

        <label for="description">Asset Description</label> <!-- Added description field -->
        <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <label for="serial_number">Serial Number *</label>
        <input type="text" id="serial_number" name="serial_number" required value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>" />

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="available" <?= (($_POST['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
            <option value="borrowed" <?= (($_POST['status'] ?? '') === 'borrowed') ? 'selected' : '' ?>>Borrowed</option>
            <option value="reserved" <?= (($_POST['status'] ?? '') === 'reserved') ? 'selected' : '' ?>>Reserved</option>
            <option value="missing" <?= (($_POST['status'] ?? '') === 'missing') ? 'selected' : '' ?>>Missing</option>
            <option value="damaged" <?= (($_POST['status'] ?? '') === 'damaged') ? 'selected' : '' ?>>Damaged</option>
        </select>

        <button type="submit">Add Asset</button>
    </form>
    <a href="list.php">Back to Assets List</a>
</div>
<?php
include '../includes/footer.php';
?>