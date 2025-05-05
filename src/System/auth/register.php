<?php
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if (empty($username) || empty($password) || empty($password_confirm)) {
        $error = 'All fields are required.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            // Hash the password and insert the new user as a member
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, 'member')");
            $stmt->execute([$username, $password_hash, '']); // Assuming full_name is optional
            $success = 'Registration successful, you can now <a href="login.php">login</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - Asset Management System</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
        body {font-family: Arial, sans-serif; background: #f4f4f4;}
        .register-container {
            background: white; max-width: 400px; margin: 80px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        h2 {text-align: center; color: #004080;}
        .error {color: red; margin-bottom: 10px;}
        .success {color: green; margin-bottom: 10px;}
        label {display: block; margin: 10px 0 5px;}
        input {width: 100%; padding: 8px; box-sizing: border-box;}
        button {width: 100%; margin-top: 15px;}
    </style>
</head>
<body>
<div class="register-container">
    <h2>Register</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />

        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required />

        <button type="submit">Register</button>
    </form>
    <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>