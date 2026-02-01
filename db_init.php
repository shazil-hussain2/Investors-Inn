<?php
function initializeDatabase() {
    try {
        // First connect without selecting a database
        $conn = new mysqli('localhost', 'root', '');
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS test2";
        if (!$conn->query($sql)) {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        $conn->select_db('test2');

        // Create tables if they don't exist
        $tables = [
            // Registration table
            "CREATE TABLE IF NOT EXISTS registration (
                id INT AUTO_INCREMENT PRIMARY KEY,
                firstName VARCHAR(50) NOT NULL,
                lastName VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                number VARCHAR(15) NOT NULL,
                password VARCHAR(255) NOT NULL,
                gender CHAR(1),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            // Transactions table
            "CREATE TABLE IF NOT EXISTS transactions (
                transaction_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                transaction_type ENUM('buy', 'sell') NOT NULL,
                item_type VARCHAR(50) NOT NULL,
                item_symbol VARCHAR(20) NOT NULL,
                quantity INT NOT NULL,
                price_per_unit DECIMAL(15, 2) NOT NULL,
                total_amount DECIMAL(15, 2) NOT NULL,
                status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES registration(id)
            )",

            // User holdings table
            "CREATE TABLE IF NOT EXISTS user_holdings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                item_type VARCHAR(50) NOT NULL,
                item_symbol VARCHAR(20) NOT NULL,
                quantity INT NOT NULL,
                purchase_price DECIMAL(15, 2) NOT NULL,
                FOREIGN KEY (user_id) REFERENCES registration(id),
                UNIQUE KEY unique_holding (user_id, item_type, item_symbol)
            )",

            // Payment methods table
            "CREATE TABLE IF NOT EXISTS payment_methods (
                payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                method_type ENUM('credit_card', 'debit_card', 'bank_account') NOT NULL,
                card_number VARCHAR(255),
                expiry_date VARCHAR(7),
                card_holder_name VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES registration(id)
            )"
        ];

        // Create each table
        foreach ($tables as $sql) {
            if (!$conn->query($sql)) {
                throw new Exception("Error creating table: " . $conn->error . "\nQuery: " . $sql);
            }
        }

        // Create indexes for better performance
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_user_email ON registration(email)",
            "CREATE INDEX IF NOT EXISTS idx_user_holdings ON user_holdings(user_id, item_type, item_symbol)",
            "CREATE INDEX IF NOT EXISTS idx_transactions_user ON transactions(user_id)"
        ];

        foreach ($indexes as $sql) {
            if (!$conn->query($sql)) {
                throw new Exception("Error creating index: " . $conn->error . "\nQuery: " . $sql);
            }
        }

        return $conn;
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        throw $e;
    }
}
?> 