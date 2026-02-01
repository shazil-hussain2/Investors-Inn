<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if transactions table exists and its structure
$result = $conn->query("SHOW CREATE TABLE transactions");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "Error checking transactions table: " . $conn->error;
}

$conn->close();
?> 