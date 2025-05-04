<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit();
}

// Fetch existing asset data
$stmt = $pdo->prepare('SELECT * FROM assets WHERE id = ?');
$stmt->execute([$id]);
$asset = $stmt->fetch();

if (!$asset) {
    header('Location: list.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $status = $_POST['status'] ?? 'available';

    if (empty($name) || empty($category) || empty($serial_number)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check for duplicate serial number, skipping current asset
        $stmt = $pdo->prepare('SELECT id FROM assets WHERE serial_number = ? AND id != ?');
        $stmt->execute([$serial_number, $id]);
        if ($stmt->fetch()) {
            $error = 'Another asset with this Serial Number already exists.';
        } else {
            $stmt = $pdo->prepare('UPDATE assets SET name = ?, category = ?, serial_number = ?, status = ? WHERE id = ?');
            $stmt->execute([$name, $category, $serial_number, $status, $id]);
            header('Location: list.php');
            exit();
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>Edit Asset</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="">
        <label for="name">Asset Name *</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $asset['name']) ?>" />

        <label for="category">Category *</label>
        <input type="text" id="category" name="category" required value="<?= htmlspecialchars($_POST['category'] ?? $asset['category']) ?>" />

        <label for="serial_number">Serial Number *</label>
        <input type="text" id="serial_number" name="serial_number" required value="<?= htmlspecialchars($_POST['serial_number'] ?? $asset['serial_number']) ?>" />

        <label for="status">Status</label>
        <select id="status" name="status">
            <?php
                $statuses = ['available', 'borrowed', 'reserved', 'missing'];
                $selectedStatus = $_POST['status'] ?? $asset['status'];
                foreach ($statuses as $statusOption) {
                    $selected = ($selectedStatus === $statusOption) ? 'selected' : '';
                    echo "<option value=\"$statusOption\" $selected>" . ucfirst($statusOption) . "</option>";
                }
            ?>
        </select>

        <button type="submit">Update Asset</button>
    </form>
    <a href="list.php">Back to Assets List</a>
</div>
<?php
include '../includes/footer.php';
?>
