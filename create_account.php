<?php
function generateAccountNumber() {
    return mt_rand(1000000000, 9999999999); // 10-digit account number
}

function createUserAccount($userId) {
    error_log("Starting createUserAccount for user ID: " . $userId);
    
    $conn = new mysqli('localhost', 'root', '', 'test2');
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    try {
        // Start transaction
        error_log("Starting transaction for user ID: " . $userId);
        $conn->begin_transaction();

        // Give initial balance in holdings (as cash)
        error_log("Preparing to insert initial balance");
        $stmt = $conn->prepare("INSERT INTO user_holdings (user_id, item_type, item_symbol, quantity, purchase_price) VALUES (?, 'cash', 'USD', 1000.00, 1.00)");
        if ($stmt === false) {
            error_log("Error preparing holdings statement: " . $conn->error);
            throw new Exception("Error preparing holdings statement: " . $conn->error);
        }
        
        error_log("Binding parameters for holdings");
        if (!$stmt->bind_param("i", $userId)) {
            error_log("Error binding holdings parameters: " . $stmt->error);
            throw new Exception("Error binding holdings parameters: " . $stmt->error);
        }
        
        error_log("Executing holdings insert");
        if (!$stmt->execute()) {
            error_log("Error executing holdings statement: " . $stmt->error);
            throw new Exception("Error executing holdings statement: " . $stmt->error);
        }
        error_log("Successfully inserted initial balance");

        // Record initial deposit transaction
        error_log("Preparing to insert transaction record");
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, item_type, item_symbol, quantity, price_per_unit, total_amount, status) VALUES (?, 'buy', 'cash', 'USD', 1000.00, 1.00, 1000.00, 'completed')");
        if ($stmt === false) {
            error_log("Error preparing transaction statement: " . $conn->error);
            throw new Exception("Error preparing transaction statement: " . $conn->error);
        }
        
        error_log("Binding parameters for transaction");
        if (!$stmt->bind_param("i", $userId)) {
            error_log("Error binding transaction parameters: " . $stmt->error);
            throw new Exception("Error binding transaction parameters: " . $stmt->error);
        }
        
        error_log("Executing transaction insert");
        if (!$stmt->execute()) {
            error_log("Error executing transaction statement: " . $stmt->error);
            throw new Exception("Error executing transaction statement: " . $stmt->error);
        }
        error_log("Successfully inserted transaction record");

        // Commit transaction
        error_log("Committing transaction");
        $conn->commit();
        error_log("Successfully committed transaction");
        return true;

    } catch (Exception $e) {
        // Rollback on error
        error_log("Error occurred, rolling back transaction: " . $e->getMessage());
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
        error_log("Database connection closed");
    }
}
?> 