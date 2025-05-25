<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine login state and user info
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role']      ?? 'guest';
$username   = $_SESSION['full_name'] ?? 'Guest';

// Include header after session started
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<body>

<main style="max-width: 800px; margin: 40px auto; padding: 25px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif; color: #333;">

    <h1 style="font-size: 2rem; margin-bottom: 25px; color: #2c3e50;">
        <?php if ($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($username) ?>!
        <?php else: ?>
            About the Asset Management System
        <?php endif; ?>
    </h1>

    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px;">
        Welcome to the Asset Management System of Central Mindanao University Supreme Student Council.
        This system provides a centralized platform for tracking, borrowing, and managing university-owned assets 
        across all departments efficiently and transparently.
    </p>

    <h2 style="color: #34495e; margin-bottom: 15px;">Key Features</h2>
    <ul style="margin-left: 20px; margin-bottom: 30px; color: #555; font-size: 1.05rem; line-height: 1.5;">
        <li>Efficient asset tracking and inventory management</li>
        <li>Role-based access for Admins, Staff, and Members</li>
        <li>Real-time borrow and return processing</li>
        <li>Overdue alerts and fine management</li>
        <li>Transaction logs and accountability monitoring</li>
    </ul>

    <h2 style="color: #34495e; margin-bottom: 15px;">Our Mission</h2>
    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px;">
        To improve transparency, accountability, and convenience in managing student council assets,
        supporting CMU's goal of digital transformation.
    </p>

    <h2 style="color: #34495e; margin-bottom: 15px;">Contact Us</h2>
    <p style="font-size: 1.1rem; line-height: 1.6;">
        For inquiries or technical support, please contact the SSC Technical Committee at:<br />
        Email: <a class="link" href="mailto:ssc-tech@cmu.edu.ph" style="color: #2e7d32; font-weight: bold; text-decoration: none;">
            ssc-tech@cmu.edu.ph
        </a>
    </p>
    
</main>


<?php include '../includes/footer.php'; ?>

</body>
</html>
