<?php
// System/borrow/request.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';


$userId = $_SESSION['user_id'];

// Get asset id from GET or POST
$asset_id = $_GET['asset_id'] ?? null;

if (!$asset_id || !is_numeric($asset_id)) {
    header("Location: ../assets/list.php");
    exit;
}

// Fetch asset info to display
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch();

if (!$asset) {
    echo "Asset not found.";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int) ($_POST['quantity'] ?? 1);
    $expected_return = $_POST['expected_return'] ?? '';

    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1.";
    } elseif ($quantity > $asset['quantity']) {
        $errors[] = "Requested quantity exceeds available quantity.";
    }

    if (empty($expected_return)) {
        $errors[] = "Please specify expected return date.";
    } elseif (strtotime($expected_return) <= time()) {
        $errors[] = "Expected return date must be in the future.";
    }

    if (empty($errors)) {
        // Insert borrow request
        $stmt = $pdo->prepare("INSERT INTO borrow_requests (user_id, asset_id, quantity, date_borrowed, expected_return, status) VALUES (?, ?, ?, NOW(), ?, 'pending')");
        $stmt->execute([$userId, $asset_id, $quantity, $expected_return]);

        // Redirect or success message
        header("Location: ../assets/list.php?msg=Borrow request submitted");
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>Borrow Request for: <?= htmlspecialchars($asset['asset_name']) ?></h2>

<?php if ($errors): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <label>
        Quantity (Available: <?= $asset['quantity'] ?>):
        <input type="number" name="quantity" value="1" min="1" max="<?= $asset['quantity'] ?>" required>
    </label><br><br>

    <label>
        Expected Return Date:
        <input type="date" name="expected_return" required>
    </label><br><br>

    <button type="submit">Submit Borrow Request</button>
</form>

<p><a href="../assets/list.php">Back to Asset List</a></p>

<?php include '../../includes/footer.php'; ?>
