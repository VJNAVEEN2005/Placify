<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "placify";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $registration_no = mysqli_real_escape_string($conn, $_POST['registration_no']);
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);

    // Check if registration number already exists
    $check_query = "SELECT * FROM students WHERE registration_no = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $registration_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Registration number already exists!";
        header("Location: dashboard/dashboard.php");
        exit();
    }

    // Insert new student
    $insert_query = "INSERT INTO students (registration_no, student_name, year) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $registration_no, $student_name, $year);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Student added successfully!";
    } else {
        $_SESSION['error'] = "Error adding student: " . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    mysqli_close($conn);

    // Redirect back to dashboard
    header("Location: dashboard/dashboard.php");
    exit();
}
?> 