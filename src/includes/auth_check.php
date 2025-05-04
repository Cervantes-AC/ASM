<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../src/auth/login.php');
    exit();
}
?>
