<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Get form data
$user_email = $_SESSION['email'];
$item_type = $_POST['item_type'];
$item_symbol = $_POST['item_symbol'];
$quantity = (float) $_POST['quantity'];
$price    = (float) $_POST['price'];
$total    = (float) $_POST['total'];
$bank_name = $_POST['bank_name'];
$account_holder_name = $_POST['account_holder_name'];
$account_number = $_POST['account_number'];
$ifsc_code = $_POST['ifsc_code'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection Failed : " . $conn->connect_error);
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if user has enough quantity to sell
    $stmt = $conn->prepare("SELECT id, quantity FROM user_holdings WHERE user_email = ? AND item_type = ? AND item_symbol = ?");
    $stmt->bind_param("sss", $user_email, $item_type, $item_symbol);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("No holdings found");
    }
    
    $row = $result->fetch_assoc();
    if ($row['quantity'] < $quantity) {
        throw new Exception("Insufficient quantity");
    }

    // Insert into transactions table
    $stmt = $conn->prepare("INSERT INTO transactions (user_email, transaction_type, item_type, item_symbol, quantity, price_per_unit, total_amount, status) VALUES (?, 'sell', ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssddd", $user_email, $item_type, $item_symbol, $quantity, $price, $total);
    $stmt->execute();
    $transaction_id = $stmt->insert_id;

    // Insert into bank_details table
    $stmt = $conn->prepare("INSERT INTO bank_details (transaction_id, bank_name, account_number, ifsc_code, account_holder_name, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issss", $transaction_id, $bank_name, $account_number, $ifsc_code, $account_holder_name);
    $stmt->execute();

    // Update user_holdings
    $new_quantity = $row['quantity'] - $quantity;
    if ($new_quantity == 0) {
        // Delete the holding if quantity becomes zero
        $stmt = $conn->prepare("DELETE FROM user_holdings WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
    } else {
        // Update the holding quantity
        $stmt = $conn->prepare("UPDATE user_holdings SET quantity = ? WHERE id = ?");
        $stmt->bind_param("di", $new_quantity, $row['id']);
    }
    $stmt->execute();

    // Update transaction and bank transfer status to completed
    $stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE bank_details SET status = 'completed' WHERE transaction_id = ?");
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Redirect to success page with transfer message
    header("Location: transfer_success.html");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: transfer_failed.html");
    exit();
}

$conn->close();
?> 