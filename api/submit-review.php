<?php
// api/submit-review.php
// Processes package review submission (customer only)

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
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

$package_id   = isset($input['package_id']) ? intval($input['package_id']) : 0;
$rating       = isset($input['rating']) ? intval($input['rating']) : 0;
$comment_text = isset($input['comment_text']) ? trim($input['comment_text']) : '';

if ($package_id <= 0 || $rating < 1 || $rating > 5 || empty($comment_text)) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all review details and rate 1-5 stars.']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?error=" . urlencode("Please fill in all review details and rate 1-5 stars."));
    }
    exit;
}

try {
    // Insert review
    $stmt = $pdo->prepare("INSERT INTO reviews (package_id, user_id, rating, comment_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$package_id, $user_id, $rating, $comment_text]);

    if ($is_ajax) {
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?success=" . urlencode("Review submitted successfully!"));
    }
} catch (\PDOException $e) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
    }
}
?>
