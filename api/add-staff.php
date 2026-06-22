<?php
// api/add-staff.php
// Registers new travel agency staff members (Admin only)

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Administrator privilege required.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a username and password.'
    ]);
    exit;
}

try {
    // Check if username is already taken
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists.'
        ]);
        exit;
    }

    // Insert staff user
    $stmt = $pdo->prepare("INSERT INTO users (role, username, password) VALUES ('staff', ?, ?)");
    $stmt->execute([$username, $password]);

    echo json_encode([
        'success' => true,
        'message' => 'Staff account created successfully!'
    ]);
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
