<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare('DELETE FROM assets WHERE id = ?');
    $stmt->execute([$id]);
}

header('Location: list.php');
exit();
?>
