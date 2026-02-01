<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
echo "<h2>Testing Database Connection</h2>";
$conn = new mysqli('localhost', 'root', '', 'test2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connection successful<br>";

// Check required tables
echo "<h2>Checking Required Tables</h2>";
$required_tables = ['registration', 'user_holdings', 'transactions'];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists<br>";
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        echo "<pre>";
        while ($field = $structure->fetch_assoc()) {
            print_r($field);
        }
        echo "</pre>";
    } else {
        echo "✗ Table '$table' is missing!<br>";
    }
}

// Test user creation function
echo "<h2>Testing User Creation</h2>";
require_once 'create_account.php';

try {
    // Insert test user into registration
    $stmt = $conn->prepare("INSERT INTO registration (firstName, lastName, email, number, password, gender) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $firstName = "Test";
    $lastName = "User";
    $email = "test" . time() . "@test.com";
    $number = "1234567890";
    $password = "test123";
    $gender = "Other";
    
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $number, $password, $gender);
    if (!$stmt->execute()) {
        throw new Exception("Error inserting test user: " . $stmt->error);
    }
    
    $userId = $stmt->insert_id;
    echo "Test user created with ID: $userId<br>";
    
    // Test account creation
    echo "Testing createUserAccount function...<br>";
    createUserAccount($userId);
    echo "Account created successfully!<br>";
    
    // Verify holdings
    $result = $conn->query("SELECT * FROM user_holdings WHERE user_id = $userId");
    echo "<h3>User Holdings:</h3><pre>";
    print_r($result->fetch_assoc());
    echo "</pre>";
    
    // Verify transaction
    $result = $conn->query("SELECT * FROM transactions WHERE user_id = $userId");
    echo "<h3>Transaction Record:</h3><pre>";
    print_r($result->fetch_assoc());
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<pre>";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

$conn->close();
?> 