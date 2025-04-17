<?php
require_once '../config/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role']; // admin or student

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $message = "Email already exists!";
        $messageType = "error";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $password, $role])) {
            $message = "Registration successful! <a href='./login.php'>Login here</a>.";
            $messageType = "success";
        } else {
            $message = "Something went wrong. Please try again.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Placify</title>
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
        
        .register-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .register-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            display: flex;
            flex-direction: row;
        }
        
        .register-banner {
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
        
        .register-banner::before {
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
        
        .benefits-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .benefits-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .benefits-list i {
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
        
        .register-form-container {
            padding: 3rem;
            width: 55%;
        }
        
        .register-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 700;
        }
        
        .register-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
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
        
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-col {
            flex: 1;
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
        
        .password-toggle {
            position: absolute;
            top: 35%;
            transform: translateY(-50%);
            right: 1rem;
            color: #6c757d;
            cursor: pointer;
            background: none;
            border: none;
            width: 34px;
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
        
        .social-register {
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
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6c757d;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 2.5px;
            background-color: #dee2e6;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
        
        .strength-weak {
            background-color: var(--error);
            width: 25%;
        }
        
        .strength-medium {
            background-color: #ffc107;
            width: 50%;
        }
        
        .strength-good {
            background-color: #17a2b8;
            width: 75%;
        }
        
        .strength-strong {
            background-color: var(--success);
            width: 100%;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .register-card {
                flex-direction: column;
                max-width: 600px;
            }
            
            .register-banner,
            .register-form-container {
                width: 100%;
            }
            
            .register-banner {
                padding: 2rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        @media (max-width: 576px) {
            .register-form-container {
                padding: 2rem;
            }
            
            .social-register {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="register-container">
        <div class="register-card">
            <div class="register-banner">
                <div class="banner-content">
                    <div class="banner-logo">
                        <i class="fas fa-check-circle"></i> Placify
                    </div>
                    <h1 class="banner-heading">Join our testing community</h1>
                    <p class="banner-text">Create an account to start your testing journey with Placify.</p>
                    
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> Create unlimited tests</li>
                        <li><i class="fas fa-check"></i> Access detailed performance reports</li>
                        <li><i class="fas fa-check"></i> Collaborate with colleagues</li>
                        <li><i class="fas fa-check"></i> Secure and reliable platform</li>
                    </ul>
                </div>
            </div>
            
            <div class="register-form-container">
                <h2 class="register-title">Create an Account</h2>
                <p class="register-subtitle">Fill in your details to get started</p>
                
                <?php if ($message): ?>
                    <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>">
                        <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control input-with-icon" 
                                placeholder="Enter your full name" 
                                required
                                autocomplete="name"
                            >
                        </div>
                    </div>
                    
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
                                placeholder="Create a strong password" 
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <small class="form-text" id="passwordFeedback">Password should be at least 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">I am a</label>
                        <div class="input-group">
                            <i class="fas fa-user-tag input-icon"></i>
                            <select 
                                id="role" 
                                name="role" 
                                class="form-control input-with-icon" 
                                required
                            >
                                <option value="" disabled selected>Select your role</option>
                                <option value="student">Student</option>
                                <option value="admin">Teacher/Admin</option>
                            </select>
                        </div>
                        <small class="form-text">
                            <i class="fas fa-info-circle"></i> Students can take tests, while Teachers/Admins can create and manage tests
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Create Account
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="./login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Password strength meter
            const strengthBar = document.getElementById('strengthBar');
            const passwordFeedback = document.getElementById('passwordFeedback');
            
            password.addEventListener('input', function() {
                const value = this.value;
                let strength = 0;
                
                // Length check
                if (value.length >= 8) {
                    strength += 1;
                }
                
                // Contains uppercase letter
                if (/[A-Z]/.test(value)) {
                    strength += 1;
                }
                
                // Contains lowercase letter
                if (/[a-z]/.test(value)) {
                    strength += 1;
                }
                
                // Contains number
                if (/[0-9]/.test(value)) {
                    strength += 1;
                }
                
                // Contains special character
                if (/[^A-Za-z0-9]/.test(value)) {
                    strength += 1;
                }
                
                // Update strength bar and feedback
                strengthBar.className = 'password-strength-bar';
                
                if (value.length === 0) {
                    strengthBar.style.width = '0';
                    passwordFeedback.textContent = 'Password should be at least 8 characters';
                } else if (strength < 2) {
                    strengthBar.classList.add('strength-weak');
                    passwordFeedback.textContent = 'Weak password';
                } else if (strength < 3) {
                    strengthBar.classList.add('strength-medium');
                    passwordFeedback.textContent = 'Medium strength password';
                } else if (strength < 5) {
                    strengthBar.classList.add('strength-good');
                    passwordFeedback.textContent = 'Good password';
                } else {
                    strengthBar.classList.add('strength-strong');
                    passwordFeedback.textContent = 'Strong password';
                }
            });
            
            // Form validation
            const registerForm = document.getElementById('registerForm');
            registerForm.addEventListener('submit', function(event) {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const passwordValue = password.value;
                const role = document.getElementById('role').value;
                const terms = document.getElementById('terms').checked;
                
                let isValid = true;
                
                if (name.length < 2) {
                    isValid = false;
                    alert('Please enter a valid name');
                } else if (!validateEmail(email)) {
                    isValid = false;
                    alert('Please enter a valid email address');
                } else if (passwordValue.length < 8) {
                    isValid = false;
                    alert('Password must be at least 8 characters long');
                } else if (!role) {
                    isValid = false;
                    alert('Please select a role');
                } else if (!terms) {
                    isValid = false;
                    alert('You must agree to the Terms of Service and Privacy Policy');
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
            
            function validateEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }
        });
    </script>
</body>
</html>