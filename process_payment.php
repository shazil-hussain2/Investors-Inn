<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get form data
$user_email = $_SESSION['email'];
$item_type = $_POST['item_type'];
$item_symbol = $_POST['item_symbol'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$total = $_POST['total'];
$payment_method = $_POST['payment_method'];
$card_number = $_POST['card_number'];
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection Failed : " . $conn->connect_error);
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert into transactions table
    $sql = "INSERT INTO transactions (user_email, transaction_type, item_type, item_symbol, quantity, price_per_unit, total_amount, status) VALUES (?, 'buy', ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
    }
    
    $stmt->bind_param("sssddd", $user_email, $item_type, $item_symbol, $quantity, $price, $total);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $transaction_id = $stmt->insert_id;

    // Insert into payment_details table
    $sql = "INSERT INTO payment_details (transaction_id, payment_method, card_number, expiry_date, cvv, status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
    }
    
    $stmt->bind_param("issss", $transaction_id, $payment_method, $card_number, $expiry_date, $cvv);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Check user holdings
    $sql = "SELECT id, quantity FROM user_holdings WHERE user_email = ? AND item_type = ? AND item_symbol = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
    }
    
    $stmt->bind_param("sss", $user_email, $item_type, $item_symbol);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing holding
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        
        $sql = "UPDATE user_holdings SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
        }
        
        $stmt->bind_param("di", $new_quantity, $row['id']);
    } else {
        // Insert new holding
        $sql = "INSERT INTO user_holdings (user_email, item_type, item_symbol, quantity, purchase_price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
        }
        
        $stmt->bind_param("sssdd", $user_email, $item_type, $item_symbol, $quantity, $price);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Update transaction status
    $sql = "UPDATE transactions SET status = 'completed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
    }
    
    $stmt->bind_param("i", $transaction_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Update payment status
    $sql = "UPDATE payment_details SET status = 'completed' WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error . " for query: " . $sql);
    }
    
    $stmt->bind_param("i", $transaction_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Redirect to success page
    header("Location: transaction_success.html");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Payment processing error: " . $e->getMessage());
    
    // For debugging, display the error
    echo "Error: " . $e->getMessage();
    exit();
}

$conn->close();
?> 