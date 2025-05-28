<?php
// user_manage.php - Single file version with embedded styles and filtering

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

require_once '../config/db.php'; // Keep DB connection external for security and reuse

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter = $_GET['filter'] ?? 'all'; // New filter parameter

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($full_name) || empty($email) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif ($action === 'add' && (empty($password) || $password !== $password_confirm)) {
        $error = "Passwords are required and must match.";
    } elseif ($action === 'edit' && !empty($password) && $password !== $password_confirm) {
        $error = "Passwords must match.";
    } else {
        // Check unique email
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        if ($action === 'edit') {
            $sql .= " AND user_id != ?";
            $params[] = $id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email already exists.";
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $role, $password]);
                $success = "User added successfully.";
            } elseif ($action === 'edit') {
                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $password, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $email, $role, $id]);
                }
                $success = "User updated successfully.";
            }
            header("Location: manage.php?action=list&filter=" . $filter);
            exit;
        }
    }
}

// Fetch existing user data for edit/delete
$userData = [
    'full_name' => '',
    'email' => '',
    'role' => '',
];

if ($action === 'edit' || $action === 'delete') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userData) {
        die("User not found.");
    }
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    header("Location: manage.php?action=list&filter=" . $filter);
    exit;
}

// Fetch all users for list action with filtering
$users = [];
if ($action === 'list' || $action === '') {
    $sql = "SELECT user_id, full_name, email, role FROM users";
    $params = [];
    
    // Apply filter based on selection
    if ($filter === 'admin') {
        $sql .= " WHERE role = 'admin'";
    } elseif ($filter === 'client') {
        $sql .= " WHERE role = 'member'";
    }
    // 'all' doesn't need a WHERE clause
    
    $sql .= " ORDER BY full_name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get counts for display
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$clientCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Management - CMU-SSC</title>
<style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        color: #333;
        line-height: 1.6;
    }
    
    /* Main content container */
    .main-content {
        max-width: 900px;
        margin: 20px auto;
        padding: 0 10px;
    }
    
    h2 {
        color: #009879;
        margin-bottom: 10px;
    }
    a.button, button {
        background-color: #009879;
        border: none;
        padding: 8px 14px;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        cursor: pointer;
    }
    a.button:hover, button:hover {
        background-color: #007f6d;
    }
    
    /* Header container for button and filter */
    .header-container {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    /* Filter dropdown styles */
    .filter-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .filter-btn {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        font-size: 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }
    
    .filter-btn:hover {
        background-color: #0056b3;
    }
    
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        border-radius: 4px;
        z-index: 1;
        top: 100%;
        left: 0;
        border: 1px solid #ddd;
    }
    
    .dropdown-content a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        border-bottom: 1px solid #f0f0f0;
        font-weight: normal;
    }
    
    .dropdown-content a:last-child {
        border-bottom: none;
    }
    
    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }
    
    .dropdown-content a.active {
        background-color: #007bff;
        color: white;
    }
    
    .filter-dropdown:hover .dropdown-content {
        display: block;
    }
    
    /* Stats container */
    .stats-container {
        background: white;
        padding: 15px 20px;
        border-radius: 6px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .filter-stats {
        color: #666;
        font-size: 14px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .filter-stats span {
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 3px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background: white;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    thead {
        background-color: #009879;
        color: white;
    }
    tbody tr:nth-child(even) {
        background-color: #f3f3f3;
    }
    .error {
        color: #dc3545;
        margin-bottom: 1rem;
    }
    .success {
        color: #28a745;
        margin-bottom: 1rem;
    }
    label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }
    input[type="text"], input[type="email"], input[type="password"], select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    form {
        background: white;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        max-width: 400px;
    }
    .actions a {
        margin-right: 10px;
        color: #007BFF;
        text-decoration: none;
    }
    .actions a:hover {
        text-decoration: underline;
    }
    .danger {
        color: #dc3545;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: normal;
        padding: 0;
    }
    
    @media (max-width: 600px) {
        .header-container {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-stats {
            flex-direction: column;
            gap: 10px;
        }
        .main-content {
            padding: 0 5px;
        }
    }
</style>
</head>
<body>

<!-- Include Navbar -->
<?php include '../../includes/navbar.php'; ?>

<div class="main-content">

<?php if ($action === 'list' || $action === ''): ?>
    
    <h2>User Management</h2>
    
    <!-- Header with Add Button and Filter -->
    <div class="header-container">
        <a href="?action=add&filter=<?= $filter ?>" class="button">Add New User</a>
        
        <div class="filter-dropdown">
            <button class="filter-btn">
                Filter: 
                <?php 
                $filterLabels = [
                    'all' => 'All Accounts',
                    'admin' => 'Admin',
                    'client' => 'Client (Members)'
                ];
                echo $filterLabels[$filter] ?? 'All Accounts';
                ?>
                <span>▼</span>
            </button>
            <div class="dropdown-content">
                <a href="?action=list&filter=all" <?= $filter === 'all' ? 'class="active"' : '' ?>>
                    All Accounts (<?= $totalUsers ?>)
                </a>
                <a href="?action=list&filter=admin" <?= $filter === 'admin' ? 'class="active"' : '' ?>>
                    Admin (<?= $adminCount ?>)
                </a>
                <a href="?action=list&filter=client" <?= $filter === 'client' ? 'class="active"' : '' ?>>
                    Client - Asset Borrowers (<?= $clientCount ?>)
                </a>
            </div>
        </div>
    </div>
    
    <!-- Stats Container -->
    <div class="stats-container">
        <div class="filter-stats">
            <span>Total Users: <?= $totalUsers ?></span>
            <span>Admins: <?= $adminCount ?></span>
            <span>Clients: <?= $clientCount ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Full Name</th><th>Email</th><th>Role</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span style="background: <?= $user['role'] === 'admin' ? '#dc3545' : '#28a745' ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                            <?= $user['role'] === 'admin' ? 'ADMIN' : 'CLIENT' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="?action=edit&id=<?= $user['user_id'] ?>&filter=<?= $filter ?>">Edit</a>
                        <a href="?action=delete&id=<?= $user['user_id'] ?>&filter=<?= $filter ?>" style="color:#dc3545;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No users found for the selected filter.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <h2><?= $action === 'add' ? "Add New User" : "Edit User" ?></h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="full_name">Full Name</label>
        <input id="full_name" name="full_name" type="text" required value="<?= htmlspecialchars($userData['full_name']) ?>" />

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars($userData['email']) ?>" />

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="admin" <?= $userData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="member" <?= $userData['role'] === 'member' ? 'selected' : '' ?>>Client (Member)</option>
        </select>

        <label for="password">Password <?= $action === 'edit' ? '<small>(leave blank to keep current)</small>' : '' ?></label>
        <input id="password" name="password" type="password" <?= $action === 'add' ? 'required' : '' ?> />

        <label for="password_confirm">Confirm Password <?= $action === 'add' ? '' : '<small>(leave blank to keep current)</small>' ?></label>
        <input id="password_confirm" name="password_confirm" type="password" <?= $action === 'add' ? 'required' : '' ?> />

        <br><br>
        <button type="submit"><?= $action === 'add' ? 'Add User' : 'Update User' ?></button>
        <a href="?action=list&filter=<?= $filter ?>" class="button" style="background:#777; margin-left:10px;">Cancel</a>
    </form>

<?php elseif ($action === 'delete'): ?>

    <h2>Delete User</h2>
    <p>Are you sure you want to delete user <strong><?= htmlspecialchars($userData['full_name']) ?></strong>?</p>
    <form method="post" action="">
        <button type="submit" style="background:#dc3545;">Yes, Delete</button>
        <a href="?action=list&filter=<?= $filter ?>" class="button" style="background:#777; margin-left:10px;">Cancel</a>
    </form>

<?php else: ?>
    <p>Invalid action specified.</p>
<?php endif; ?>

</div>

</body>
</html>