<?php
// GlobeTrek Adventures - Booking Process Handler

// Inject CORS Headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS Preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db_config.php';

// Force authentication
if (!isset($_SESSION['user_id'])) {
    // Save current target to redirect after login
    $_SESSION['redirect_url'] = 'packages.html';
    header('Location: ' . FRONTEND_URL . '/join.html?tab=login&error=auth_required');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . FRONTEND_URL . '/packages.html');
    exit;
}

$package_id = intval($_POST['package_id'] ?? 0);
$accommodation_id = isset($_POST['accommodation_id']) && $_POST['accommodation_id'] !== '' ? intval($_POST['accommodation_id']) : null;
$travel_date = $_POST['travel_date'] ?? '';
$number_of_guests = intval($_POST['guests'] ?? 1);
$user_id = $_SESSION['user_id'];

$today = date('Y-m-d');

if ($package_id <= 0 || empty($travel_date) || $travel_date < $today || $number_of_guests < 1 || $number_of_guests > 10) {
    header('Location: ' . FRONTEND_URL . '/packages.html?error=invalid_inputs');
    exit;
}

try {
    // 1. Fetch package details to compute price securely on backend
    $stmt_pkg = $conn->prepare("SELECT price, duration FROM packages WHERE id = :id");
    $stmt_pkg->execute(['id' => $package_id]);
    $package = $stmt_pkg->fetch();
    
    if (!$package) {
        header('Location: ' . FRONTEND_URL . '/packages.html?error=package_not_found');
        exit;
    }
    
    // Parse duration (e.g. "8 Days, 7 Nights" -> 8)
    preg_match('/\d+/', $package['duration'], $duration_matches);
    $days = isset($duration_matches[0]) ? intval($duration_matches[0]) : 1;
    
    $package_total = floatval($package['price']) * $number_of_guests;
    $hotel_total = 0;
    
    // 2. Fetch lodging details if selected
    if ($accommodation_id !== null) {
        $stmt_acc = $conn->prepare("SELECT price_per_night FROM accommodations WHERE id = :id");
        $stmt_acc->execute(['id' => $accommodation_id]);
        $hotel = $stmt_acc->fetch();
        
        if ($hotel) {
            $nights = max(1, $days - 1);
            $hotel_total = floatval($hotel['price_per_night']) * $nights * $number_of_guests;
        }
    }
    
    $total_price = $package_total + $hotel_total;
    
    // 3. Create Booking Record
    $stmt_insert = $conn->prepare("
        INSERT INTO bookings (user_id, package_id, accommodation_id, travel_date, total_price, status) 
        VALUES (:user_id, :package_id, :accommodation_id, :travel_date, :total_price, 'pending')
    ");
    $stmt_insert->execute([
        'user_id' => $user_id,
        'package_id' => $package_id,
        'accommodation_id' => $accommodation_id,
        'travel_date' => $travel_date,
        'total_price' => $total_price
    ]);
    
    header('Location: ' . FRONTEND_URL . '/packages.html?booking_success=1');
    exit;

} catch (PDOException $e) {
    header('Location: ' . FRONTEND_URL . '/packages.html?error=booking_system_failed');
    exit;
}
?>
