<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['package_name']) || !isset($data['state']) || !isset($data['rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$package_name = trim($data['package_name']);
$state = trim($data['state']);
$rating = intval($data['rating']);

// Validate rating range
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit();
}

// Get user IP and user agent
$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

try {
    // Check if user has already rated this package (optional duplicate prevention)
    // You can remove this check if you want to allow multiple ratings from same IP
    $check_stmt = $conn->prepare("SELECT id FROM ratings WHERE package_name = ? AND state = ? AND user_ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $check_stmt->bind_param("sss", $package_name, $state, $user_ip);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You have already rated this package in the last 24 hours']);
        exit();
    }
    $check_stmt->close();

    // Insert rating
    $stmt = $conn->prepare("INSERT INTO ratings (package_name, state, rating, user_ip, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $package_name, $state, $rating, $user_ip, $user_agent);
    
    if ($stmt->execute()) {
        // Get updated average rating
        $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM ratings WHERE package_name = ? AND state = ?");
        $avg_stmt->bind_param("ss", $package_name, $state);
        $avg_stmt->execute();
        $avg_result = $avg_stmt->get_result();
        $avg_data = $avg_result->fetch_assoc();
        $avg_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'average_rating' => round($avg_data['avg_rating'], 1),
            'total_ratings' => $avg_data['total_ratings']
        ]);
    } else {
        throw new Exception('Failed to insert rating');
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>