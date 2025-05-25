<?php
// System/index.php

// Redirect logged-in users to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ./dashboard/index.php');
    exit;
}

// Redirect guests to the About page
header('Location: ./about.php');
exit;
?>
