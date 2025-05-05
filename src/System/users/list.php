<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Only admin access
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2>User List</h2>
    <a href="manage.php" style="margin-bottom: 15px; display: inline-block;">Add New User</a>
    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Created At</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if ($users): ?>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td> <!-- Use 'user_id' -->
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <a href="manage.php?id=<?= $user['user_id'] ?>">Edit</a> <!-- Use 'user_id' -->
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
include '../includes/footer.php';
?>