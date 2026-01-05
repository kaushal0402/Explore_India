<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if this is a cart purchase or single package
$is_cart_purchase = isset($data['cart_items']) && is_array($data['cart_items']);

// Validate required customer fields
$required_customer_fields = ['customer_name', 'customer_email', 'customer_phone', 'payment_method'];
foreach ($required_customer_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required field: ' . $field
        ]);
        exit;
    }
}

// Sanitize customer inputs
$customer_name = htmlspecialchars(trim($data['customer_name']));
$customer_email = filter_var(trim($data['customer_email']), FILTER_SANITIZE_EMAIL);
$customer_phone = htmlspecialchars(trim($data['customer_phone']));
$payment_method = htmlspecialchars(trim($data['payment_method']));

// Validate email
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

// Get database connection
$conn = getDBConnection();

try {
    $conn->begin_transaction();
    
    $booking_ids = [];
    $total_amount = 0;
    
    if ($is_cart_purchase) {
        // Process cart items
        foreach ($data['cart_items'] as $item) {
            $state = htmlspecialchars(trim($item['state']));
            $package_name = htmlspecialchars(trim($item['packageName']));
            $package_price = floatval($item['price']);
            
            $stmt = $conn->prepare("INSERT INTO bookings (customer_name, customer_email, customer_phone, state, package_name, package_price, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssssds", $customer_name, $customer_email, $customer_phone, $state, $package_name, $package_price, $payment_method);
            
            if ($stmt->execute()) {
                $booking_ids[] = $stmt->insert_id;
                $total_amount += $package_price;
            } else {
                throw new Exception('Failed to create booking for ' . $package_name);
            }
            $stmt->close();
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'All ' . count($booking_ids) . ' bookings created successfully! Total: ₹' . number_format($total_amount) . '. Booking IDs: ' . implode(', ', $booking_ids),
            'booking_ids' => $booking_ids,
            'total_amount' => $total_amount
        ]);
        
    } else {
        // Process single package (legacy support)
        if (empty($data['state']) || empty($data['package_name']) || empty($data['package_price'])) {
            throw new Exception('Missing package information');
        }
        
        $state = htmlspecialchars(trim($data['state']));
        $package_name = htmlspecialchars(trim($data['package_name']));
        $package_price = floatval($data['package_price']);
        
        $stmt = $conn->prepare("INSERT INTO bookings (customer_name, customer_email, customer_phone, state, package_name, package_price, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssssds", $customer_name, $customer_email, $customer_phone, $state, $package_name, $package_price, $payment_method);
        
        if ($stmt->execute()) {
            $booking_id = $stmt->insert_id;
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Booking created successfully! Your booking ID is: ' . $booking_id,
                'booking_id' => $booking_id
            ]);
        } else {
            throw new Exception('Failed to create booking: ' . $stmt->error);
        }
        $stmt->close();
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>