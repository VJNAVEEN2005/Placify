<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect based on role (for pages that require a specific role)
if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    header('Location: login.php');
    exit;
}
?>
