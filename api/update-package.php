<?php
// api/update-package.php
// Updates package details (destination, price, description) in SQL

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication and privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Administrator privilege required.']);
    } else {
        header("Location: ../login.php?error=" . urlencode("Unauthorized."));
    }
    exit;
}

if ($is_ajax) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

$package_id  = isset($input['package_id']) ? intval($input['package_id']) : 0;
$destination = isset($input['destination']) ? trim($input['destination']) : '';
$price       = isset($input['price']) ? floatval($input['price']) : 0.0;
$description = isset($input['description']) ? trim($input['description']) : '';

if ($package_id <= 0 || empty($destination) || $price <= 0 || empty($description)) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all package details and check pricing.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Please fill in all package details and check pricing."));
    }
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE packages SET destination = ?, price = ?, description = ? WHERE id = ?");
    $stmt->execute([$destination, $price, $description, $package_id]);

    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => 'Package details updated successfully!'
        ]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?success=" . urlencode("Package details updated successfully!"));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
    }
}
?>
