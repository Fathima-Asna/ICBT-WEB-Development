<?php
// api/submit-query.php
// Processes user questions or logs replies from admin

session_start();
require_once '../config/db.php';

$is_ajax = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to perform this action.']);
    } else {
        header("Location: ../login.php?error=" . urlencode("Please log in to perform this action."));
    }
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($is_ajax) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

// Case 1: Admin replying to a query
if (isset($input['query_id']) && isset($input['answer_text'])) {
    if ($role !== 'admin') {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Administrator privilege required.']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Unauthorized."));
        }
        exit;
    }

    $query_id = intval($input['query_id']);
    $answer_text = trim($input['answer_text']);

    if ($query_id <= 0 || empty($answer_text)) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Invalid reply content.']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Invalid reply content."));
        }
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE queries SET answer_text = ?, status = 'Answered' WHERE id = ?");
        $stmt->execute([$answer_text, $query_id]);

        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Reply posted successfully!']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?success=" . urlencode("Reply posted successfully!"));
        }
    } catch (\PDOException $e) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
        }
    }
    exit;
}

// Case 2: Customer asking a question about a package
if (isset($input['package_id']) && isset($input['question_text'])) {
    $package_id = intval($input['package_id']);
    $question_text = trim($input['question_text']);

    if ($package_id <= 0 || empty($question_text)) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Invalid query content.']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?error=" . urlencode("Invalid query content."));
        }
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO queries (user_id, package_id, question_text, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $package_id, $question_text]);

        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Your question has been submitted!']);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?success=" . urlencode("Your question has been submitted!"));
        }
    } catch (\PDOException $e) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } else {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../packages.php') . "?error=" . urlencode("Database error: " . $e->getMessage()));
        }
    }
    exit;
}

// If neither parameters set
if ($is_ajax) {
    echo json_encode(['success' => false, 'message' => 'Invalid API request format.']);
} else {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
}
?>
