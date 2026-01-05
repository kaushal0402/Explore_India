<?php
require_once 'razorpay_config.php';
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount'])) {
    echo json_encode(['error' => 'Amount is required']);
    exit;
}

$amount = $data['amount']; // Amount in Rupees

$api_key = RAZORPAY_KEY_ID;
$api_secret = RAZORPAY_KEY_SECRET;

// Create order data
$orderData = [
    'receipt'         => 'rcptid_' . time(),
    'amount'          => $amount * 100, // Amount in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // Auto capture
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_USERPWD, $api_key . ':' . $api_secret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
} else {
    // Return the response from Razorpay (contains id, amount, etc.)
    echo $result;
}

curl_close($ch);
?>