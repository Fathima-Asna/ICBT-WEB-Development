<?php
// api/toggle-save.php
// Toggles bookmark status for a package

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to save this package.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$package_id = isset($input['package_id']) ? intval($input['package_id']) : 0;

if ($package_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid package ID.'
    ]);
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
        echo json_encode([
            'success' => true,
            'saved' => false,
            'message' => 'Package removed from bookmarks.'
        ]);
    } else {
        // Create bookmark
        $stmt = $pdo->prepare("INSERT INTO saved_packages (user_id, package_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $package_id]);
        echo json_encode([
            'success' => true,
            'saved' => true,
            'message' => 'Package added to bookmarks!'
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error toggling bookmark: ' . $e->getMessage()
    ]);
}
?>
