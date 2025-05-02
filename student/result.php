<?php
session_start();
require_once '../config/db.php';

// Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['submission_id'])) {
    echo "No submission selected.";
    exit;
}

$submission_id = $_GET['submission_id'];

// Fetch the submission details
$stmt = $pdo->prepare("
    SELECT s.*, t.test_title 
    FROM submissions s
    JOIN tests t ON s.test_id = t.test_id
    WHERE s.submission_id = ? AND s.user_id = ?
");
$stmt->execute([$submission_id, $_SESSION['user_id']]);
$submission = $stmt->fetch();

if (!$submission) {
    echo "Submission not found or access denied.";
    exit;
}

// Fetch answers to display in detail - modified to include question images
$stmt = $pdo->prepare("
    SELECT a.*, q.question_text, q.question_id, q.image_data, q.image_type, o.option_text 
    FROM answers a
    JOIN questions q ON a.question_id = q.question_id
    LEFT JOIN options o ON a.selected_option_id = o.option_id
    WHERE a.submission_id = ?
");
$stmt->execute([$submission_id]);
$answers = $stmt->fetchAll();

// Calculate the percentage score
if ($submission['total_marks'] > 0) {
    $percentage = ($submission['score'] / $submission['total_marks']) * 100;
} else {
    $percentage = 0;
}

$scoreClass = '';
if ($percentage >= 80) {
    $scoreClass = 'score-good';
} elseif ($percentage >= 60) {
    $scoreClass = 'score-average';
} else {
    $scoreClass = 'score-low';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($submission['test_title']); ?> - Result | Placify</title>
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
        
        .result-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .result-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.7;
        }
        
        .result-heading {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .result-summary {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .result-score {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            min-width: 100px;
        }
        
        .score-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .score-label {
            font-size: 0.9rem;
            opacity: 0.8;
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
        
        .answer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .answer-table th, 
        .answer-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .answer-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }
        
        .answer-table tr:last-child td {
            border-bottom: none;
        }
        
        .answer-table tr:hover td {
            background-color: #f8f9fa;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.15s ease;
            margin-top: 1rem;
        }
        
        .back-link i {
            margin-right: 0.5rem;
        }
        
        .back-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .answer-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-correct {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .status-incorrect {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error);
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
        
        /* New styles for question images */
        .question-image {
            max-width: 100px;
            max-height: 60px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease;
        }
        
        .question-image:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Modal styles for image preview */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
            }
            
            .result-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .result-score {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
            }
            
            .answer-table {
                display: block;
                overflow-x: auto;
            }
            
            .question-image {
                max-width: 80px;
                max-height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="result-header">
            <h2 class="result-heading"><?= htmlspecialchars($submission['test_title']); ?> - Result</h2>
            <div class="result-summary">
                <div class="result-score">
                    <div class="score-value"><?= number_format($percentage, 0); ?>%</div>
                    <div class="score-label">Score</div>
                </div>
                <div>
                    <p>Total Points: <?= $submission['score']; ?> / <?= $submission['total_marks']; ?></p>
                    <p>Completed: <?= date('F j, Y, g:i a', strtotime($submission['submitted_at'])); ?></p>
                </div>
            </div>
        </div>

        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="dashboard-section">
            <h3 class="section-heading">
                <i class="fas fa-list-check"></i> Answer Details
            </h3>
            
            <table class="answer-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Image</th>
                        <th>Your Answer</th>
                        <th>Status</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $questionNumber = 1; ?>
                    <?php foreach ($answers as $answer): ?>
                        <tr>
                            <td><?= $questionNumber++; ?></td>
                            <td><?= htmlspecialchars($answer['question_text']); ?></td>
                            <td>
                                <?php if (!empty($answer['image_data'])): ?>
                                    <?php 
                                    $imageData = base64_encode($answer['image_data']);
                                    $imageType = $answer['image_type'];
                                    $imageId = "question-" . $answer['question_id'];
                                    ?>
                                    <img 
                                        src="data:<?= $imageType ?>;base64,<?= $imageData ?>" 
                                        alt="Question Image" 
                                        class="question-image"
                                        onclick="openImageInNewTab('data:<?= $imageType ?>;base64,<?= $imageData ?>')"
                                    >
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($answer['option_text'] ?: $answer['answer_text']); ?></td>
                            <td>
                                <?php if ($answer['is_correct']): ?>
                                    <span class="answer-status status-correct">Correct</span>
                                <?php else: ?>
                                    <span class="answer-status status-incorrect">Incorrect</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $answer['is_correct'] ? '1' : '0'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="dashboard-section">
            <h3 class="section-heading">
                <i class="fas fa-chart-simple"></i> Performance Summary
            </h3>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 1.2rem;">Overall Performance</h4>
                    <p style="margin: 0; color: #6c757d;">
                        You scored <span class="score-badge <?= $scoreClass ?>"><?= number_format($percentage, 0); ?>%</span>
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.9rem; color: #6c757d; margin-bottom: 0.25rem;">
                        Correct Answers: <?= $submission['score']; ?> / <?= $submission['total_marks']; ?>
                    </div>
                    <?php
                    if ($submission['total_marks'] > 0) {
                        $correctPercentage = ($submission['score'] / $submission['total_marks']) * 100;
                    } else {
                        $correctPercentage = 0;
                    }
                    
                    $incorrectPercentage = 100 - $correctPercentage;
                    ?>
                    <div style="width: 200px; height: 10px; background-color: rgba(220, 53, 69, 0.2); border-radius: 5px; overflow: hidden;">
                        <div style="width: <?= $correctPercentage; ?>%; height: 100%; background-color: var(--success);"></div>
                    </div>
                </div>
            </div>
            
            <?php
            // Performance feedback based on score
            if ($percentage >= 80) {
                $feedbackTitle = "Excellent Work!";
                $feedbackMessage = "You've demonstrated a strong understanding of the subject matter.";
            } elseif ($percentage >= 60) {
                $feedbackTitle = "Good Effort";
                $feedbackMessage = "You've grasped the key concepts, but there's room for improvement in some areas.";
            } else {
                $feedbackTitle = "Needs Improvement";
                $feedbackMessage = "You may need to review the material again to strengthen your understanding.";
            }
            ?>
            
            <div style="background-color: #f8f9fa; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.75rem 0; font-size: 1.2rem;"><?= $feedbackTitle; ?></h4>
                <p style="margin: 0; color: #6c757d;"><?= $feedbackMessage; ?></p>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Function to open image in a new tab
        function openImageInNewTab(imageDataUrl) {
            const newTab = window.open();
            newTab.document.write('<html><head><title>Question Image</title>');
            newTab.document.write('<style>body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #000; }');
            newTab.document.write('img { max-width: 90%; max-height: 90%; object-fit: contain; }</style>');
            newTab.document.write('</head><body>');
            newTab.document.write('<img src="' + imageDataUrl + '" alt="Question Image">');
            newTab.document.write('</body></html>');
            newTab.document.close();
        }
    </script>
</body>
</html>