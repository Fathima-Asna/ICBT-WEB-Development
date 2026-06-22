<?php
// api/delete-package.php
// Removes a package from the database (Admin only)

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
$package_id = isset($input['package_id']) ? intval($input['package_id']) : 0;

if ($package_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid package ID.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tour package removed from catalog successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Package not found or already deleted.'
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
