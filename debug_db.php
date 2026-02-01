<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Tables:</h2>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        echo "<h3>Table: $table</h3>";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        echo "<pre>";
        while ($field = $structure->fetch_assoc()) {
            print_r($field);
        }
        echo "</pre>";
        
        // Show table contents
        $contents = $conn->query("SELECT * FROM $table");
        if ($contents && $contents->num_rows > 0) {
            echo "<p>Contents:</p><pre>";
            while ($row = $contents->fetch_assoc()) {
                print_r($row);
            }
            echo "</pre>";
        } else {
            echo "<p>Table is empty</p>";
        }
        echo "<hr>";
    }
} else {
    echo "Error getting tables: " . $conn->error;
}

$conn->close();
?> 