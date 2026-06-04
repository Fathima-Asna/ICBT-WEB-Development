<?php
// GlobeTrek Adventures - Database Config and Secure PDO Connection Setup

// Inject CORS Headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS Preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Define frontend base URL for absolute redirects in decoupled setup
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost:8000' || $_SERVER['HTTP_HOST'] === '127.0.0.1:8000')) {
    define('FRONTEND_URL', 'http://localhost:5500'); // Local frontend dev server
} else {
    define('FRONTEND_URL', 'https://globetrek-adventures.vercel.app'); // Production Vercel domain
}

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'globetrek_db');
define('DB_SSL_CA', ''); // e.g. '/path/to/ca-cert.pem' for remote TiDB Serverless SSL

// Initialize PHP Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Enforce real prepared statements for absolute security
    ];

    if (!empty(DB_SSL_CA)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
    }

    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Return connection error securely in JSON format if API query or fail gracefully
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

/**
 * Helper to run secure prepared query statements
 */
function db_query($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt;
}
?>
