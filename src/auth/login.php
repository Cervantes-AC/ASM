<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password_hash'])) { // Use 'password_hash' instead of 'password'
        $_SESSION['user_id'] = $user['user_id']; // Use 'user_id' instead of 'id'
        $_SESSION['role'] = $user['role'];
        header('Location: ../index.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Asset Management System</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
        body {font-family: Arial, sans-serif; background: #f4f4f4;}
        .login-container {
            background: white; max-width: 400px; margin: 80px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        h2 {text-align: center; color: #004080;}
        .error {color: red; margin-bottom: 10px;}
        label {display: block; margin: 10px 0 5px;}
        input {width: 100%; padding: 8px; box-sizing: border-box;}
        button {width: 100%; margin-top: 15px;}
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />

        <button type="submit">Login</button>
    </form>
    <p style="margin-top: 15px;">No account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>