<?php
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$email = $_POST['email'];
	$number = $_POST['number'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];

	// Database connection
	$conn = new mysqli('localhost','root','','test2');
	if($conn->connect_error){
		echo "$conn->connect_error";
		die("Connection Failed : ". $conn->connect_error);
	} else {
		$stmt = $conn->prepare("insert into registration(firstName, lastName, email, number, password, gender) values(?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssiss", $firstName, $lastName, $email, $number, $password, $gender);
		$execval = $stmt->execute();
		
		if($execval) {
			// Registration successful - redirect to login page
			header("Location: login.html");
			exit();
		} else {
			// Registration failed
			echo "Registration failed. Please try again.";
		}
		
		$stmt->close();
		$conn->close();
	}
?>