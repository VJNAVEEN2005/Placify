<?php
session_start();
require_once './config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user info in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        // Redirect to respective dashboard
        if ($user['role'] === 'admin') {
            header("Location: ./admin/dashboard.php");
        } else {
            header("Location: ./student/dashboard.php");
        }
        exit;
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Placify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h2>Login</h2>
    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="./auth/register.php">Register here</a></p>
</body>
</html>
