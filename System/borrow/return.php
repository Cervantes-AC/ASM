<?php
// System/borrow/return.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Fetch approved borrow requests that are not yet returned
if ($userRole === 'admin') {
    $stmt = $pdo->prepare("
        SELECT br.borrow_id, a.asset_name, br.quantity, br.date_borrowed, br.expected_return 
        FROM borrow_requests br 
        JOIN assets a ON br.asset_id = a.asset_id 
        WHERE br.status = 'approved' 
        ORDER BY br.date_borrowed DESC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT br.borrow_id, a.asset_name, br.quantity, br.date_borrowed, br.expected_return 
        FROM borrow_requests br 
        JOIN assets a ON br.asset_id = a.asset_id 
        WHERE br.user_id = ? AND br.status = 'approved' 
        ORDER BY br.date_borrowed DESC
    ");
    $stmt->execute([$userId]);
}
$borrowedItems = $stmt->fetchAll();

// Handle return submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = $_POST['borrow_id'] ?? null;
    $condition = $_POST['condition'] ?? '';
    $remarks = $_POST['remarks'] ?? '';

    if ($borrow_id) {
        // Insert return record
        $stmt = $pdo->prepare("INSERT INTO returns (borrow_id, return_date, `condition`, remarks) VALUES (?, NOW(), ?, ?)");
        $stmt->execute([$borrow_id, $condition, $remarks]);

        // Update status of borrow request
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'returned' WHERE borrow_id = ?");
        $stmt->execute([$borrow_id]);

        // Get asset info from the borrow request
        $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE borrow_id = ?");
        $stmt->execute([$borrow_id]);
        $borrow = $stmt->fetch();

        if ($borrow) {
            // Update asset quantity
            $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity + ? WHERE asset_id = ?");
            $stmt->execute([$borrow['quantity'], $borrow['asset_id']]);

            // Fetch asset name for logging
            $stmt = $pdo->prepare("SELECT asset_name FROM assets WHERE asset_id = ?");
            $stmt->execute([$borrow['asset_id']]);
            $asset = $stmt->fetch();

            // Insert log entry
            $log_stmt = $pdo->prepare("
                INSERT INTO logs (user_id, action, target_id, description)
                VALUES (?, 'return_asset', ?, ?)
            ");
            $log_description = "User returned asset '{$asset['asset_name']}' (Borrow ID: $borrow_id), Quantity: {$borrow['quantity']}, Condition: $condition.";
            $log_stmt->execute([$userId, $borrow_id, $log_description]);
        }

        header('Location: return.php?success=1');
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>Return Borrowed Asset</h2>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">Return processed successfully!</p>
<?php endif; ?>

<?php if (count($borrowedItems) === 0): ?>
    <p>No borrowed items to return.</p>
<?php else: ?>
    <form method="post">
        <label for="borrow_id">Select Borrowed Item to Return:</label><br>
        <select name="borrow_id" id="borrow_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($borrowedItems as $item): ?>
                <option value="<?= $item['borrow_id'] ?>">
                    <?= htmlspecialchars($item['asset_name']) ?> — Qty: <?= $item['quantity'] ?> — Borrowed on: <?= $item['date_borrowed'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="condition">Condition on Return:</label><br>
        <input type="text" name="condition" id="condition" placeholder="e.g., Good, Damaged" required><br><br>

        <label for="remarks">Remarks:</label><br>
        <textarea name="remarks" id="remarks" rows="4" cols="50" placeholder="Optional remarks"></textarea><br><br>

        <button type="submit">Submit Return</button>
    </form>
<?php endif; ?>

<p><a href="../dashboarrd/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
