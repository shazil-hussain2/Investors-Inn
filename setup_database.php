<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Connection Failed : " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS test2";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}
echo "Database created or already exists.\n";

// Select the database
$conn->select_db('test2');
echo "Database selected.\n";

// Drop existing tables in reverse order of dependencies
$conn->query("DROP TABLE IF EXISTS bank_details");
$conn->query("DROP TABLE IF EXISTS payment_details");
$conn->query("DROP TABLE IF EXISTS user_holdings");
$conn->query("DROP TABLE IF EXISTS transactions");
$conn->query("DROP TABLE IF EXISTS registration");
echo "Existing tables dropped.\n";

// Create registration table first (if it doesn't exist)
$sql = "CREATE TABLE IF NOT EXISTS registration (
    email VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255),
    password VARCHAR(255),
    gender VARCHAR(10)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating registration table: " . $conn->error);
}
echo "Registration table created.\n";

// Create user_holdings table
$sql = "CREATE TABLE IF NOT EXISTS user_holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    item_type ENUM('stock', 'forex', 'index'),
    item_symbol VARCHAR(20),
    quantity DECIMAL(10,2),
    purchase_price DECIMAL(10,2),
    FOREIGN KEY (user_email) REFERENCES registration(email) ON DELETE CASCADE ON UPDATE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating user_holdings table: " . $conn->error);
}
echo "User holdings table created.\n";

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    transaction_type ENUM('buy', 'sell'),
    item_type ENUM('stock', 'forex', 'index'),
    item_symbol VARCHAR(20),
    quantity DECIMAL(10,2),
    price_per_unit DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (user_email) REFERENCES registration(email)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating transactions table: " . $conn->error);
}
echo "Transactions table created.\n";

// Create payment_details table
$sql = "CREATE TABLE IF NOT EXISTS payment_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer'),
    card_number VARCHAR(255),
    expiry_date VARCHAR(7),
    cvv VARCHAR(255),
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating payment_details table: " . $conn->error);
}
echo "Payment details table created.\n";

// Create bank_details table
$sql = "CREATE TABLE IF NOT EXISTS bank_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    bank_name VARCHAR(255),
    account_number VARCHAR(255),
    ifsc_code VARCHAR(20),
    account_holder_name VARCHAR(255),
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating bank_details table: " . $conn->error);
}
echo "Bank details table created.\n";

echo "All database tables created successfully!\n";
$conn->close();
?> 