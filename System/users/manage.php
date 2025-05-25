<?php
// user_manage.php - Single file version with embedded styles

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

require_once '../config/db.php'; // Keep DB connection external for security and reuse

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($full_name) || empty($email) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif ($action === 'add' && (empty($password) || $password !== $password_confirm)) {
        $error = "Passwords are required and must match.";
    } elseif ($action === 'edit' && !empty($password) && $password !== $password_confirm) {
        $error = "Passwords must match.";
    } else {
        // Check unique email
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        if ($action === 'edit') {
            $sql .= " AND user_id != ?";
            $params[] = $id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email already exists.";
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $role, $password]);
                $success = "User added successfully.";
            } elseif ($action === 'edit') {
                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $password, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $id]);
                }
                $success = "User updated successfully.";
            }
            header("Location: user_manage.php?action=list");
            exit;
        }
    }
}

// Fetch existing user data for edit/delete
$userData = [
    'full_name' => '',
    'email' => '',
    'role' => '',
];

if ($action === 'edit' || $action === 'delete') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userData) {
        die("User not found.");
    }
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    header("Location: user_manage.php?action=list");
    exit;
}

// Fetch all users for list action
$users = [];
if ($action === 'list' || $action === '') {
    $stmt = $pdo->query("SELECT user_id, full_name, email, role FROM users ORDER BY full_name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Management</title>
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 900px;
        margin: 20px auto;
        padding: 0 10px;
        background: #f9f9f9;
        color: #333;
    }
    h2 {
        color: #009879;
        margin-bottom: 10px;
    }
    a.button, button {
        background-color: #009879;
        border: none;
        padding: 8px 14px;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        cursor: pointer;
    }
    a.button:hover, button:hover {
        background-color: #007f6d;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background: white;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    thead {
        background-color: #009879;
        color: white;
    }
    tbody tr:nth-child(even) {
        background-color: #f3f3f3;
    }
    .error {
        color: #dc3545;
        margin-bottom: 1rem;
    }
    .success {
        color: #28a745;
        margin-bottom: 1rem;
    }
    label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }
    input[type="text"], input[type="email"], input[type="password"], select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    form {
        background: white;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        max-width: 400px;
    }
    .actions a {
        margin-right: 10px;
        color: #007BFF;
        text-decoration: none;
    }
    .actions a:hover {
        text-decoration: underline;
    }
    .danger {
        color: #dc3545;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: normal;
        padding: 0;
    }
</style>
</head>
<body>

<?php if ($action === 'list' || $action === ''): ?>
    <h2>User List</h2>
    <p><a href="?action=add" class="button">Add New User</a></p>
    <table>
        <thead>
            <tr>
                <th>Full Name</th><th>Email</th><th>Role</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="actions">
                        <a href="?action=edit&id=<?= $user['user_id'] ?>">Edit</a>
                        <a href="?action=delete&id=<?= $user['user_id'] ?>" style="color:#dc3545;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <h2><?= $action === 'add' ? "Add New User" : "Edit User" ?></h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="full_name">Full Name</label>
        <input id="full_name" name="full_name" type="text" required value="<?= htmlspecialchars($userData['full_name']) ?>" />

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars($userData['email']) ?>" />

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="admin" <?= $userData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="member" <?= $userData['role'] === 'member' ? 'selected' : '' ?>>Member</option>
        </select>

        <label for="password">Password <?= $action === 'edit' ? '<small>(leave blank to keep current)</small>' : '' ?></label>
        <input id="password" name="password" type="password" <?= $action === 'add' ? 'required' : '' ?> />

        <label for="password_confirm">Confirm Password <?= $action === 'add' ? '' : '<small>(leave blank to keep current)</small>' ?></label>
        <input id="password_confirm" name="password_confirm" type="password" <?= $action === 'add' ? 'required' : '' ?> />

        <br><br>
        <button type="submit"><?= $action === 'add' ? 'Add User' : 'Update User' ?></button>
        <a href="?action=list" class="button" style="background:#777; margin-left:10px;">Cancel</a>
    </form>

<?php elseif ($action === 'delete'): ?>

    <h2>Delete User</h2>
    <p>Are you sure you want to delete user <strong><?= htmlspecialchars($userData['full_name']) ?></strong>?</p>
    <form method="post" action="">
        <button type="submit" style="background:#dc3545;">Yes, Delete</button>
        <a href="?action=list" class="button" style="background:#777; margin-left:10px;">Cancel</a>
    </form>

<?php else: ?>
    <p>Invalid action specified.</p>
<?php endif; ?>

</body>
</html>
