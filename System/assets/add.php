<?php
// System/assets/add.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

// Only admin and staff can add assets
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: list.php");
    exit;
}

$errors = [];
$asset_name = $category = $serial_code = $condition = $status = $location = "";
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_name = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $serial_code = trim($_POST['serial_code']);
    $quantity = (int) $_POST['quantity'];
    $condition = trim($_POST['condition']);
    $status = $_POST['status'] ?? 'available';
    $location = trim($_POST['location']);

    // Basic validation
    if (empty($asset_name)) {
        $errors[] = "Asset name is required.";
    }
    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1.";
    }
    if (!in_array($status, ['available', 'borrowed', 'reserved', 'damaged', 'lost'])) {
        $status = 'available';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO assets (asset_name, category, serial_code, quantity, `condition`, status, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$asset_name, $category, $serial_code, $quantity, $condition, $status, $location]);
        header("Location: list.php?msg=Asset added successfully");
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>Add New Asset</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="add.php">
    <label>Asset Name:<br>
        <input type="text" name="asset_name" value="<?= htmlspecialchars($asset_name) ?>" required>
    </label><br><br>

    <label>Category:<br>
        <input type="text" name="category" value="<?= htmlspecialchars($category) ?>">
    </label><br><br>

    <label>Serial Code:<br>
        <input type="text" name="serial_code" value="<?= htmlspecialchars($serial_code) ?>">
    </label><br><br>

    <label>Quantity:<br>
        <input type="number" name="quantity" min="1" value="<?= htmlspecialchars($quantity) ?>" required>
    </label><br><br>

    <label>Condition:<br>
        <input type="text" name="condition" value="<?= htmlspecialchars($condition) ?>">
    </label><br><br>

    <label>Status:<br>
        <select name="status">
            <?php
            $statuses = ['available', 'borrowed', 'reserved', 'damaged', 'lost'];
            foreach ($statuses as $s):
            ?>
                <option value="<?= $s ?>" <?= ($status === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Location:<br>
        <input type="text" name="location" value="<?= htmlspecialchars($location) ?>">
    </label><br><br>

    <button type="submit">Add Asset</button>
    <a href="list.php">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>
