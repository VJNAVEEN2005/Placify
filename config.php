<?php
$servername = "localhost";  // Correct MySQL server hostname
$username = "root";
$password = ""; // Your MySQL password
$database = "placify";
$conn = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if ($conn) {
    echo "Connection successful";
} else {
    echo "Connection failed: " . mysqli_connect_error(); // Output the error message
}
?>
