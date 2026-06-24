<?php
// api/toggle-save.php
// Toggles bookmark status for a package

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to save this package.']);
    } else {
        header("Location: ../login.php?error=" . urlencode("Please log in to perform this action."));
    }
    exit;
}

$user_id = $_SESSION['user_id'];

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
    // Check if bookmark already exists
    $stmt = $pdo->prepare("SELECT * FROM saved_packages WHERE user_id = ? AND package_id = ?");
    $stmt->execute([$user_id, $package_id]);
    $bookmark = $stmt->fetch();

    if ($bookmark) {
        // Delete bookmark
        $stmt = $pdo->prepare("DELETE FROM saved_packages WHERE user_id = ? AND package_id = ?");
        $stmt->execute([$user_id, $package_id]);
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'saved' => false,
                'message' => 'Package removed from bookmarks.'
            ]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
        }
    } else {
        // Create bookmark
        $stmt = $pdo->prepare("INSERT INTO saved_packages (user_id, package_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $package_id]);
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'saved' => true,
                'message' => 'Package added to bookmarks!'
            ]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
        }
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Error toggling bookmark: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php'));
    }
}
?>
