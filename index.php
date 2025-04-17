<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placify - Modern Online Testing Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: white;
            color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--light);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background-color: rgba(255,255,255,0.15);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background-color: rgba(255,255,255,0.25);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .welcome-back {
            background-color: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .welcome-back h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .dashboard-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .dashboard-item {
            background-color: var(--light);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .dashboard-item:hover {
            background-color: var(--primary);
            color: white;
            transform: scale(1.05);
        }
        
        .dashboard-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logged in view -->
    <div class="hero">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Let's continue making learning interactive and effective with Placify.</p>
    </div>
    
    <div class="welcome-back">
        <h2>Your Testing Dashboard</h2>
        <p>Pick up where you left off or explore new features.</p>
        
        <div class="dashboard-preview">
            <a href="dashboard/tests.php" class="dashboard-item">
                <div class="dashboard-icon"><i class="fas fa-clipboard-list"></i></div>
                <h3>My Tests</h3>
                <p>Manage your created tests</p>
            </a>
            <a href="dashboard/results.php" class="dashboard-item">
                <div class="dashboard-icon"><i class="fas fa-chart-bar"></i></div>
                <h3>Results</h3>
                <p>View test performance</p>
            </a>
            <a href="dashboard/create.php" class="dashboard-item">
                <div class="dashboard-icon"><i class="fas fa-plus-circle"></i></div>
                <h3>Create New</h3>
                <p>Design a new test</p>
            </a>
            <a href="dashboard/settings.php" class="dashboard-item">
                <div class="dashboard-icon"><i class="fas fa-cog"></i></div>
                <h3>Settings</h3>
                <p>Update your preferences</p>
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- Guest view -->
    <div class="hero">
        <h1>Revolutionize Your Testing Experience</h1>
        <p>Create engaging assessments, track performance, and enhance learning outcomes with Placify's powerful online testing platform.</p>
        
        <div class="cta-buttons">
            <a href="./auth/login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="./auth/register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </div>
    </div>
    
    <div class="features">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-pencil-alt"></i></div>
            <h3 class="feature-title">Create Custom Tests</h3>
            <p>Design professional tests with multiple question types including multiple choice, true/false, short answer, and more.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
            <h3 class="feature-title">Analytics & Insights</h3>
            <p>Get detailed performance metrics and actionable insights to improve testing strategies and learning outcomes.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-clock"></i></div>
            <h3 class="feature-title">Time-Saving Tools</h3>
            <p>Automate grading, schedule tests, and manage results - all from a single, intuitive dashboard.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
            <h3 class="feature-title">Secure Testing</h3>
            <p>Advanced security features protect test integrity with randomized questions, time limits, and anti-cheating measures.</p>
        </div>
    </div>
<?php endif; ?>

<footer>
    <p>&copy; <?= date('Y'); ?> Placify - All rights reserved.</p>
    <p>
        <a href="about.php" style="color: var(--accent); margin: 0 10px;">About</a>
        <a href="contact.php" style="color: var(--accent); margin: 0 10px;">Contact</a>
        <a href="privacy.php" style="color: var(--accent); margin: 0 10px;">Privacy Policy</a>
        <a href="terms.php" style="color: var(--accent); margin: 0 10px;">Terms of Service</a>
    </p>
</footer>

<script>
    // Add any JavaScript functionality here
    document.addEventListener('DOMContentLoaded', function() {
        // Animation for feature cards
        const cards = document.querySelectorAll('.feature-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 100}ms`;
            card.classList.add('animate-in');
        });
    });
</script>

</body>
</html>