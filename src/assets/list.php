<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$stmt = $pdo->query("SELECT * FROM assets ORDER BY created_at DESC");
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
                <th>ID</th><th>Name</th><th>Category</th><th>Serial Number</th><th>Status</th><th>Created At</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($assets as $asset): ?>
            <tr>
                <td><?= htmlspecialchars($asset['id']) ?></td>
                <td><?= htmlspecialchars($asset['name']) ?></td>
                <td><?= htmlspecialchars($asset['category']) ?></td>
                <td><?= htmlspecialchars($asset['serial_number']) ?></td>
                <td><?= htmlspecialchars($asset['status']) ?></td>
                <td><?= htmlspecialchars($asset['created_at']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $asset['id'] ?>">Edit</a> |
                    <a href="delete.php?id=<?= $asset['id'] ?>" onclick="return confirm('Are you sure to delete this asset?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$assets): ?>
            <tr><td colspan="7" style="text-align:center;">No assets found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
include '../includes/footer.php';
?>
