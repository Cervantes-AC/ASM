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

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f9f9f9;
    }
    h2 {
        color: #333;
    }
    a.button {
        display: inline-block;
        padding: 8px 15px;
        margin-bottom: 15px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    a.button:hover {
        background-color: #0056b3;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #f1f1f1;
        font-weight: 700;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .actions a {
        margin-right: 10px;
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }
    .actions a:hover {
        text-decoration: underline;
    }
    p.back-link {
        margin-top: 20px;
    }
</style>

<h2>User Management</h2>

<a href="manage.php?action=add" class="button">Add New User</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td class="actions">
                        <a href="manage.php?action=edit&id=<?= $user['user_id'] ?>">Edit</a>
                        <a href="manage.php?action=delete&id=<?= $user['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
