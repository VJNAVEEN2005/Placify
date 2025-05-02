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


    $stmt = $pdo->prepare('DELETE FROM `tests` WHERE `tests`.`test_id` = ?');
    $stmt->execute([$test_id]);
    header('Location: dashboard.php');

// Fetch test details
