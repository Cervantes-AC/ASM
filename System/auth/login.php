<?php
session_start();
require_once '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, full_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Plain text password comparison instead of password_verify()
        if ($user && $user['status'] === 'active' && $password === $user['password']) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header('Location: ../dashboard/index.php');
            exit;
        } else {
            $error = "Invalid email or password, or account inactive.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - Asset Management System</title>
    <style>
        /* Your exact styles here, unchanged */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f0f0;
        }
        nav {
            background: #007bff;
            padding: 1rem 0;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 0.5rem 1rem;
        }
        nav ul li a:hover {
            background: #0056b3;
            border-radius: 4px;
        }
        .login-container {
            max-width: 420px;
            margin: 3rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        label {
            font-weight: bold;
            color: #555;
        }
        input[type="email"],
        input[type="password"] {
            padding: 0.6rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            padding: 0.75rem;
            font-size: 1.1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            required
            autofocus
            autocomplete="username"
        >

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
