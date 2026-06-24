<?php
// api/add-package.php
// Creates a new travel package (Admin only)

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authorization
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

$destination = isset($input['destination']) ? trim($input['destination']) : '';
$price       = isset($input['price']) ? floatval($input['price']) : 0.0;
$description = isset($input['description']) ? trim($input['description']) : '';
$image_url   = isset($input['image_url']) ? trim($input['image_url']) : '';

if (empty($destination) || $price <= 0 || empty($description)) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields and check pricing.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Please fill in all required fields and check pricing."));
    }
    exit;
}

// Fallback image url if empty
if (empty($image_url)) {
    $image_url = 'images/negombo_lagoon.jpg';
}

try {
    $stmt = $pdo->prepare("INSERT INTO packages (destination, price, description, likes_count, image_url) VALUES (?, ?, ?, 0, ?)");
    $stmt->execute([$destination, $price, $description, $image_url]);

    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => 'New tour package added successfully!'
        ]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?success=" . urlencode("New tour package added successfully!"));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
    }
}
?>
