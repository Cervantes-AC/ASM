<?php
require_once '../../includes/auth_check.php';

checkAuth();

$userRole = $_SESSION['role'];
if ($userRole !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied.";
    exit;
}

include '../../includes/header.php';
?>

<h2>User Roles</h2>

<ul>
    <li><strong>Admin</strong> - Full system access.</li>
    <li><strong>Staff</strong> - Limited access to manage assets and requests.</li>
    <li><strong>Member</strong> - Can request and borrow assets.</li>
</ul>

<p><a href="../dashboard/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
