<?php
// api/login.php
// Processes authentication and starts user role sessions

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Get raw JSON body
$input = json_decode(file_get_contents('php://input'), true);

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';
$role     = isset($input['role']) ? trim($input['role']) : '';

if (empty($username) || empty($password) || empty($role)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all credentials.'
    ]);
    exit;
}

try {
    // Retrieve user by username and role
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    // Verify password (plain text check for development seed requirements)
    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Determine redirect page
        $redirect = 'index.php';
        if ($user['role'] === 'admin') {
            $redirect = 'admin.php';
        } elseif ($user['role'] === 'staff') {
            $redirect = 'staff.php';
        } else {
            $redirect = 'dashboard.php';
        }

        echo json_encode([
            'success' => true,
            'message' => 'Welcome back, ' . htmlspecialchars($user['username']) . '!',
            'redirect' => $redirect
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username, password, or role choice.'
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication failed: ' . $e->getMessage()
    ]);
}
?>
