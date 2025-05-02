<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Check if test_id is provided
if (!isset($_GET['test_id'])) {
    header('Location: dashboard.php');
    exit;
}

$test_id = $_GET['test_id'];

// Fetch the test details
$stmt = $pdo->prepare("SELECT * FROM tests WHERE test_id = ?");
$stmt->execute([$test_id]);
$test = $stmt->fetch();

// Fetch questions with their options AND image data
$stmt = $pdo->prepare("
    SELECT q.question_id, q.question_text, q.points, q.image_data, q.image_type, o.option_id, o.option_text
    FROM questions q
    JOIN options o ON q.question_id = o.question_id
    WHERE q.test_id = ?
    ORDER BY q.question_id, o.option_id
");
$stmt->execute([$test_id]);
$raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group options under their questions
$questions = [];
foreach ($raw_data as $row) {
    $qid = $row['question_id'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'question_id' => $qid,
            'question_text' => $row['question_text'],
            'points' => $row['points'],
            'image_data' => $row['image_data'],
            'image_type' => $row['image_type'],
            'options' => []
        ];
    }
    $questions[$qid]['options'][] = [
        'option_id' => $row['option_id'],
        'option_text' => $row['option_text']
    ];
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $total_marks = 0;

    // Create submission
    $stmt = $pdo->prepare("INSERT INTO submissions (test_id, user_id) VALUES (?, ?)");
    $stmt->execute([$test_id, $_SESSION['user_id']]);
    $submission_id = $pdo->lastInsertId();

    // Calculate the total_marks for the test
    foreach ($questions as $q) {
        $total_marks += $q['points'];
    }

    // Evaluate each question
    foreach ($questions as $q) {
        $qid = $q['question_id'];
        $selected_option_id = $_POST['question_' . $qid] ?? null;

        // Get correct option(s)
        $correct_stmt = $pdo->prepare("SELECT option_id FROM options WHERE question_id = ? AND is_correct = 1");
        $correct_stmt->execute([$qid]);
        $correct_option = $correct_stmt->fetchColumn();

        $is_correct = ($selected_option_id == $correct_option) ? 1 : 0;
        if ($is_correct) $score += $q['points'];

        // Save answer
        $stmt = $pdo->prepare("
            INSERT INTO answers (submission_id, question_id, selected_option_id, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$submission_id, $qid, $selected_option_id, $is_correct]);
    }

    // Update score and total_marks in submissions
    $stmt = $pdo->prepare("UPDATE submissions SET score = ?, total_marks = ? WHERE submission_id = ?");
    $stmt->execute([$score, $total_marks, $submission_id]);

    // Redirect to result page
    header("Location: result.php?submission_id=" . $submission_id);
    exit;
}

// Count total questions
$question_count = count($questions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($test['test_title']); ?> | Placify</title>
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
        
        .top-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            background-color: white;
            box-shadow: 0 2px 6px rgba(67, 97, 238, 0.1);
        }
        
        .back-link i {
            margin-right: 0.5rem;
        }
        
        .back-link:hover {
            color: var(--secondary);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.15);
            transform: translateY(-1px);
        }
        
        .test-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .test-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.7;
        }
        
        .test-heading {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .test-info {
            position: relative;
            z-index: 1;
            max-width: 80%;
        }
        
        .test-summary {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .test-stats {
            display: flex;
            gap: 1rem;
        }
        
        .stat-box {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 90px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
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
        
        .question-card {
            border-radius: 12px;
            padding: 0;
            margin-bottom: 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .question-card:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .question-header {
            background-color: #f8f9fa;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .question-text-wrapper {
            display: flex;
            align-items: center;
        }
        
        .question-number {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(67, 97, 238, 0.2);
        }
        
        .question-text {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .question-points {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .options-list {
            list-style: none;
            padding: 1.5rem;
            margin: 0;
        }
        
        .option-item {
            margin-bottom: 1rem;
            position: relative;
        }
        
        .option-item:last-child {
            margin-bottom: 0;
        }
        
        .option-label {
            display: flex;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .option-label:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .option-input {
            position: relative;
            margin-right: 1rem;
            cursor: pointer;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .option-text {
            flex: 1;
            font-size: 1rem;
            line-height: 1.5;
        }
        
        .form-note {
            background-color: rgba(76, 201, 240, 0.1);
            border-left: 4px solid var(--accent);
            padding: 1.25rem;
            margin-bottom: 2rem;
            border-radius: 0 8px 8px 0;
            font-size: 0.95rem;
            color: #495057;
            display: flex;
            align-items: flex-start;
        }
        
        .form-note i {
            color: var(--accent);
            margin-right: 0.75rem;
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }
        
        .submit-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .submit-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
        }
        
        .submit-button i {
            margin-left: 0.5rem;
        }
        
        .submit-button:hover {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(67, 97, 238, 0.25);
        }
        
        /* New style for question images */
        .question-image {
            margin: 1rem 0;
            display: block;
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .question-image-container {
            padding: 0 1.5rem;
            margin-top: 1rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .test-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .test-stats {
                width: 100%;
                justify-content: space-between;
            }
            
            .question-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .question-points {
                margin-top: 0.75rem;
            }
            
            .submit-container {
                flex-direction: column-reverse;
                gap: 1rem;
            }
            
            .submit-button, .back-link {
                width: 100%;
                justify-content: center;
            }
            
            .back-link {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="top-navigation">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="test-header">
            <h2 class="test-heading"><?= htmlspecialchars($test['test_title']); ?></h2>
            <div class="test-info">
                <p><?= htmlspecialchars($test['description']); ?></p>
            </div>
            <div class="test-summary">
                <div class="test-stats">
                    <div class="stat-box">
                        <div class="stat-value"><?= $question_count; ?></div>
                        <div class="stat-label">Questions</div>
                    </div>
                    <?php
                    // Calculate total points
                    $total_points = 0;
                    foreach ($questions as $q) {
                        $total_points += $q['points'];
                    }
                    ?>
                    <div class="stat-box">
                        <div class="stat-value"><?= $total_points; ?></div>
                        <div class="stat-label">Total Points</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-note">
            <i class="fas fa-info-circle"></i>
            <div>Please answer all questions below. Each question has only one correct answer.</div>
        </div>
        
        <form method="POST">
            <div class="dashboard-section">
                <h3 class="section-heading">
                    <i class="fas fa-question-circle"></i> Test Questions
                </h3>
                <?php $questionNumber = 1; ?>
                <?php foreach ($questions as $q): ?>
                    <div class="question-card">
                        <div class="question-header">
                            <div class="question-text-wrapper">
                                <span class="question-number"><?= $questionNumber++; ?></span>
                                <div class="question-text"><?= htmlspecialchars($q['question_text']); ?></div>
                            </div>
                            <span class="question-points"><?= $q['points']; ?> <?= $q['points'] > 1 ? 'Points' : 'Point' ?></span>
                        </div>
                        
                        <?php if (!empty($q['image_data'])): ?>
                        <div class="question-image-container">
                            <?php
                            // Convert binary image data to base64 for display
                            $base64Image = base64_encode($q['image_data']);
                            $imageType = $q['image_type'];
                            ?>
                            <img src="data:<?= $imageType; ?>;base64,<?= $base64Image; ?>" alt="Question Image" class="question-image">
                        </div>
                        <?php endif; ?>
                        
                        <ul class="options-list">
                            <?php foreach ($q['options'] as $opt): ?>
                                <li class="option-item">
                                    <label class="option-label">
                                        <input type="radio" name="question_<?= $q['question_id']; ?>" value="<?= $opt['option_id']; ?>" required class="option-input">
                                        <span class="option-text"><?= htmlspecialchars($opt['option_text']); ?></span>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                
                <div class="submit-container">
                    <button type="submit" class="submit-button">
                        Submit Test <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>