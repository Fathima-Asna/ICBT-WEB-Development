<?php
// GlobeTrek Adventures - Registration Process Handler

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
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Server-Side Verification
if (strlen($username) < 4 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=invalid_username');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=invalid_email');
    exit;
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=weak_password');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=password_mismatch');
    exit;
}

try {
    // Check if user already exists
    $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt_user->execute(['username' => $username]);
    if ($stmt_user->fetch()) {
        header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=username_taken');
        exit;
    }

    $stmt_email = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt_email->execute(['email' => $email]);
    if ($stmt_email->fetch()) {
        header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=email_registered');
        exit;
    }

    // Insert user with password hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, 'user')");
    $stmt_insert->execute([
        'username' => $username,
        'email' => $email,
        'password_hash' => $password_hash
    ]);

    header('Location: ' . FRONTEND_URL . '/join.html?tab=login&registered=1');
    exit;

} catch (PDOException $e) {
    header('Location: ' . FRONTEND_URL . '/join.html?tab=register&error=system_error');
    exit;
}
?>
