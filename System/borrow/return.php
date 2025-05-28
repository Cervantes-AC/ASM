<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Fetch approved borrow requests that are not yet returned
if ($userRole === 'admin') {
    $stmt = $pdo->prepare("
        SELECT br.borrow_id, a.asset_name, a.category, a.serial_code, br.quantity, br.date_borrowed, br.expected_return 
        FROM borrow_requests br
        JOIN assets a ON br.asset_id = a.asset_id
        WHERE br.status = 'approved'
        ORDER BY br.date_borrowed DESC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT br.borrow_id, a.asset_name, a.category, a.serial_code, br.quantity, br.date_borrowed, br.expected_return
        FROM borrow_requests br
        JOIN assets a ON br.asset_id = a.asset_id
        WHERE br.user_id = ? AND br.status = 'approved'
        ORDER BY br.date_borrowed DESC
    ");
    $stmt->execute([$userId]);
}

$borrowedItems = $stmt->fetchAll();

// Handle return submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrow_id = $_POST['borrow_id'];

    // Mark as returned
    $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'returned' WHERE borrow_id = ?");
    $stmt->execute([$borrow_id]);

    // Update asset quantity
    $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE borrow_id = ?");
    $stmt->execute([$borrow_id]);
    $borrow = $stmt->fetch();

    if ($borrow) {
        $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity + ? WHERE asset_id = ?");
        $stmt->execute([$borrow['quantity'], $borrow['asset_id']]);

        $stmt = $pdo->prepare("SELECT asset_name FROM assets WHERE asset_id = ?");
        $stmt->execute([$borrow['asset_id']]);
        $asset = $stmt->fetch();

        // Log the return
        $log_stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, target_id, description)
            VALUES (?, 'return_asset', ?, ?)
        ");
        $log_description = "User returned asset '{$asset['asset_name']}' (Borrow ID: $borrow_id), Quantity: {$borrow['quantity']}.";
        $log_stmt->execute([$userId, $borrow_id, $log_description]);
    }

    header('Location: return.php?success=1');
    exit;
}

include '../../includes/header.php';
?>

<style>
  body {
    font-family: Arial, sans-serif;
    background: #f7f9fc;
    padding: 20px;
    color: #333;
  }
  h2 {
    color: #2c3e50;
    margin-bottom: 20px;
  }
  .message-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    max-width: 800px;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    background: white;
    margin-bottom: 20px;
  }
  th, td {
    border: 1px solid #ddd;
    padding: 10px 14px;
    text-align: left;
  }
  th {
    background-color: #007bff;
    color: white;
  }
  form {
    display: inline;
  }
  button {
    background-color: #28a745;
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
  }
  button:hover {
    background-color: #218838;
  }
</style>

<h2>Borrowed Assets</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="message-success">Return processed successfully!</div>
<?php endif; ?>

<?php if (empty($borrowedItems)): ?>
    <p>You currently have no borrowed items to return.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Serial Code</th>
                <th>Quantity</th>
                <th>Date Borrowed</th>
                <th>Expected Return</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowedItems as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['asset_name']) ?></td>
                    <td><?= htmlspecialchars($item['category'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['serial_code'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td><?= htmlspecialchars($item['date_borrowed']) ?></td>
                    <td><?= htmlspecialchars($item['expected_return']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>