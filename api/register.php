<?php
// api/register.php
// Registers a new customer user account

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($is_ajax) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($username) || empty($password)) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all fields.'
        ]);
    } else {
        header("Location: ../register.php?error=" . urlencode("Please fill in all fields."));
    }
    exit;
}

if (strlen($username) < 3 || strlen($username) > 100) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Username must be between 3 and 100 characters.'
        ]);
    } else {
        header("Location: ../register.php?error=" . urlencode("Username must be between 3 and 100 characters."));
    }
    exit;
}

if (strlen($password) < 6) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters long.'
        ]);
    } else {
        header("Location: ../register.php?error=" . urlencode("Password must be at least 6 characters long."));
    }
    exit;
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        if ($is_ajax) {
            echo json_encode([
                'success' => false,
                'message' => 'Username is already taken.'
            ]);
        } else {
            header("Location: ../register.php?error=" . urlencode("Username is already taken."));
        }
        exit;
    }

    // Insert user (plain text password to match development seed standards)
    $stmt = $pdo->prepare("INSERT INTO users (role, username, password) VALUES ('customer', ?, ?)");
    $stmt->execute([$username, $password]);

    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Redirecting to Login...'
        ]);
    } else {
        header("Location: ../login.php?success=" . urlencode("Account created successfully! Please sign in."));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
    } else {
        header("Location: ../register.php?error=" . urlencode("Registration failed: " . $e->getMessage()));
    }
}
?>
