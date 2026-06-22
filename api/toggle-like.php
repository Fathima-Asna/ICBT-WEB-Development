<?php
// api/toggle-like.php
// Increments likes count for a travel package

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to like this package.'
    ]);
    exit;
}

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
    // Increment likes count
    $stmt = $pdo->prepare("UPDATE packages SET likes_count = likes_count + 1 WHERE id = ?");
    $stmt->execute([$package_id]);

    // Retrieve new count
    $stmt = $pdo->prepare("SELECT likes_count FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();

    if ($package) {
        echo json_encode([
            'success' => true,
            'likes_count' => $package['likes_count'],
            'message' => 'You liked this package!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Package not found.'
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing like: ' . $e->getMessage()
    ]);
}
?>
