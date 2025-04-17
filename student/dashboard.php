<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Fetch all available tests the student hasn't taken yet
$stmt = $pdo->prepare("SELECT * FROM tests WHERE test_id NOT IN (SELECT test_id FROM submissions WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed submissions
$stmt = $pdo->prepare("
    SELECT s.submission_id, s.score, t.test_title, s.total_marks
    FROM submissions s
    JOIN tests t ON s.test_id = t.test_id
    WHERE s.user_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Placify</title>
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
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.7;
        }
        
        .welcome-heading {
            font-size: 2.2rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-section {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-heading {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
        }
        
        .section-heading i {
            margin-right: 0.75rem;
            color: var(--primary);
        }
        
        .test-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .test-item {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        
        .test-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            border-color: #dee2e6;
        }
        
        .test-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .test-link {
            display: inline-block;
            font-weight: 500;
            color: white;
            text-align: center;
            background-color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            margin-top: 0.5rem;
        }
        
        .test-link:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .results-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .result-item {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .result-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            border-color: #dee2e6;
        }
        
        .result-info {
            flex: 1;
        }
        
        .result-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .result-score {
            font-size: 0.95rem;
            color: #6c757d;
        }
        
        .score-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
            min-width: 60px;
        }
        
        .score-good {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .score-average {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .score-low {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }
        
        .view-result {
            display: inline-block;
            font-weight: 500;
            color: var(--primary);
            text-align: center;
            background-color: transparent;
            border: 1px solid var(--primary);
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            margin-left: 1rem;
        }
        
        .view-result:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .logout-link {
            display: inline-block;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.15s ease;
        }
        
        .logout-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
            }
            
            .result-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .result-actions {
                margin-top: 1rem;
                display: flex;
                width: 100%;
                justify-content: space-between;
                align-items: center;
            }
            
            .view-result {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-header">
            <h2 class="welcome-heading">Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>
            
        </div>
        <p><a href="../auth/logout.php" class="logout-link">Logout</a></p>
        
        <div class="dashboard-section">
            <h3 class="section-heading">
                <i class="fas fa-clipboard-list"></i> Available Tests
            </h3>
            
            <?php if (count($tests) > 0): ?>
                <ul class="test-list">
                    <?php foreach ($tests as $test): ?>
                        <li class="test-item">
                            <div class="test-title"><?= htmlspecialchars($test['test_title']); ?></div>
                            <a href="take_test.php?test_id=<?= $test['test_id']; ?>" class="test-link">
                                <i class="fas fa-play-circle"></i> Start Test
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-message">
                    <p>You have already completed all the available tests.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-section">
            <h3 class="section-heading">
                <i class="fas fa-chart-bar"></i> Your Test Results
            </h3>
            
            <?php if (count($submissions) > 0): ?>
                <ul class="results-list">
                    <?php foreach ($submissions as $submission): 
                        $scoreClass = '';
                        if ($submission['score'] / $submission['total_marks'] * 100 >= 80) {
                            $scoreClass = 'score-good';
                        } elseif ($submission['score'] / $submission['total_marks'] * 100 >= 60) {
                            $scoreClass = 'score-average';
                        } else {
                            $scoreClass = 'score-low';
                        }
                    ?>
                        <li class="result-item">
                            <div class="result-info">
                                <div class="result-title"><?= htmlspecialchars($submission['test_title']); ?></div>
                                <div class="result-score">Score: <?= $submission['score'] / $submission['total_marks'] * 100; ?>%</div>
                            </div>
                            <div class="result-actions">
                                <span class="score-badge <?= $scoreClass ?>"><?= $submission['score'] / $submission['total_marks'] * 100; ?>%</span>
                                <a href="result.php?submission_id=<?= $submission['submission_id']; ?>" class="view-result">
                                    View Result
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-message">
                    <p>No test results found yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        
    </div>
</body>
</html>