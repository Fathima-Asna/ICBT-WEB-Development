<?php
// api/submit-query.php
// Processes user questions or logs replies from staff

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to perform this action.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$input = json_decode(file_get_contents('php://input'), true);

// Case 1: Staff/Admin replying to a query
if (isset($input['query_id']) && isset($input['answer_text'])) {
    if ($role !== 'staff' && $role !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Staff privilege required.'
        ]);
        exit;
    }

    $query_id = intval($input['query_id']);
    $answer_text = trim($input['answer_text']);

    if ($query_id <= 0 || empty($answer_text)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid reply content.'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE queries SET answer_text = ?, status = 'Answered' WHERE id = ?");
        $stmt->execute([$answer_text, $query_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Reply posted successfully!'
        ]);
    } catch (\PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Case 2: Customer asking a question about a package
if (isset($input['package_id']) && isset($input['question_text'])) {
    $package_id = intval($input['package_id']);
    $question_text = trim($input['question_text']);

    if ($package_id <= 0 || empty($question_text)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid query content.'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO queries (user_id, package_id, question_text, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $package_id, $question_text]);

        echo json_encode([
            'success' => true,
            'message' => 'Your question has been submitted!'
        ]);
    } catch (\PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// If neither parameters set
echo json_encode([
    'success' => false,
    'message' => 'Invalid API request format.'
]);
?>
