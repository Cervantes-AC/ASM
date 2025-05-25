<?php
// System/config/db.php

session_start();  // Ensure sessions are started here if not started already

$host = 'localhost';    
$db   = 'final';        
$user = 'root';         
$pass = '';             
$charset = 'utf8mb4';   

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Disable emulated prepares for real prepares
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Stop script and display error message on connection failure
    exit('Database connection failed: ' . $e->getMessage());
}
