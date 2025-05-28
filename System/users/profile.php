<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];  // Get logged-in user ID from session

$error = '';
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
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
        }
        
        // Handle password update validation
        $password_change_requested = !empty($current_password) || !empty($new_password) || !empty($confirm_password);
        
        if (!$error && $password_change_requested) {
            // All password fields must be filled if any password field is filled
            if (empty($current_password)) {
                $error = "Current password is required to change password.";
            } elseif (empty($new_password)) {
                $error = "New password cannot be empty.";
            } elseif (empty($confirm_password)) {
                $error = "Please confirm your new password.";
            } elseif ($new_password !== $confirm_password) {
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

        // Update user profile if no errors
        if (!$error) {
            try {
                // Update user profile (and password if requested)
                if ($password_change_requested && !$error) {
                    $hashedNewPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
                    $result = $stmt->execute([$full_name, $email, $hashedNewPassword, $userId]);
                    
                    if ($result) {
                        $success = "Profile and password updated successfully.";
                    } else {
                        $error = "Failed to update profile and password.";
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
                    $result = $stmt->execute([$full_name, $email, $userId]);
                    
                    if ($result) {
                        $success = "Profile updated successfully.";
                    } else {
                        $error = "Failed to update profile.";
                    }
                }

                // Refresh user data if update was successful
                if ($success) {
                    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                }
                
            } catch (Exception $e) {
                $error = "An error occurred while updating your profile. Please try again.";
                // Log the actual error for debugging (don't show to user)
                error_log("Profile update error: " . $e->getMessage());
            }
        }
    }
}

include '../../includes/header.php';
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #fafafa;
    }
    h2 {
        color: #333;
    }
    form {
        background: #fff;
        padding: 25px 30px;
        border-radius: 6px;
        max-width: 500px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #007bff;
        outline: none;
    }
    button {
        background-color: #007bff;
        border: none;
        padding: 12px 20px;
        color: white;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 700;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #0056b3;
    }
    hr {
        margin: 30px 0;
        border: none;
        border-top: 1px solid #ddd;
    }
    .message {
        max-width: 500px;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    p.back-link {
        margin-top: 20px;
    }
    p.back-link a {
        text-decoration: none;
        color: #007bff;
        font-weight: 600;
    }
    p.back-link a:hover {
        text-decoration: underline;
    }
    .password-note {
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
        font-style: italic;
    }
</style>
<center>
<h2>My Profile</h2>

<?php if ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" action="">
    <label for="full_name">Full Name:</label>
    <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($user['full_name']) ?>">

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

    <hr>

    <p><strong>Change Password (optional):</strong></p>
    <p class="password-note">Leave blank if you don't want to change your password</p>

    <label for="current_password">Current Password:</label>
    <input type="password" id="current_password" name="current_password" autocomplete="current-password">

    <label for="new_password">New Password:</label>
    <input type="password" id="new_password" name="new_password" autocomplete="new-password">

    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">

    <button type="submit">Update Profile</button>
</form>
</center>

<?php include '../../includes/footer.php'; ?>