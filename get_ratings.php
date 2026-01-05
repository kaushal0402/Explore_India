<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$package_name = isset($_GET['package_name']) ? trim($_GET['package_name']) : null;
$state = isset($_GET['state']) ? trim($_GET['state']) : null;

try {
    if ($package_name && $state) {
        // Get rating for specific package
        $stmt = $conn->prepare("SELECT AVG(rating) as average_rating, COUNT(*) as total_ratings FROM ratings WHERE package_name = ? AND state = ?");
        $stmt->bind_param("ss", $package_name, $state);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'package_name' => $package_name,
            'state' => $state,
            'average_rating' => $data['average_rating'] ? round($data['average_rating'], 1) : 0,
            'total_ratings' => $data['total_ratings'] ?? 0
        ]);
    } elseif ($state) {
        // Get all ratings for a state
        $stmt = $conn->prepare("SELECT package_name, AVG(rating) as average_rating, COUNT(*) as total_ratings FROM ratings WHERE state = ? GROUP BY package_name");
        $stmt->bind_param("s", $state);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ratings = [];
        while ($row = $result->fetch_assoc()) {
            $ratings[] = [
                'package_name' => $row['package_name'],
                'average_rating' => round($row['average_rating'], 1),
                'total_ratings' => $row['total_ratings']
            ];
        }
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'state' => $state,
            'ratings' => $ratings
        ]);
    } else {
        // Get all ratings grouped by state and package
        $query = "SELECT state, package_name, AVG(rating) as average_rating, COUNT(*) as total_ratings FROM ratings GROUP BY state, package_name ORDER BY state, package_name";
        $result = $conn->query($query);
        
        $ratings = [];
        while ($row = $result->fetch_assoc()) {
            $ratings[] = [
                'state' => $row['state'],
                'package_name' => $row['package_name'],
                'average_rating' => round($row['average_rating'], 1),
                'total_ratings' => $row['total_ratings']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'ratings' => $ratings
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>