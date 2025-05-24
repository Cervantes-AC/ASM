<?php
// System/borrow/overdue.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = $_POST['borrow_id'] ?? null;
    $new_status = $_POST['status'] ?? null;

    $allowed_status = ['approved', 'denied', 'returned', 'overdue'];

    if ($borrow_id && in_array($new_status, $allowed_status)) {
        // Update status
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = ? WHERE borrow_id = ?");
        $stmt->execute([$new_status, $borrow_id]);

        // If returned, update asset quantity back
        if ($new_status === 'returned') {
            // Fetch borrow request details
            $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE borrow_id = ?");
            $stmt->execute([$borrow_id]);
            $borrow = $stmt->fetch();

            if ($borrow) {
                // Increase asset quantity
                $stmt = $pdo->prepare("UPDATE assets SET quantity = quantity + ? WHERE asset_id = ?");
                $stmt->execute([$borrow['quantity'], $borrow['asset_id']]);
            }
        }

        header("Location: overdue.php");
        exit;
    }
}

// Fetch all borrow requests
$stmt = $pdo->query("SELECT br.borrow_id, u.full_name, a.asset_name, br.quantity, br.date_borrowed, br.expected_return, br.status
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.user_id
    JOIN assets a ON br.asset_id = a.asset_id
    ORDER BY br.date_borrowed DESC");
$requests = $stmt->fetchAll();

include '../../includes/header.php';
?>

<h2>Manage Borrow Requests</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Asset</th>
            <th>Quantity</th>
            <th>Date Borrowed</th>
            <th>Expected Return</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $req): ?>
        <tr>
            <td><?= $req['borrow_id'] ?></td>
            <td><?= htmlspecialchars($req['full_name']) ?></td>
            <td><?= htmlspecialchars($req['asset_name']) ?></td>
            <td><?= $req['quantity'] ?></td>
            <td><?= $req['date_borrowed'] ?></td>
            <td><?= $req['expected_return'] ?></td>
            <td><?= $req['status'] ?></td>
            <td>
                <?php if ($req['status'] === 'pending'): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                        <button name="status" value="approved" onclick="return confirm('Approve this request?')">Approve</button>
                        <button name="status" value="denied" onclick="return confirm('Deny this request?')">Deny</button>
                    </form>
                <?php elseif ($req['status'] === 'approved'): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="borrow_id" value="<?= $req['borrow_id'] ?>">
                        <button name="status" value="returned" onclick="return confirm('Mark as returned?')">Returned</button>
                        <button name="status" value="overdue" onclick="return confirm('Mark as overdue?')">Overdue</button>
                    </form>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><a href="../dashboarrd/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
