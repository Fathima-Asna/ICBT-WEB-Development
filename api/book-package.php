<?php
// api/book-package.php
// Inserts a new booking record for the logged-in customer

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to book this package.']);
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
    // Insert new booking (default status is Confirmed as per SQL definition)
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, package_id, status) VALUES (?, ?, 'Confirmed')");
    $stmt->execute([$user_id, $package_id]);
    
    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking successfully confirmed!'
        ]);
    } else {
        header("Location: ../dashboard.php?success=" . urlencode("Booking successfully confirmed!"));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Error booking package: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?error=" . urlencode("Error booking package: " . $e->getMessage()));
    }
}
?>
