<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        header('Location: list.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'member';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username)) {
        $error = 'Username is required.';
    } elseif (!in_array($role, ['admin', 'staff', 'member'])) {
        $error = 'Invalid role.';
    } elseif ($id === null && (empty($password) || empty($password_confirm))) {
        $error = 'Password and confirm password are required for new users.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate username
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $id ?: 0]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            if ($id) {
                // Update user
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?');
                    $stmt->execute([$username, $role, $password_hash, $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ? WHERE id = ?');
                    $stmt->execute([$username, $role, $id]);
                }
                $success = 'User updated successfully.';
            } else {
                // Insert new user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                $stmt->execute([$username, $password_hash, $role]);
                $success = 'User created successfully.';
                header('Location: list.php');
                exit();
            }
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container">
    <h2><?= $id ? 'Edit' : 'Add New' ?> User</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="">
        <label for="username">Username *</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? $user['username'] ?? '') ?>" />

        <label for="role">Role *</label>
        <select id="role" name="role" required>
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
        <input type="password" id="password" name="password" <?= $id ? '' : 'required' ?> />

        <label for="password_confirm"><?= $id ? 'Confirm New Password' : 'Confirm Password *' ?></label>
        <input type="password" id="password_confirm" name="password_confirm" <?= $id ? '' : 'required' ?> />

        <button type="submit"><?= $id ? 'Update' : 'Create' ?> User</button>
    </form>
    <a href="list.php">Back to User List</a>
</div>
<?php
include '../includes/footer.php';
?>
