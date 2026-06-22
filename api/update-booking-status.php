<?php
// api/update-booking-status.php
// Updates the status of a specific booking (Staff / Admin privilege required)

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

$booking_id = isset($input['booking_id']) ? intval($input['booking_id']) : 0;
$status     = isset($input['status']) ? trim($input['status']) : '';

// Validate booking status enum
$valid_statuses = ['Pending', 'Confirmed', 'Cancelled'];
if ($booking_id <= 0 || !in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid booking ID or status value.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    echo json_encode([
        'success' => true,
        'message' => "Booking status updated to '$status' successfully!"
    ]);
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
