<?php
session_start();

// Get user input
$email = $_POST['email'];
$password = $_POST['password'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection Failed : " . $conn->connect_error);
}

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM registration WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Login successful
    $_SESSION['email'] = $email;
    header("Location: index.html");
    exit();
} else {
    // Login failed
    header("Location: login.html?error=1");
    exit();
}

$stmt->close();
$conn->close();
?> 