<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

checkAuth();

$userId = $_SESSION['user_id'];  // Get logged-in user ID from session

$error = '';
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT username, full_name, email FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email)) {
        $error = "Full name and email cannot be empty.";
    } else {
        // Check if email is unique for others
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email is already taken by another user.";
        } else {
            // Handle password update if fields filled
            if ($new_password !== '' || $confirm_password !== '') {
                if ($new_password !== $confirm_password) {
                    $error = "New passwords do not match.";
                } elseif (strlen($new_password) < 6) {
                    $error = "New password must be at least 6 characters.";
                } else {
                    // Verify current password before updating
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $hashedPassword = $stmt->fetchColumn();

                    if (!password_verify($current_password, $hashedPassword)) {
                        $error = "Current password is incorrect.";
                    }
                }
            }

            if (!$error) {
                // Update user profile (and password if requested)
                if ($new_password !== '') {
                    $hashedNewPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $hashedNewPassword, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $userId]);
                }

                $success = "Profile updated successfully.";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT username, full_name, email FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            }
        }
    }
}

include '../../includes/header.php';
?>

<h2>My Profile</h2>

<?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" action="">
    <label>Username:<br>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
    </label><br><br>

    <label>Full Name:<br>
        <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name']) ?>">
    </label><br><br>

    <label>Email:<br>
        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
    </label><br><br>

    <hr>

    <p><strong>Change Password (optional):</strong></p>

    <label>Current Password:<br>
        <input type="password" name="current_password">
    </label><br><br>

    <label>New Password:<br>
        <input type="password" name="new_password">
    </label><br><br>

    <label>Confirm New Password:<br>
        <input type="password" name="confirm_password">
    </label><br><br>

    <button type="submit">Update Profile</button>
</form>

<p><a href="../dashboard/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
