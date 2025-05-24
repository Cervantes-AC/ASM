<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $asset_id = $_POST['asset_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $reason = trim($_POST['reason'] ?? '');

    if (!$user_id) $errors[] = "Please select a user.";
    if (!$asset_id) $errors[] = "Please select an asset.";
    if (!$amount || !is_numeric($amount) || $amount <= 0) $errors[] = "Please enter a valid amount.";
    if (empty($reason)) $errors[] = "Please provide a reason.";

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO fines (user_id, asset_id, amount, reason) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $asset_id, $amount, $reason]);
        header('Location: manage.php');
        exit;
    }
}

// Fetch users and assets for dropdowns
$users = $pdo->query("SELECT user_id, full_name FROM users ORDER BY full_name")->fetchAll();
$assets = $pdo->query("SELECT asset_id, asset_name FROM assets ORDER BY asset_name")->fetchAll();

include '../../includes/header.php';
?>

<h2>Add New Fine</h2>

<?php if ($errors): ?>
    <ul style="color: red;">
    <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="add.php">
    <label for="user_id">User:</label><br>
    <select name="user_id" id="user_id" required>
        <option value="">-- Select User --</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['user_id'] ?>" <?= (isset($user_id) && $user_id == $user['user_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="asset_id">Asset:</label><br>
    <select name="asset_id" id="asset_id" required>
        <option value="">-- Select Asset --</option>
        <?php foreach ($assets as $asset): ?>
            <option value="<?= $asset['asset_id'] ?>" <?= (isset($asset_id) && $asset_id == $asset['asset_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($asset['asset_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="amount">Amount (PHP):</label><br>
    <input type="number" step="0.01" min="0" name="amount" id="amount" value="<?= htmlspecialchars($amount ?? '') ?>" required><br><br>

    <label for="reason">Reason:</label><br>
    <textarea name="reason" id="reason" rows="4" required><?= htmlspecialchars($reason ?? '') ?></textarea><br><br>

    <button type="submit">Add Fine</button>
</form>

<p><a href="manage.php">Back to Fines Management</a></p>

<?php include '../../includes/footer.php'; ?>
