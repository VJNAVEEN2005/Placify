<?php
session_start();
require_once '../config/db.php';

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
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit;
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Placify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --error: #dc3545;
            --success: #28a745;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .login-container {
            max-width: 80%;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            display: flex;
            flex-direction: row;
        }
        
        .login-banner {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 45%;
            position: relative;
            overflow: hidden;
        }
        
        .login-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.7;
        }
        
        .banner-content {
            position: relative;
            z-index: 1;
        }
        
        .banner-logo {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .banner-logo i {
            margin-right: 0.75rem;
        }
        
        .banner-heading {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .banner-text {
            margin-bottom: 2rem;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .features-list i {
            margin-right: 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .login-form-container {
            padding: 3rem;
            width: 55%;
        }
        
        .login-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 700;
        }
        
        .login-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            width: 90%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 1rem;
            color: #6c757d;
        }
        
        .input-with-icon {
            padding-left: 2.5rem;
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
            width: 100%;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: #dee2e6;
        }
        
        .divider::before {
            margin-right: 1rem;
        }
        
        .divider::after {
            margin-left: 1rem;
        }
        
        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            transition: all 0.15s ease-in-out;
        }
        
        .social-btn:hover {
            background-color: #e9ecef;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6c757d;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .login-card {
                flex-direction: column;
                max-width: 600px;
            }
            
            .login-banner,
            .login-form-container {
                width: 100%;
            }
            
            .login-banner {
                padding: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-form-container {
                padding: 2rem;
            }
            
            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="login-container">
        <div class="login-card">
            <div class="login-banner">
                <div class="banner-content">
                    <div class="banner-logo">
                        <i class="fas fa-check-circle"></i> Placify
                    </div>
                    <h1 class="banner-heading">Welcome back!</h1>
                    <p class="banner-text">Log in to access your account and continue your testing journey.</p>
                    
                    <ul class="features-list">
                        <li><i class="fas fa-check"></i> Create and manage tests</li>
                        <li><i class="fas fa-check"></i> Track performance analytics</li>
                        <li><i class="fas fa-check"></i> Access test results instantly</li>
                        <li><i class="fas fa-check"></i> Collaborate with teams</li>
                    </ul>
                </div>
            </div>
            
            <div class="login-form-container">
                <h2 class="login-title">Sign In</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>
                
                <?php if ($message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control input-with-icon" 
                                placeholder="your@email.com" 
                                required
                                autocomplete="email"
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control input-with-icon" 
                                placeholder="Enter your password" 
                                required
                                autocomplete="current-password"
                            >
                        </div>
                    </div>
                    
                   
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Sign In
                    </button>
                </form>
                
                
                
                <div class="register-link">
                    Don't have an account? <a href="./register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    event.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
</body>
</html>