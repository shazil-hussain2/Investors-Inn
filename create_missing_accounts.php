<?php
require_once 'create_account.php';

$conn = new mysqli('localhost', 'root', '', 'test2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, recreate the accounts table
$sql = file_get_contents('check_accounts.sql');
if (!$conn->multi_query($sql)) {
    die("Error creating accounts table: " . $conn->error);
}

// Wait for all results to be processed
while ($conn->more_results()) {
    $conn->next_result();
}

// Get all users who don't have accounts
$sql = "SELECT r.id, r.email 
        FROM registration r 
        LEFT JOIN accounts a ON r.id = a.user_id 
        WHERE a.account_id IS NULL";

$result = $conn->query($sql);

if ($result === false) {
    die("Error checking users: " . $conn->error);
}

$count = 0;
while ($user = $result->fetch_assoc()) {
    try {
        createUserAccount($user['id']);
        $count++;
        echo "Created account for user ID: " . $user['id'] . " (Email: " . $user['email'] . ")<br>";
    } catch (Exception $e) {
        echo "Error creating account for user ID: " . $user['id'] . " - " . $e->getMessage() . "<br>";
    }
}

echo "<br>Created " . $count . " new accounts.<br>";
echo "<br><a href='Login.html'>Return to Login</a>";

$conn->close();
?> 