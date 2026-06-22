<?php
// Central PDO Database Connection script for GlobeTrek Adventures

$host = '127.0.0.1';
$db   = 'globetrek_db';
$user = 'root';
$pass = ''; // Default password for XAMPP is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If connection fails, output a neat message or error
     header('Content-Type: application/json', true, 500);
     echo json_encode([
         'success' => false,
         'message' => 'Database connection failed: ' . $e->getMessage()
     ]);
     exit;
}
?>
