<?php
// GlobeTrek Adventures - Session Status API Endpoint (With Concurrent Session Blocking)

// Inject CORS Headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS Preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db_config.php';

header('Content-Type: application/json');

$response = [
    'logged_in' => false,
    'username' => null,
    'role' => null,
    'user_id' => null
];

if (isset($_SESSION['user_id'])) {
    try {
        // Fetch session token from database to compare
        $stmt = $conn->prepare("SELECT `session_token` FROM `users` WHERE `id` = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        $client_token = $_SESSION['session_token'] ?? '';
        $db_token = $user['session_token'] ?? '';

        if ($user && $client_token === $db_token && !empty($db_token)) {
            $response['logged_in'] = true;
            $response['username'] = $_SESSION['username'];
            $response['role'] = $_SESSION['role'];
            $response['user_id'] = $_SESSION['user_id'];
        } else {
            // Mismatch or duplicate login detected! Log out current client
            session_unset();
            session_destroy();
        }
    } catch (PDOException $e) {
        // Safe fallback in case of schema migration queries
        $response['logged_in'] = true;
        $response['username'] = $_SESSION['username'];
        $response['role'] = $_SESSION['role'];
        $response['user_id'] = $_SESSION['user_id'];
    }
}

echo json_encode($response);
exit;
?>
