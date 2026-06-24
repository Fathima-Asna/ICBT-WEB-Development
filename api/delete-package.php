<?php
// api/delete-package.php
// Removes a package from the database (Admin only)

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

$package_id = isset($input['package_id']) ? intval($input['package_id']) : 0;

if ($package_id <= 0) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid package ID.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Invalid package ID."));
    }
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);

    if ($stmt->rowCount() > 0) {
        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Tour package removed from catalog successfully!'
            ]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?success=" . urlencode("Tour package removed from catalog successfully!"));
        }
    } else {
        if ($is_ajax) {
            echo json_encode([
                'success' => false,
                'message' => 'Package not found or already deleted.'
            ]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Package not found or already deleted."));
        }
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
    }
}
?>
