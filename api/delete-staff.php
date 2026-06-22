<?php
// api/delete-staff.php
// Removes a staff member account from the database (Admin only)

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
$staff_id = isset($input['staff_id']) ? intval($input['staff_id']) : 0;

if ($staff_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid staff ID.'
    ]);
    exit;
}

try {
    // Delete only users with 'staff' role
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
    $stmt->execute([$staff_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Staff member deleted successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Staff member not found or cannot be deleted.'
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
