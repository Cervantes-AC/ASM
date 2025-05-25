<?php
// System/assets/list.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userRole = $_SESSION['role'];

// Fetch all assets
$stmt = $pdo->query("SELECT * FROM assets ORDER BY date_added DESC");
$assets = $stmt->fetchAll();

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

    .action-link {
        color: #007BFF;
        text-decoration: none;
        font-weight: 500;
    }

    .action-link:hover {
        text-decoration: underline;
    }

    .action-link.danger {
        color: #dc3545;
    }

    .unavailable {
        color: #999;
        font-style: italic;
    }

    .btn {
        display: inline-block;
        background-color: #009879;
        color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
    }

    .btn:hover {
        background-color: #007f6d;
    }

    h2 {
        margin-top: 20px;
    }
</style>

<h2>Assets List</h2>

<?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
    <p><a href="add.php" class="btn">Add New Asset</a></p>
<?php endif; ?>

<div class="table-container">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Condition</th>
                <th>Status</th>
                <th>Location</th>
                <th>Date Added</th>
                <?php if ($userRole === 'admin' || $userRole === 'staff' || $userRole === 'member'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                    <td><?= htmlspecialchars($asset['category']) ?></td>
                    <td><?= (int)$asset['quantity'] ?></td>
                    <td><?= htmlspecialchars($asset['condition']) ?></td>
                    <td><?= htmlspecialchars($asset['status']) ?></td>
                    <td><?= htmlspecialchars($asset['location']) ?></td>
                    <td><?= htmlspecialchars($asset['date_added']) ?></td>
                    <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
                        <td>
                            <a href="edit.php?id=<?= $asset['asset_id'] ?>" class="action-link">Edit</a> | 
                            <a href="delete.php?id=<?= $asset['asset_id'] ?>" class="action-link danger" onclick="return confirm('Delete this asset?');">Delete</a>
                        </td>
                    <?php elseif ($userRole === 'member'): ?>
                        <td>
                            <?php if ($asset['status'] === 'available' && $asset['quantity'] > 0): ?>
                                <a href="../borrow/request.php?asset_id=<?= $asset['asset_id'] ?>" class="action-link">Request to Borrow</a>
                            <?php else: ?>
                                <span class="unavailable">Not Available</span>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assets)): ?>
                <tr><td colspan="8">No assets found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
