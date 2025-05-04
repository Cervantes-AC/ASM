<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

$id = $_GET['id'] ?? null;

// Fetch existing user data if editing
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        header('Location: list.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete user account
        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        header('Location: list.php');
        exit();
    } elseif (isset($_POST['save'])) {
        // Save or update user details
        $username = trim($_POST['username'] ?? '');
        $role = $_POST['role'] ?? 'member';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validate inputs
        if (empty($username)) {
            $error = 'Username is required.';
        } elseif (!in_array($role, ['admin', 'staff', 'member'])) {
            $error = 'Invalid role selected.';
        } elseif ($id === null && (empty($password) || empty($password_confirm))) {
            $error = 'Password and confirm password are required for new users.';
        } elseif ($password !== $password_confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check for duplicate username excluding current user if editing
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? AND user_id != ?');
            $stmt->execute([$username, $id ?: 0]);
            if ($stmt->fetch()) {
                $error = 'Username already exists.';
            } else {
                if ($id) {
                    // Update existing user
                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ?, password_hash = ? WHERE user_id = ?');
                        $stmt->execute([$username, $role, $password_hash, $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ? WHERE user_id = ?');
                        $stmt->execute([$username, $role, $id]);
                    }
                    $success = 'User updated successfully.';
                    // Refresh user data after update
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                } else {
                    // Insert new user
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
                    $stmt->execute([$username, $password_hash, $role]);
                    $success = 'User created successfully.';
                    header('Location: list.php');
                    exit();
                }
            }
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2><?= $id ? 'Edit User' : 'Add New User' ?></h2>

    <?php if ($error): ?>
        <div class="error" style="color:red; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success" style="color:green; margin-bottom: 15px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="username">Username *</label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            required 
            value="<?= htmlspecialchars($_POST['username'] ?? $user['username'] ?? '') ?>" 
            style="width: 100%; padding: 8px; margin-bottom: 10px;"
        />

        <label for="role">Role *</label>
        <select 
            id="role" 
            name="role" 
            required 
            style="width: 100%; padding: 8px; margin-bottom: 10px;"
        >
            <?php
                $roles = ['admin', 'staff', 'member'];
                $selectedRole = $_POST['role'] ?? $user['role'] ?? 'member';
                foreach ($roles as $roleOption) {
                    $selected = ($selectedRole === $roleOption) ? 'selected' : '';
                    echo "<option value=\"$roleOption\" $selected>" . ucfirst($roleOption) . "</option>";
                }
            ?>
        </select>

        <label for="password"><?= $id ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            <?= $id ? '' : 'required' ?> 
            style="width: 100%; padding: 8px; margin-bottom: 10px;"
        />

        <label for="password_confirm"><?= $id ? 'Confirm New Password' : 'Confirm Password *' ?></label>
        <input 
            type="password" 
            id="password_confirm" 
            name="password_confirm" 
            <?= $id ? '' : 'required' ?> 
            style="width: 100%; padding: 8px; margin-bottom: 20px;"
        />

        <button 
            type="submit" 
            name="save" 
            style="padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;"
        >
            <?= $id ? 'Update' : 'Create' ?> User
        </button>

        <?php if ($id): ?>
            <button 
                type="submit" 
                name="delete" 
                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" 
                style="padding: 10px 20px; background-color: #d9534f; color: white; border: none; cursor: pointer; margin-left: 15px;"
            >
                Delete User
            </button>
        <?php endif; ?>
    </form>

    <p style="margin-top: 20px;">
        <a href="list.php">Back to User List</a>
    </p>
</div>

<?php
include '../includes/footer.php';
?>