<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userRole = $_SESSION['role'];
if ($userRole !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission for add or edit
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
        // Check if email is unique for add or edit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?" . ($action === 'edit' ? " AND user_id != ?" : ""));
        if ($action === 'edit') {
            $stmt->execute([$email, $id]);
        } else {
            $stmt->execute([$email]);
        }
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Email already exists.";
        } else {
            if ($action === 'add') {
                // Store password as plain text (no hashing)
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $role, $password]);
                $success = "User added successfully.";
            } elseif ($action === 'edit') {
                if (!empty($password)) {
                    // Update password as plain text (no hashing)
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $password, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $id]);
                }
                $success = "User updated successfully.";
            }
            // Redirect back to list after success to prevent resubmission
            header("Location: list.php");
            exit;
        }
    }
}

// For edit and delete: fetch existing user data
$userData = [
    'full_name' => '',
    'email' => '',
    'role' => '',
];

if ($action === 'edit' || $action === 'delete') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $userData = $stmt->fetch();
    if (!$userData) {
        die("User not found.");
    }
}

if ($action === 'delete') {
    // Perform delete after confirmation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        header("Location: list.php");
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>
    <?php
    if ($action === 'add') echo "Add New User";
    elseif ($action === 'edit') echo "Edit User";
    elseif ($action === 'delete') echo "Delete User";
    else echo "Invalid Action";
    ?>
</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($action === 'delete'): ?>
    <p>Are you sure you want to delete user <strong><?= htmlspecialchars($userData['full_name']) ?></strong>?</p>
    <form method="post">
        <button type="submit">Yes, Delete</button>
        <a href="list.php">Cancel</a>
    </form>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <form method="post" action="">
        <label>Full Name:<br>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($userData['full_name']) ?>">
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" required value="<?= htmlspecialchars($userData['email']) ?>">
        </label><br><br>

        <label>Role:<br>
            <select name="role" required>
                <option value="admin" <?= $userData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="member" <?= $userData['role'] === 'member' ? 'selected' : '' ?>>Member</option>
            </select>
        </label><br><br>

        <label>Password:<br>
            <input type="password" name="password" <?= $action === 'add' ? 'required' : '' ?>>
            <?php if ($action === 'edit'): ?><small>Leave blank to keep current password</small><?php endif; ?>
        </label><br><br>

        <label>Confirm Password:<br>
            <input type="password" name="password_confirm" <?= $action === 'add' ? 'required' : '' ?>>
        </label><br><br>

        <button type="submit"><?= $action === 'add' ? 'Add User' : 'Update User' ?></button>
        <a href="list.php">Cancel</a>
    </form>

<?php else: ?>
    <p>Invalid action specified.</p>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
