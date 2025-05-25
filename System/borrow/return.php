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
    $condition = trim($_POST['condition'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($borrow_id && $condition !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO returns (borrow_id, return_date, `condition`, remarks) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt->execute([$borrow_id, $condition, $remarks]);

        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'returned' WHERE borrow_id = ?");
        $stmt->execute([$borrow_id]);

        $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE borrow_id = ?");
        $stmt->execute([$borrow_id]);
        $borrow = $stmt->fetch();

        if ($borrow) {
            $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity + ? WHERE asset_id = ?");
            $stmt->execute([$borrow['quantity'], $borrow['asset_id']]);

            $stmt = $pdo->prepare("SELECT asset_name FROM assets WHERE asset_id = ?");
            $stmt->execute([$borrow['asset_id']]);
            $asset = $stmt->fetch();

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
    max-width: 600px;
  }
  .message-info {
    font-style: italic;
    color: #666;
    max-width: 600px;
  }
  form {
    background: white;
    padding: 20px 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
    max-width: 600px;
  }
  label {
    font-weight: 600;
    display: block;
    margin-bottom: 8px;
    margin-top: 15px;
  }
  select, input[type="text"], textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
  }
  select:focus, input[type="text"]:focus, textarea:focus {
    border-color: #007bff;
    outline: none;
  }
  button {
    background-color: #007bff;
    border: none;
    color: white;
    padding: 12px 25px;
    margin-top: 25px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #0056b3;
  }
</style>
<center>
<h2>Return Borrowed Asset</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="message-success">Return processed successfully!</div>
<?php endif; ?>

<?php if (empty($borrowedItems)): ?>
    <p class="message-info">You currently have no borrowed items to return.</p>
<?php else: ?>
    <form method="post" action="">
        <label for="borrow_id">Select Borrowed Item to Return:</label>
        <select name="borrow_id" id="borrow_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($borrowedItems as $item): ?>
                <option value="<?= htmlspecialchars($item['borrow_id']) ?>">
                    <?= htmlspecialchars($item['asset_name']) ?> — Qty: <?= htmlspecialchars($item['quantity']) ?> — Borrowed on: <?= htmlspecialchars($item['date_borrowed']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="condition">Condition on Return:</label>
        <input type="text" name="condition" id="condition" placeholder="e.g., Good, Damaged" required>

        <label for="remarks">Remarks:</label>
        <textarea name="remarks" id="remarks" rows="4" placeholder="Optional remarks"></textarea>

        <button type="submit">Submit Return</button>
    </form>
<?php endif; ?>
</center>
<?php include '../../includes/footer.php'; ?>
