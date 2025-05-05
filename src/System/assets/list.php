<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Fetch assets from the database
$stmt = $pdo->query("SELECT * FROM assets ORDER BY asset_id DESC");
$assets = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2>Assets List</h2>
    <a href="add.php" style="margin-bottom: 15px; display: inline-block;">Add New Asset</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Serial Number</th>
                <th>Status</th>
                <th>Date Acquired</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($assets): ?>
                <?php foreach ($assets as $asset): ?>
                    <tr>
                        <td><?= htmlspecialchars($asset['asset_id']) ?></td>
                        <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                        <td><?= htmlspecialchars($asset['asset_description']) ?></td>
                        <td><?= htmlspecialchars($asset['serial_number']) ?></td>
                        <td><?= htmlspecialchars($asset['status']) ?></td>
                        <td><?= htmlspecialchars($asset['date_acquired']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= htmlspecialchars($asset['asset_id']) ?>">Edit</a> |
                            <a href="delete.php?id=<?= htmlspecialchars($asset['asset_id']) ?>" onclick="return confirm('Are you sure to delete this asset?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No assets found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/footer.php';
?>