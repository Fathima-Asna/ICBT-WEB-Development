<?php
// api/update-package.php
// Updates package details (destination, price, description) in SQL

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check authentication and privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Staff or Admin privilege required.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$package_id  = isset($input['package_id']) ? intval($input['package_id']) : 0;
$destination = isset($input['destination']) ? trim($input['destination']) : '';
$price       = isset($input['price']) ? floatval($input['price']) : 0.0;
$description = isset($input['description']) ? trim($input['description']) : '';

if ($package_id <= 0 || empty($destination) || $price <= 0 || empty($description)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all package details and check pricing.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE packages SET destination = ?, price = ?, description = ? WHERE id = ?");
    $stmt->execute([$destination, $price, $description, $package_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Package details updated successfully!'
    ]);
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
