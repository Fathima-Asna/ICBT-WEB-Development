<?php
// api/update-booking-status.php
// Updates the status of a specific booking (Admin privilege required)

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

$booking_id = isset($input['booking_id']) ? intval($input['booking_id']) : 0;
$status     = isset($input['status']) ? trim($input['status']) : '';

// Validate booking status enum
$valid_statuses = ['Pending', 'Confirmed', 'Cancelled'];
if ($booking_id <= 0 || !in_array($status, $valid_statuses)) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID or status value.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Invalid booking ID or status value."));
    }
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => "Booking status updated to '$status' successfully!"
        ]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?success=" . urlencode("Booking status updated to '$status' successfully!"));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
    }
}
?>
