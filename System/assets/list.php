<?php
// System/assets/list.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

$userRole = $_SESSION['role'];

// Fetch all assets
$stmt = $pdo->query("SELECT * FROM assets ORDER BY date_added DESC");
$assets = $stmt->fetchAll();

include '../../includes/header.php';
?>

<h2>Assets List</h2>

<?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
    <p><a href="add.php">Add New Asset</a></p>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Asset Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Condition</th>
            <th>Status</th>
            <th>Location</th>
            <th>Date Added</th>
            <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
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
                    <a href="edit.php?id=<?= $asset['asset_id'] ?>">Edit</a> | 
                    <a href="delete.php?id=<?= $asset['asset_id'] ?>" onclick="return confirm('Delete this asset?');">Delete</a>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($assets)): ?>
            <tr><td colspan="8">No assets found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
