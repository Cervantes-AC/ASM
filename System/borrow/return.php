<?php
// Enhanced return.php with condition update functionality - ADMIN ONLY
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Check if user is admin - redirect non-admins
if ($userRole !== 'admin') {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}

// Handle return submission with condition update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_item') {
    $borrow_id = $_POST['borrow_id'];
    $return_condition = $_POST['return_condition'];
    $return_notes = trim($_POST['return_notes'] ?? '');
    
    try {
        $pdo->beginTransaction();
        
        // Get borrow request details
        $stmt = $pdo->prepare("
            SELECT br.*, a.asset_name, a.quantity as original_quantity, a.serial_code, u.full_name as borrower_name
            FROM borrow_requests br
            JOIN assets a ON br.asset_id = a.asset_id
            JOIN users u ON br.user_id = u.user_id
            WHERE br.borrow_id = ? AND br.status = 'approved'
        ");
        $stmt->execute([$borrow_id]);
        $borrow = $stmt->fetch();
        
        if (!$borrow) {
            throw new Exception("Borrow request not found or already returned.");
        }
        
        // Check if this is an individual item or regular asset
        $item_id = $borrow['item_id'] ?? null;
        $item_number = $borrow['item_number'] ?? null;
        
        if ($item_id || $item_number) {
            // This is an individual item from multi-quantity asset
            if ($item_id) {
                // Update using item_id
                $updateStmt = $pdo->prepare("
                    UPDATE asset_items 
                    SET `condition` = ?, 
                        status = 'available', 
                        borrowed_by = NULL, 
                        borrowed_date = NULL, 
                        return_date = NOW(),
                        notes = CASE 
                            WHEN notes IS NULL OR notes = '' THEN ?
                            ELSE CONCAT(notes, '\n', ?)
                        END
                    WHERE item_id = ?
                ");
                $note = "Returned on " . date('Y-m-d H:i:s') . " by admin - Condition: " . $return_condition;
                if ($return_notes) $note .= " - " . $return_notes;
                $updateStmt->execute([$return_condition, $note, $note, $item_id]);
            } else {
                // Update using asset_id and item_number
                $updateStmt = $pdo->prepare("
                    UPDATE asset_items 
                    SET `condition` = ?, 
                        status = 'available', 
                        borrowed_by = NULL, 
                        borrowed_date = NULL, 
                        return_date = NOW(),
                        notes = CASE 
                            WHEN notes IS NULL OR notes = '' THEN ?
                            ELSE CONCAT(notes, '\n', ?)
                        END
                    WHERE asset_id = ? AND item_number = ?
                ");
                $note = "Returned on " . date('Y-m-d H:i:s') . " by admin - Condition: " . $return_condition;
                if ($return_notes) $note .= " - " . $return_notes;
                $updateStmt->execute([$return_condition, $note, $note, $borrow['asset_id'], $item_number]);
            }
        } else {
            // This is a regular single-quantity asset
            $updateStmt = $pdo->prepare("
                UPDATE assets 
                SET `condition` = ?, 
                    quantity = quantity + ?,
                    status = 'available'
                WHERE asset_id = ?
            ");
            $updateStmt->execute([$return_condition, $borrow['quantity'], $borrow['asset_id']]);
        }
        
        // Mark borrow request as returned
        $returnStmt = $pdo->prepare("
            UPDATE borrow_requests 
            SET status = 'returned', 
                return_date = NOW(),
                return_condition = ?,
                return_notes = ?
            WHERE borrow_id = ?
        ");
        $returnStmt->execute([$return_condition, $return_notes, $borrow_id]);
        
        // Log the return (admin action)
        $log_stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, target_id, description)
            VALUES (?, 'admin_return_asset', ?, ?)
        ");
        $log_description = "Admin processed return for '{$borrow['asset_name']}' borrowed by {$borrow['borrower_name']} (Borrow ID: $borrow_id), Condition: $return_condition";
        if ($item_number) $log_description .= " (Item #$item_number)";
        $log_stmt->execute([$userId, $borrow_id, $log_description]);
        
        $pdo->commit();
        header('Location: return.php?success=1');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollback();
        $error_message = "Error processing return: " . $e->getMessage();
    }
}

// Fetch ALL approved borrow requests that are not yet returned (admin can see all)
$stmt = $pdo->prepare("
    SELECT br.borrow_id, br.asset_id, br.item_id, br.item_number, br.quantity, 
           br.date_borrowed, br.expected_return, br.borrow_condition, br.user_id,
           a.asset_name, a.category, a.serial_code,
           u.full_name as borrower_name, u.email as borrower_email
    FROM borrow_requests br
    JOIN assets a ON br.asset_id = a.asset_id
    JOIN users u ON br.user_id = u.user_id
    WHERE br.status = 'approved'
    ORDER BY br.expected_return ASC, br.date_borrowed DESC
");
$stmt->execute();
$borrowedItems = $stmt->fetchAll();

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
    .admin-notice {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 12px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        max-width: 800px;
        font-weight: bold;
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
    .message-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
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
        vertical-align: top;
    }
    th {
        background-color: #007bff;
        color: white;
    }
    .borrower-info {
        background-color: #f8f9fa;
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 8px;
        font-size: 0.9em;
    }
    .return-form {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .return-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    .return-form select, .return-form textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .return-form textarea {
        resize: vertical;
        height: 60px;
    }
    .condition-options {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .condition-option {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    button {
        background-color: #28a745;
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }
    button:hover {
        background-color: #218838;
    }
    .btn-danger {
        background-color: #dc3545;
    }
    .btn-danger:hover {
        background-color: #c82333;
    }
    .individual-item {
        background-color: #e3f2fd;
    }
    .item-details {
        font-size: 0.9em;
        color: #666;
    }
    .overdue {
        background-color: #ffebee;
    }
    .due-soon {
        background-color: #fff3e0;
    }
</style>

<h2>Return Borrowed Assets (Admin Panel)</h2>

<div class="admin-notice">
    🔒 Admin Access Only - You can process returns for all users
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="message-success">Return processed successfully!</div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<?php if (empty($borrowedItems)): ?>
    <p>There are currently no borrowed items to return.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Borrower & Asset Details</th>
                <th>Borrowed Info</th>
                <th>Process Return</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowedItems as $item): ?>
                <?php 
                $due_date = new DateTime($item['expected_return']);
                $today = new DateTime();
                $diff = $today->diff($due_date);
                $row_class = '';
                if ($today > $due_date) {
                    $row_class = 'overdue';
                } elseif ($diff->days <= 3) {
                    $row_class = 'due-soon';
                }
                if ($item['item_number']) {
                    $row_class .= ' individual-item';
                }
                ?>
                <tr class="<?= $row_class ?>">
                    <td>
                        <div class="borrower-info">
                            <strong>👤 Borrower:</strong> <?= htmlspecialchars($item['borrower_name']) ?><br>
                            <strong>📧 Email:</strong> <?= htmlspecialchars($item['borrower_email']) ?>
                        </div>
                        
                        <strong><?= htmlspecialchars($item['asset_name']) ?></strong><br>
                        <div class="item-details">
                            Category: <?= htmlspecialchars($item['category'] ?? 'N/A') ?><br>
                            <?php if ($item['serial_code']): ?>
                                Serial: <?= htmlspecialchars($item['serial_code']) ?><br>
                            <?php endif; ?>
                            <?php if ($item['item_number']): ?>
                                Item #<?= htmlspecialchars($item['item_number']) ?><br>
                            <?php endif; ?>
                            Quantity: <?= htmlspecialchars($item['quantity']) ?>
                            <?php if ($item['borrow_condition']): ?>
                                <br>Borrowed in: <em><?= htmlspecialchars($item['borrow_condition']) ?></em>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <strong>Borrowed:</strong> <?= htmlspecialchars($item['date_borrowed']) ?><br>
                        <strong>Due:</strong> <?= htmlspecialchars($item['expected_return']) ?><br>
                        <?php if ($today > $due_date): ?>
                            <span style="color: #dc3545; font-weight: bold;">
                                ⚠️ Overdue by <?= $diff->days ?> day(s)
                            </span>
                        <?php elseif ($diff->days <= 3): ?>
                            <span style="color: #ffc107; font-weight: bold;">
                                ⏰ Due in <?= $diff->days ?> day(s)
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="return-form">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to process this return?');">
                                <input type="hidden" name="action" value="return_item">
                                <input type="hidden" name="borrow_id" value="<?= $item['borrow_id'] ?>">
                                
                                <label>Return Condition:</label>
                                <div class="condition-options">
                                    <div class="condition-option">
                                        <input type="radio" name="return_condition" value="excellent" id="excellent_<?= $item['borrow_id'] ?>" required>
                                        <label for="excellent_<?= $item['borrow_id'] ?>">Excellent</label>
                                    </div>
                                    <div class="condition-option">
                                        <input type="radio" name="return_condition" value="good" id="good_<?= $item['borrow_id'] ?>" required>
                                        <label for="good_<?= $item['borrow_id'] ?>">Good</label>
                                    </div>
                                    <div class="condition-option">
                                        <input type="radio" name="return_condition" value="fair" id="fair_<?= $item['borrow_id'] ?>" required>
                                        <label for="fair_<?= $item['borrow_id'] ?>">Fair</label>
                                    </div>
                                    <div class="condition-option">
                                        <input type="radio" name="return_condition" value="poor" id="poor_<?= $item['borrow_id'] ?>" required>
                                        <label for="poor_<?= $item['borrow_id'] ?>">Poor</label>
                                    </div>
                                    <div class="condition-option">
                                        <input type="radio" name="return_condition" value="damaged" id="damaged_<?= $item['borrow_id'] ?>" required>
                                        <label for="damaged_<?= $item['borrow_id'] ?>">Damaged</label>
                                    </div>
                                </div>
                                
                                <label for="notes_<?= $item['borrow_id'] ?>">Return Notes (optional):</label>
                                <textarea name="return_notes" id="notes_<?= $item['borrow_id'] ?>" placeholder="Any issues, damages, or comments about the returned item..."></textarea>
                                
                                <button type="submit">Process Return</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
// Auto-select condition based on borrowed condition
document.addEventListener('DOMContentLoaded', function() {
    // You can add JavaScript here to pre-select the same condition as borrowed
    // or implement other UI enhancements
});
</script>

<?php include '../../includes/footer.php'; ?>