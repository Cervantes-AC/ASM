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

    if ($borrow_id && in_array($action, ['approve', 'deny'])) {
        $status = $action === 'approve' ? 'approved' : 'denied';

        // Update request status
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = ? WHERE borrow_id = ?");
        $stmt->execute([$status, $borrow_id]);

        // Insert log entry
        $admin_id = $_SESSION['user_id'];
        $log_action = ucfirst($status) . " borrow request ID $borrow_id";
        $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
        $log_stmt->execute([$admin_id, $log_action]);

        header("Location: manage_requests.php?msg=Request $status successfully");
        exit;
    }
}

// Fetch pending borrow requests
$stmt = $pdo->query("
    SELECT br.borrow_id, br.quantity, br.date_borrowed, br.expected_return,
           u.full_name, a.asset_name
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.user_id
    JOIN assets a ON br.asset_id = a.asset_id
    WHERE br.status = 'pending'
    ORDER BY br.date_borrowed DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<h2>Manage Borrow Requests</h2>

<?php if (!empty($_GET['msg'])): ?>
    <p style="color:green;"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<?php if (count($requests) === 0): ?>
    <p>No pending borrow requests.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>User</th>
                <th>Asset</th>
                <th>Quantity</th>
                <th>Date Borrowed</th>
                <th>Expected Return</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td><?= htmlspecialchars($req['borrow_id']) ?></td>
                <td><?= htmlspecialchars($req['full_name']) ?></td>
                <td><?= htmlspecialchars($req['asset_name']) ?></td>
                <td><?= htmlspecialchars($req['quantity']) ?></td>
                <td><?= htmlspecialchars($req['date_borrowed']) ?></td>
                <td><?= htmlspecialchars($req['expected_return']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                        <button type="submit" name="action" value="deny" onclick="return confirm('Are you sure you want to deny this request?');">Deny</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
