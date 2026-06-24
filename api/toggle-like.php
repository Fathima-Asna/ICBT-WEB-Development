<?php
// api/toggle-like.php
// Increments likes count for a travel package

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to like this package.']);
    } else {
        header("Location: ../login.php?error=" . urlencode("Please log in to perform this action."));
    }
    exit;
}

if ($is_ajax) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

$package_id = isset($input['package_id']) ? intval($input['package_id']) : 0;

if ($package_id <= 0) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid package ID.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
    }
    exit;
}

try {
    // Increment likes count
    $stmt = $pdo->prepare("UPDATE packages SET likes_count = likes_count + 1 WHERE id = ?");
    $stmt->execute([$package_id]);

    // Retrieve new count
    $stmt = $pdo->prepare("SELECT likes_count FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();

    if ($package) {
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'likes_count' => $package['likes_count'],
                'message' => 'You liked this package!'
            ]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Package not found.']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
        }
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Error processing like: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
    }
}
?>
