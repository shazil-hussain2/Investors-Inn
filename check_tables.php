<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
echo "<h2>Existing Tables:</h2>";
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
    echo "- " . $row[0] . "<br>";
}

// Check required tables
$required_tables = ['registration', 'user_holdings', 'transactions'];
$missing_tables = array_diff($required_tables, $tables);

if (!empty($missing_tables)) {
    echo "<h2>Missing Tables:</h2>";
    foreach ($missing_tables as $table) {
        echo "- " . $table . "<br>";
    }
}

// Show structure of existing tables
echo "<h2>Table Structures:</h2>";
foreach ($tables as $table) {
    echo "<h3>$table</h3>";
    $structure = $conn->query("DESCRIBE $table");
    echo "<pre>";
    while ($field = $structure->fetch_assoc()) {
        print_r($field);
    }
    echo "</pre>";
}

// Check for any foreign key constraints
echo "<h2>Foreign Key Constraints:</h2>";
foreach ($tables as $table) {
    $query = "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE 
            TABLE_SCHEMA = 'test2' AND
            TABLE_NAME = '$table' AND
            REFERENCED_TABLE_NAME IS NOT NULL";
            
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        echo "<h3>$table</h3>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    }
}

$conn->close();
?> 