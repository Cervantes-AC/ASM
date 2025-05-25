<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userRole = $_SESSION['role'];

if ($userRole !== 'admin') {
    // Only admin allowed
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

// Fetch all users without 'username'
$stmt = $pdo->query("SELECT user_id, full_name, email, role FROM users ORDER BY user_id ASC");
$users = $stmt->fetchAll();

include '../../includes/header.php';
?>

<h2>User Management</h2>

<p><a href="manage.php?action=add">Add New User</a></p>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['user_id']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <a href="manage.php?action=edit&id=<?= $user['user_id'] ?>">Edit</a> | 
                <a href="manage.php?action=delete&id=<?= $user['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><a href="../dashboard/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
