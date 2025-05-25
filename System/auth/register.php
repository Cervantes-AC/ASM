<?php
// System/auth/register.php
require_once '../config/db.php';

// Start session to handle auth and messages
session_start();

// Only admin can register new users, so check if user logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'member'; // Fixed role

    // Simple validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            // Store password as plain text (not recommended)
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $password, $role]);

            $_SESSION['success'] = "User registered successfully.";
            header('Location: list.php');  // Redirect to user list page
            exit;
        }
    }
}
?>

<!-- Simple HTML form for registration -->
<!DOCTYPE html>
<html lang="en">
<head><title>Register User</title></head>
<body>
<h2>Register New Member</h2>
<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="POST" action="">
    Full Name: <input type="text" name="full_name" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="hidden" name="role" value="member">
    <button type="submit">Register</button>
</form>
</body>
</html>
