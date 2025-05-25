<?php
// System/assets/edit.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';


// Only admin and staff can edit assets
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: list.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$asset_id = (int) $_GET['id'];
$errors = [];

// Fetch existing asset data
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch();

if (!$asset) {
    header("Location: list.php");
    exit;
}

$asset_name = $asset['asset_name'];
$category = $asset['category'];
$serial_code = $asset['serial_code'];
$quantity = $asset['quantity'];
$condition = $asset['condition'];
$status = $asset['status'];
$location = $asset['location'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_name = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $serial_code = trim($_POST['serial_code']);
    $quantity = (int) $_POST['quantity'];
    $condition = trim($_POST['condition']);
    $status = $_POST['status'] ?? 'available';
    $location = trim($_POST['location']);

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
        $stmt = $pdo->prepare("UPDATE assets SET asset_name=?, category=?, serial_code=?, quantity=?, `condition`=?, status=?, location=? WHERE asset_id=?");
        $stmt->execute([$asset_name, $category, $serial_code, $quantity, $condition, $status, $location, $asset_id]);
        header("Location: list.php?msg=Asset updated successfully");
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>Edit Asset</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="edit.php?id=<?= $asset_id ?>">
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

    <button type="submit">Update Asset</button>
    <a href="list.php">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>
