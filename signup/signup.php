<?php
session_start();
include '../config.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstname = trim($_POST['admin-firstname']);
    $lastname = trim($_POST['admin-lastname']);
    $email = trim($_POST['admin-email']);
    $username = trim($_POST['admin-username']);
    $password = trim($_POST['admin-password']);
    $confirm_password = trim($_POST['admin-confirm-password']);
    $institution = trim($_POST['admin-institution']);
    $position = trim($_POST['admin-position']);
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Check if email or username already exists
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email or Username already exists!'); window.history.back();</script>";
        exit();
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO admins (firstname, lastname, email, username, password, institution, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstname, $lastname, $email, $username, $hashed_password, $institution, $position);
    
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Registration failed! Please try again.'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method!'); window.history.back();</script>";
}
?>