<?php
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = file_get_contents('temp_setup.sql');

if ($conn->multi_query($sql)) {
    echo "Database and tables created successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?> 