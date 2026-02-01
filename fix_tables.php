<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Drop all existing tables
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $table = $row[0];
    $conn->query("DROP TABLE IF EXISTS $table");
    echo "Dropped table $table<br>";
}

// Create registration table
$sql = "CREATE TABLE registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    number VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender CHAR(1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Created registration table<br>";
} else {
    die("Error creating registration table: " . $conn->error);
}

// Create transactions table
$sql = "CREATE TABLE transactions (
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
)";

if ($conn->query($sql)) {
    echo "Created transactions table<br>";
} else {
    die("Error creating transactions table: " . $conn->error);
}

// Create user_holdings table
$sql = "CREATE TABLE user_holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type VARCHAR(50) NOT NULL,
    item_symbol VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES registration(id),
    UNIQUE KEY unique_holding (user_id, item_type, item_symbol)
)";

if ($conn->query($sql)) {
    echo "Created user_holdings table<br>";
} else {
    die("Error creating user_holdings table: " . $conn->error);
}

// Create payment_methods table
$sql = "CREATE TABLE payment_methods (
    payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    method_type ENUM('credit_card', 'debit_card', 'bank_account') NOT NULL,
    card_number VARCHAR(255),
    expiry_date VARCHAR(7),
    card_holder_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES registration(id)
)";

if ($conn->query($sql)) {
    echo "Created payment_methods table<br>";
} else {
    die("Error creating payment_methods table: " . $conn->error);
}

// Create indexes
$conn->query("CREATE INDEX idx_user_email ON registration(email)");
$conn->query("CREATE INDEX idx_user_holdings ON user_holdings(user_id, item_type, item_symbol)");
$conn->query("CREATE INDEX idx_transactions_user ON transactions(user_id)");

// Insert a test user
$sql = "INSERT INTO registration (firstName, lastName, email, number, password, gender) 
        VALUES ('Test', 'User', 'test@example.com', '1234567890', 'password123', 'M')";
if ($conn->query($sql)) {
    echo "Created test user<br>";
} else {
    echo "Error creating test user: " . $conn->error . "<br>";
}

echo "Database schema updated successfully!<br>";
echo "<a href='index.html'>Return to Home</a>";

$conn->close();
?> 