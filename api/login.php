<?php
// api/login.php
// Processes authentication and starts user role sessions

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
$role     = isset($input['role']) ? trim($input['role']) : '';

if (empty($username) || empty($password) || empty($role)) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all credentials.'
        ]);
    } else {
        header("Location: ../login.php?error=" . urlencode("Please fill in all credentials."));
    }
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
        } else {
            $redirect = 'dashboard.php';
        }

        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back, ' . htmlspecialchars($user['username']) . '!',
                'redirect' => $redirect
            ]);
        } else {
            header("Location: ../" . $redirect);
        }
    } else {
        if ($is_ajax) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username, password, or role choice.'
            ]);
        } else {
            header("Location: ../login.php?error=" . urlencode("Invalid username, password, or role choice."));
        }
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Authentication failed: ' . $e->getMessage()
        ]);
    } else {
        header("Location: ../login.php?error=" . urlencode('Authentication failed: ' . $e->getMessage()));
    }
}
?>
