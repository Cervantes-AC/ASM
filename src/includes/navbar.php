<?php

// Check if the session variables are set
$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Asset Management System Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; color: #333; }
        header { background: #004080; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }

        /* Navbar styles */
        .navbar {
            background-color: #004080; /* Dark blue background */
            padding: 10px 20px; /* Padding around the navbar */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        .navbar a {
            color: white; /* White text color */
            text-decoration: none; /* Remove underline */
            padding: 10px 15px; /* Padding for links */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        .navbar a:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .logout {
            margin-left: auto; /* Push logout link to the right */
            background-color: #d9534f; /* Red background for logout */
            padding: 10px 15px;
            border-radius: 5px;
        }

        .logout:hover {
            background-color: #c9302c; /* Darker red on hover */
        }
    </style>
</head>
<body>
<header>
    <div><strong>Asset Management System</strong></div>
    <nav class="navbar">
        <a href="index.php">Dashboard</a>
        <a href="assets/list.php">Assets</a>
        
        <a href="notifications/index.php">Notifications</a>
        <?php if ($username): ?>
            <a href="auth/logout.php" class="logout">Logout</a>
        <?php else: ?>
            <a href="auth/login.php" class="logout">Login</a>
        <?php endif; ?>
    </nav>
</header>
</body>
</html>