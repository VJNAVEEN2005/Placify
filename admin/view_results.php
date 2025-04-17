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

// Fetch test details
$stmt = $pdo->prepare("SELECT * FROM tests WHERE test_id = ?");
$stmt->execute([$test_id]);
$test = $stmt->fetch();

if (!$test) {
    echo "Test not found.";
    exit;
}

// Fetch all submissions for the test with student names
$stmt = $pdo->prepare("
    SELECT s.submission_id, s.score, u.name AS student_name
    FROM submissions s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.test_id = ?
");
$stmt->execute([$test_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results | Placify</title>
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
        
        .test-name {
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
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .results-section {
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
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .results-table th,
        .results-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .results-table th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
        }
        
        .results-table tr:hover {
            background-color: rgba(245, 247, 251, 0.5);
        }
        
        .results-table td:last-child {
            text-align: right;
        }
        
        .view-link {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: var(--primary);
            color: white;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        
        .view-link:hover {
            background-color: var(--secondary);
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
        }
        
        .view-link i {
            margin-right: 0.4rem;
        }
        
        .score-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--accent);
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
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-icon {
            font-size: 2.5rem;
            color: #ced4da;
            margin-bottom: 1rem;
        }
        
        .empty-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 1rem;
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
            }
            
            .results-table th,
            .results-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Test Results</h1>
                <div class="test-name">Test: <?= htmlspecialchars($test['test_title']) ?></div>
            </div>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="results-section">
            <div class="section-header">
                <i class="fas fa-chart-bar"></i>
                <h2 class="section-title">Student Submissions (<?= count($submissions) ?>)</h2>
            </div>
            
            <?php if (count($submissions) > 0): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?= htmlspecialchars($submission['student_name']) ?></td>
                                <td><span class="score-badge"><?= $submission['score'] ?></span></td>
                                <td>
                                    <a href="view_submission.php?submission_id=<?= $submission['submission_id'] ?>" class="view-link">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <p class="empty-message">No submissions yet for this test.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>