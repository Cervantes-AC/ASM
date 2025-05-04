<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$asset_id = $_GET['id'] ?? null; // Renamed variable for clarity

if ($asset_id) {
    $stmt = $pdo->prepare('DELETE FROM assets WHERE asset_id = ?'); // Changed 'id' to 'asset_id'
    $stmt->execute([$asset_id]);
}

header('Location: list.php');
exit();
?>