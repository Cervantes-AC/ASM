<?php
// System/borrow/manage_requests.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../assets/list.php?error=Access denied");
    exit;
}

// Handle approve/deny POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = $_POST['borrow_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($borrow_id && $action === 'approve') {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Get borrow request details
            $stmt = $pdo->prepare("
                SELECT br.asset_id, br.quantity, a.asset_name, a.quantity as current_quantity
                FROM borrow_requests br 
                JOIN assets a ON br.asset_id = a.asset_id 
                WHERE br.borrow_id = ? AND br.status = 'pending'
            ");
            $stmt->execute([$borrow_id]);
            $borrow_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$borrow_details) {
                throw new Exception("Borrow request not found or already processed");
            }
            
            // Check if there's enough quantity available
            if ($borrow_details['current_quantity'] < $borrow_details['quantity']) {
                throw new Exception("Insufficient quantity available. Only {$borrow_details['current_quantity']} items available.");
            }
            
            // Update borrow request status to 'approved'
            $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'approved' WHERE borrow_id = ?");
            $stmt->execute([$borrow_id]);
            
            // DEDUCT the borrowed quantity from asset inventory
            $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity - ? WHERE asset_id = ?");
            $stmt->execute([$borrow_details['quantity'], $borrow_details['asset_id']]);
            
            // Update asset status to 'unavailable' if quantity becomes 0
            $stmt = $pdo->prepare("
                UPDATE assets 
                SET status = CASE 
                    WHEN quantity <= 0 THEN 'unavailable' 
                    ELSE 'available' 
                END 
                WHERE asset_id = ?
            ");
            $stmt->execute([$borrow_details['asset_id']]);
            
            // Insert log entry
            $admin_id = $_SESSION['user_id'];
            $log_action = "Approved borrow request ID $borrow_id. Asset: {$borrow_details['asset_name']}, Quantity borrowed: {$borrow_details['quantity']}";
            $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $log_stmt->execute([$admin_id, $log_action]);
            
            // Commit transaction
            $pdo->commit();
            
            header("Location: manage_requests.php?msg=Request approved successfully. Quantity deducted from inventory.");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            header("Location: manage_requests.php?error=Error approving request: " . $e->getMessage());
            exit;
        }
    }
    
    if ($borrow_id && $action === 'deny') {
        // Update request status to denied
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'denied' WHERE borrow_id = ?");
        $stmt->execute([$borrow_id]);

        // Insert log entry
        $admin_id = $_SESSION['user_id'];
        $log_action = "Denied borrow request ID $borrow_id";
        $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
        $log_stmt->execute([$admin_id, $log_action]);

        header("Location: manage_requests.php?msg=Request denied successfully");
        exit;
    }
    
    // Handle confirm return action
    if ($borrow_id && $action === 'confirm_return') {
        $condition = $_POST['condition'] ?? null;
        
        if (!$condition) {
            header("Location: manage_requests.php?error=Please select a condition");
            exit;
        }
        
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Get borrow request details
            $stmt = $pdo->prepare("
                SELECT br.asset_id, br.quantity, a.asset_name 
                FROM borrow_requests br 
                JOIN assets a ON br.asset_id = a.asset_id 
                WHERE br.borrow_id = ?
            ");
            $stmt->execute([$borrow_id]);
            $borrow_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$borrow_details) {
                throw new Exception("Borrow request not found");
            }
            
            // Update borrow request status to 'returned'
            $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'returned' WHERE borrow_id = ?");
            $stmt->execute([$borrow_id]);
            
            // Update asset quantity (return the borrowed quantity back to inventory)
            $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity + ? WHERE asset_id = ?");
            $stmt->execute([$borrow_details['quantity'], $borrow_details['asset_id']]);
            
            // Update asset condition (wrap 'condition' in backticks since it's a reserved word)
            $stmt = $pdo->prepare("UPDATE assets SET `condition` = ? WHERE asset_id = ?");
            $stmt->execute([$condition, $borrow_details['asset_id']]);
            
            // Make sure asset status is available if quantity > 0
            $stmt = $pdo->prepare("
                UPDATE assets 
                SET status = CASE 
                    WHEN quantity > 0 THEN 'available' 
                    ELSE status 
                END 
                WHERE asset_id = ?
            ");
            $stmt->execute([$borrow_details['asset_id']]);
            
            // Insert log entry
            $admin_id = $_SESSION['user_id'];
            $log_action = "Confirmed return of borrow request ID $borrow_id. Asset: {$borrow_details['asset_name']}, Quantity: {$borrow_details['quantity']}, Condition: $condition";
            $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $log_stmt->execute([$admin_id, $log_action]);
            
            // Commit transaction
            $pdo->commit();
            
            header("Location: manage_requests.php?msg=Return confirmed successfully");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            header("Location: manage_requests.php?error=Error processing return: " . $e->getMessage());
            exit;
        }
    }
}

// Get the view parameter (default to 'pending')
$view = $_GET['view'] ?? 'pending';

// Fetch requests based on view
if ($view === 'pending') {
    $stmt = $pdo->query("
        SELECT br.borrow_id, br.quantity, br.date_borrowed, br.expected_return,
               u.full_name, a.asset_name, a.category, a.serial_code
        FROM borrow_requests br
        JOIN users u ON br.user_id = u.user_id
        JOIN assets a ON br.asset_id = a.asset_id
        WHERE br.status = 'pending'
        ORDER BY br.date_borrowed DESC
    ");
} elseif ($view === 'approved') {
    $stmt = $pdo->query("
        SELECT br.borrow_id, br.quantity, br.date_borrowed, br.expected_return,
               u.full_name, a.asset_name, a.category, a.serial_code, a.`condition` as current_condition
        FROM borrow_requests br
        JOIN users u ON br.user_id = u.user_id
        JOIN assets a ON br.asset_id = a.asset_id
        WHERE br.status = 'approved'
        ORDER BY br.expected_return ASC
    ");
} else {
    $stmt = $pdo->query("
        SELECT br.borrow_id, br.quantity, br.date_borrowed, br.expected_return,
               u.full_name, a.asset_name, a.category, a.serial_code, br.status
        FROM borrow_requests br
        JOIN users u ON br.user_id = u.user_id
        JOIN assets a ON br.asset_id = a.asset_id
        WHERE br.status IN ('returned', 'denied')
        ORDER BY br.date_borrowed DESC
    ");
}

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<style>
    .table-container {
        overflow-x: auto;
        margin-top: 1rem;
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
        min-width: 800px;
        border: 1px solid #ddd;
    }

    .styled-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: left;
    }

    .styled-table th, .styled-table td {
        padding: 12px 15px;
        border: 1px solid #ddd;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .btn {
        display: inline-block;
        background-color: #009879;
        color: #fff;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        cursor: pointer;
        margin: 2px;
    }

    .btn:hover {
        background-color: #007f6d;
    }

    .btn-danger {
        background-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-success {
        background-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .nav-tabs {
        margin: 20px 0;
        border-bottom: 2px solid #009879;
    }

    .nav-tabs a {
        display: inline-block;
        padding: 10px 20px;
        text-decoration: none;
        color: #009879;
        border: 2px solid #009879;
        border-bottom: none;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
    }

    .nav-tabs a.active {
        background-color: #009879;
        color: white;
    }

    .nav-tabs a:hover {
        background-color: #f8f9fa;
    }

    .nav-tabs a.active:hover {
        background-color: #007f6d;
    }

    .return-form {
        display: inline-block;
    }

    .return-form select {
        margin-right: 5px;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .success-msg {
        color: green;
        background-color: #d4edda;
        padding: 10px;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        margin: 10px 0;
    }

    .error-msg {
        color: #721c24;
        background-color: #f8d7da;
        padding: 10px;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        margin: 10px 0;
    }

    h2 {
        margin-top: 20px;
    }
</style>

<h2>Manage Borrow Requests</h2>

<?php if (!empty($_GET['msg'])): ?>
    <div class="success-msg"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="nav-tabs">
    <a href="?view=pending" class="<?= $view === 'pending' ? 'active' : '' ?>">Pending Requests</a>
    <a href="?view=approved" class="<?= $view === 'approved' ? 'active' : '' ?>">Approved/Borrowed Items</a>
    <a href="?view=history" class="<?= $view === 'history' ? 'active' : '' ?>">History</a>
</div>

<?php if (count($requests) === 0): ?>
    <p>No <?= $view ?> requests found.</p>
<?php else: ?>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User</th>
                    <th>Asset Name</th>
                    <th>Category</th>
                    <th>Serial Code</th>
                    <th>Quantity</th>
                    <th>Date Borrowed</th>
                    <th>Expected Return</th>
                    <?php if ($view === 'history'): ?>
                        <th>Status</th>
                    <?php endif; ?>
                    <?php if ($view !== 'history'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['borrow_id']) ?></td>
                    <td><?= htmlspecialchars($req['full_name']) ?></td>
                    <td><?= htmlspecialchars($req['asset_name']) ?></td>
                    <td><?= htmlspecialchars($req['category']) ?></td>
                    <td><?= htmlspecialchars($req['serial_code'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($req['quantity']) ?></td>
                    <td><?= htmlspecialchars($req['date_borrowed']) ?></td>
                    <td><?= htmlspecialchars($req['expected_return']) ?></td>
                    
                    <?php if ($view === 'history'): ?>
                        <td><?= ucfirst(htmlspecialchars($req['status'])) ?></td>
                    <?php endif; ?>
                    
                    <?php if ($view === 'pending'): ?>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn">Approve</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                                <button type="submit" name="action" value="deny" class="btn btn-danger" onclick="return confirm('Are you sure you want to deny this request?');">Deny</button>
                            </form>
                        </td>
                    <?php elseif ($view === 'approved'): ?>
                        <td>
                            <form method="post" class="return-form">
                                <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                                <select name="condition" required>
                                    <option value="">Select Condition</option>
                                    <option value="excellent">Excellent</option>
                                    <option value="good" selected>Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                                <button type="submit" name="action" value="confirm_return" class="btn btn-success" onclick="return confirm('Confirm that this item has been returned?');">
                                    Confirm Return
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>