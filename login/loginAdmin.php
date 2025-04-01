<?php
session_start();

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

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST['admin-username']);
    $password = trim($_POST['admin-password']);


$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $password_data = $row['password'];

    if ($password == $password_data) {
        // Password is correct, create session variables
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_username'] = $row['username'];

        // Redirect to the admin dashboard
        echo '<script>alert("Login successful!");</script>';
        echo 'username: ' . $username . '<br>';
        echo 'password: ' . $password . '<br>';

        $_SESSION["username"] = $username;
        $_SESSION["password"] = $password;
        

        header("Location: ../dashboard/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
} else {
    $error = "Invalid username or password!";
}
$stmt->close();
$conn->close();
}
?>