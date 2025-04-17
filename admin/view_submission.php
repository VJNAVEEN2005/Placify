<?php
session_start();
require_once '../config/db.php';

// Only allow admins to view this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Validate submission_id
if (!isset($_GET['submission_id'])) {
    echo "Invalid access.";
    exit;
}

$submission_id = $_GET['submission_id'];

// Fetch submission details
$stmt = $pdo->prepare("
    SELECT s.*, t.test_title, t.test_id, u.name AS student_name
    FROM submissions s
    JOIN tests t ON s.test_id = t.test_id
    JOIN users u ON s.user_id = u.user_id
    WHERE s.submission_id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();

if (!$submission) {
    echo "Submission not found.";
    exit;
}

// Fetch all answers related to this submission
$stmt = $pdo->prepare("
    SELECT q.question_text, o.option_text AS selected_option, a.is_correct
    FROM answers a
    JOIN questions q ON a.question_id = q.question_id
    LEFT JOIN options o ON a.selected_option_id = o.option_id
    WHERE a.submission_id = ?
");
$stmt->execute([$submission_id]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details | Placify</title>
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
            --warning: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .student-info {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 0.3rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .btn-outline {
            background-color: transparent;
            color: white;
            border: 1px solid white;
        }
        
        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .section-header i {
            margin-right: 0.75rem;
            color: var(--primary);
            font-size: 1.25rem;
        }
        
        .section-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-card {
            background-color: var(--light);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }
        
        .score-value {
            color: var(--primary);
        }
        
        .answer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .answer-item {
            background-color: var(--light);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .answer-item:last-child {
            margin-bottom: 0;
        }
        
        .answer-question {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-right: 2rem;
        }
        
        .answer-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .answer-selected {
            flex: 1;
            min-width: 200px;
        }
        
        .answer-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .answer-value {
            padding: 0.5rem 0.75rem;
            background-color: white;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .answer-status {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }
        
        .status-correct {
            background-color: var(--success);
        }
        
        .status-incorrect {
            background-color: var(--error);
        }
        
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        .back-link i {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-outline {
                margin-top: 1rem;
                align-self: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .answer-details {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Submission Details</h1>
                <div class="student-info">
                    Student: <?= htmlspecialchars($submission['student_name']) ?> | 
                    Test: <?= htmlspecialchars($submission['test_title']) ?>
                </div>
            </div>
            <a href="view_results.php?test_id=<?= $submission['test_id'] ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
        </div>
        
        <div class="section">
            <div class="section-header">
                <i class="fas fa-info-circle"></i>
                <h2 class="section-title">Submission Summary</h2>
            </div>
            
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Student</div>
                    <div class="info-value"><?= htmlspecialchars($submission['student_name']) ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Test</div>
                    <div class="info-value"><?= htmlspecialchars($submission['test_title']) ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Score</div>
                    <div class="info-value score-value"><?= $submission['score'] ?></div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <i class="fas fa-clipboard-check"></i>
                <h2 class="section-title">Answers (<?= count($answers) ?>)</h2>
            </div>
            
            <ol class="answer-list">
                <?php foreach ($answers as $index => $answer): ?>
                    <li class="answer-item">
                        <div class="answer-status <?= $answer['is_correct'] ? 'status-correct' : 'status-incorrect' ?>">
                            <?= $answer['is_correct'] ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?>
                        </div>
                        
                        <div class="answer-question"><?= htmlspecialchars($answer['question_text']) ?></div>
                        
                        <div class="answer-details">
                            <div class="answer-selected">
                                <div class="answer-label">Selected Answer</div>
                                <div class="answer-value"><?= htmlspecialchars($answer['selected_option'] ?? 'No answer') ?></div>
                            </div>
                            
                            <div class="answer-result">
                                <div class="answer-label">Result</div>
                                <div class="answer-value" style="color: <?= $answer['is_correct'] ? 'var(--success)' : 'var(--error)' ?>">
                                    <?= $answer['is_correct'] ? 'Correct' : 'Incorrect' ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        
        <div class="footer">
            <a href="view_results.php?test_id=<?= $submission['test_id'] ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
            
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>
    </div>
</body>
</html>