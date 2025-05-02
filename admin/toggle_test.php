<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Check if test_id is provided
if (!isset($_GET['test_id'])) {
    header('Location: dashboard.php');
    exit;
}

$test_id = $_GET['test_id'];
$action = $_GET['action'];

if ($action == 'deactivate') {
    $stmt = $pdo->prepare('UPDATE tests SET is_active = 0 WHERE test_id = ?');
    $stmt->execute([$test_id]);
    header('Location: dashboard.php');
} else if ($action == 'activate') {
    echo 'activate';
    $stmt = $pdo->prepare('UPDATE tests SET is_active = 1 WHERE test_id = ?');
    $stmt->execute([$test_id]);
    header('Location: dashboard.php');
}

// Fetch test details
