<?php
// GlobeTrek Adventures - Login Process Handler

// Inject CORS Headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS Preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=login');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=login&error=empty_fields');
    exit;
}

try {
    // Dynamic database self-healing: ensure session_token column exists
    try {
        $conn->exec("ALTER TABLE `users` ADD COLUMN `session_token` VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $ex) {
        // Safe to ignore if column already exists
    }

    // Find user securely using prepared statements
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, establish session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Generate cryptographically secure session token
        $session_token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $session_token;

        // Store session token in database to prevent concurrent logins
        $stmt_update = $conn->prepare("UPDATE `users` SET `session_token` = :token WHERE `id` = :id");
        $stmt_update->execute(['token' => $session_token, 'id' => $user['id']]);

        // Redirect URL if redirect session was set
        if (isset($_SESSION['redirect_url'])) {
            $redirect = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            if (strpos($redirect, 'http') !== 0) {
                $redirect = FRONTEND_URL . '/' . ltrim($redirect, '/');
            }
            header('Location: ' . $redirect);
        } else {
            header('Location: ' . FRONTEND_URL . '/index.html');
        }
        exit;
    } else {
        header('Location: ' . FRONTEND_URL . '/join.html?tab=login&error=invalid_credentials');
        exit;
    }

} catch (PDOException $e) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=login&error=system_error');
    exit;
}
?>
