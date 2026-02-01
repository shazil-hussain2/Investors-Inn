<?php
session_start();

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Get parameters
$user_email = $_SESSION['email'];
$item_type = $_GET['type'];
$item_symbol = $_GET['symbol'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get user holdings
$stmt = $conn->prepare("SELECT quantity FROM user_holdings WHERE user_email = ? AND item_type = ? AND item_symbol = ?");
$stmt->bind_param("sss", $user_email, $item_type, $item_symbol);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['quantity' => $row['quantity']]);
} else {
    echo json_encode(['quantity' => 0]);
}

$conn->close();
?> 