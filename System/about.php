<?php


// Determine login state and role
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role']       ?? 'guest';
$username   = $_SESSION['full_name']  ?? 'Guest';

// Include header after session started
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About – Asset Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        ul {
            margin-left: 1.5rem;
        }
        a.link {
            color: #007bff;
            text-decoration: none;
        }
        a.link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<main>
    <h1>
        <?php if ($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($username) ?>!
        <?php else: ?>
            About the Asset Management System
        <?php endif; ?>
    </h1>

    <p>
        Welcome to the Asset Management System of Central Mindanao University Supreme Student Council.
        This system provides a centralized platform for tracking, borrowing, and managing university-owned assets 
        across all departments.
    </p>

    <h2>Key Features</h2>
    <ul>
        <li>Efficient asset tracking and inventory management</li>
        <li>Role-based access for Admins, Staff, and Members</li>
        <li>Real-time borrow and return processing</li>
        <li>Overdue alerts and fine management</li>
        <li>Transaction logs and accountability monitoring</li>
    </ul>

    <h2>Our Mission</h2>
    <p>
        To improve transparency, accountability, and convenience in managing student council assets,
        supporting CMU's goal of digital transformation.
    </p>

    <h2>Contact Us</h2>
    <p>
        For inquiries or technical support, please contact the SSC Technical Committee at:
        <br>Email: <a class="link" href="mailto:ssc-tech@cmu.edu.ph">ssc-tech@cmu.edu.ph</a>
    </p>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
