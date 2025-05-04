<?php
session_start();

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
  main { max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  h1 { color: #004080; margin-bottom: 20px; }
  .welcome { margin-bottom: 20px; font-size: 1.2em; }
  .description { margin-bottom: 20px; }
  .dashboard-links a { display: inline-block; margin: 10px 15px 10px 0; padding: 10px 20px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; }
  .dashboard-links a:hover { background: #0056b3; }
</style>
</head>
<body>
<header>
  <div><strong>Asset Management System</strong></div>
  <nav>
    <?php if ($username): ?>
      <span>Welcome, <?= htmlspecialchars($username) ?></span>
      <a href="/src/auth/logout.php">Logout</a>
    <?php else: ?>
      <a href="/src/auth/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
<main>
  <h1>Welcome to the Asset Management System</h1>

  <div class="description">
    <p>The Asset Management System is designed to facilitate the tracking, borrowing, and management of assets within the Central Mindanao University Supreme Student Council. This system aims to streamline asset management processes, ensuring that all assets are accounted for and easily accessible to authorized users.</p>
    <p>Users of this system include:</p>
    <ul>
      <li><strong>Admin:</strong> Responsible for managing users, assets, fines, and logs. Admins have full access to all functionalities of the system.</li>
      <li><strong>Member:</strong> Can request to borrow assets, return borrowed items, and manage any fines incurred.</li>
    </ul>
  </div>

  <?php if (!$username): ?>
    <p>Please <a href="auth/login.php">log in</a> to access your dashboard and manage assets efficiently.</p>
  <?php else: ?>
    <div class="welcome">You are logged in as <strong><?= htmlspecialchars($role) ?></strong>.</div>
    <div class="dashboard-links">
      <?php if ($role === 'admin'): ?>
        <a href="users/list.php">Manage Users</a>
        <a href="assets/list.php">Manage Assets</a>
        <a href="fines/manage.php">Manage Fines</a>
        <a href="logs/view_logs.php">View Logs</a>
      <?php elseif ($role === 'member'): ?>
        <a href="borrow/request.php">Request Borrow</a>
        <a href="borrow/return.php">Return Asset</a>
        <a href="fines/manage.php">Pay Fines</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</main>
</body>
</html>