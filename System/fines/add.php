<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

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

<style>
    h2 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }
    form {
        max-width: 500px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #34495e;
    }
    select, input[type="number"], textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        margin-bottom: 16px;
        box-sizing: border-box;
    }
    textarea {
        resize: vertical;
    }
    button {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
    }
    button:hover {
        background-color: #2980b9;
    }
    ul.errors {
        color: #e74c3c;
        list-style-type: disc;
        margin-bottom: 1rem;
        padding-left: 20px;
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
<center>
<main>
    <h2>Add New Fine</h2>

    <?php if ($errors): ?>
        <ul class="errors" role="alert">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="add.php" novalidate>
        <label for="user_id">User:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Select User --</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int)$user['user_id'] ?>" <?= (isset($user_id) && $user_id == $user['user_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="asset_id">Asset:</label>
        <select name="asset_id" id="asset_id" required>
            <option value="">-- Select Asset --</option>
            <?php foreach ($assets as $asset): ?>
                <option value="<?= (int)$asset['asset_id'] ?>" <?= (isset($asset_id) && $asset_id == $asset['asset_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($asset['asset_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="amount">Amount (PHP):</label>
        <input 
            type="number" step="0.01" min="0.01" 
            name="amount" id="amount" 
            value="<?= htmlspecialchars($amount ?? '') ?>" 
            required
            aria-describedby="amountHelp"
        >
        <small id="amountHelp" style="color:#777;">Enter a positive amount, e.g., 100.00</small>

        <label for="reason">Reason:</label>
        <textarea name="reason" id="reason" rows="4" required><?= htmlspecialchars($reason ?? '') ?></textarea>

        <button type="submit">Add Fine</button>
    </form>

    <p class="back-link"><a href="manage.php">← Back to Fines Management</a></p>
    </center>
</main>

<?php include '../../includes/footer.php'; ?>
