<?php
// System/assets/delete.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';


// Only admin can delete assets
if ($_SESSION['role'] !== 'admin') {
    header("Location: list.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$asset_id = (int) $_GET['id'];

// Optional: you might want to check if asset is currently borrowed or reserved before deletion

// Delete asset
$stmt = $pdo->prepare("DELETE FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);

header("Location: list.php?msg=Asset deleted successfully");
exit;
