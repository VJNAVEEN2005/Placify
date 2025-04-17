<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['test_id'])) {
    header('Location: dashboard.php');
    exit;
}

$test_id = $_GET['test_id'];
$message = '';

// Fetch test info
$test_stmt = $pdo->prepare("SELECT test_title FROM tests WHERE test_id = ?");
$test_stmt->execute([$test_id]);
$test = $test_stmt->fetch(PDO::FETCH_ASSOC);

// Handle deletion
if (isset($_GET['delete_question_id'])) {
    $qid = $_GET['delete_question_id'];

    // Delete from options first (due to foreign key)
    $pdo->prepare("DELETE FROM options WHERE question_id = ?")->execute([$qid]);

    // Then delete from questions
    $pdo->prepare("DELETE FROM questions WHERE question_id = ?")->execute([$qid]);

    $message = "Question deleted successfully!";
}

// Handle adding new question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_text'])) {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];
    $points = $_POST['points'] ?? 1;

    $stmt = $pdo->prepare("INSERT INTO questions (test_id, question_text, question_type, points) VALUES (?, ?, 'mcq', ?)");
    if ($stmt->execute([$test_id, $question_text, $points])) {
        $question_id = $pdo->lastInsertId();

        $options = [
            'A' => $option_a,
            'B' => $option_b,
            'C' => $option_c,
            'D' => $option_d
        ];

        foreach ($options as $label => $text) {
            $is_correct = ($label === $correct_option) ? 1 : 0;
            $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)")
                ->execute([$question_id, $text, $is_correct]);
        }

        $message = "Question added successfully!";
    } else {
        $message = "Something went wrong while adding the question.";
    }
}

// Fetch questions to display
$questions = $pdo->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY question_id DESC");
$questions->execute([$test_id]);
$questions = $questions->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question | Placify</title>
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
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .form-section {
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
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background-color: #fff;
            font-size: 1rem;
            color: var(--dark);
            transition: border-color 0.15s ease-in-out;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.25);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .option-row {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .option-label {
            background-color: var(--light);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .option-input {
            flex-grow: 1;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
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
            border: none;
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .questions-list {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }
        
        .question-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
        }
        
        .question-item:last-child {
            border-bottom: none;
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .question-text {
            font-weight: 600;
            font-size: 1.1rem;
            line-height: 1.5;
            margin: 0;
            flex-grow: 1;
            padding-right: 1rem;
        }
        
        .question-points {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .options-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1rem 0;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            background-color: var(--light);
        }
        
        .option-item.correct {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 3px solid var(--success);
        }
        
        .option-marker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
            flex-shrink: 0;
            border: 1px solid #ced4da;
        }
        
        .option-item.correct .option-marker {
            background-color: var(--success);
            color: white;
            border-color: var(--success);
        }
        
        .question-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .action-link {
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
            text-decoration: none;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        
        .action-link:hover {
            background-color: #f8f9fa;
        }
        
        .action-link i {
            margin-right: 0.35rem;
        }
        
        .action-link.delete {
            color: var(--error);
        }
        
        .action-link.delete:hover {
            background-color: rgba(220, 53, 69, 0.1);
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
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .question-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .question-points {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Add MCQ Questions</h1>
                <?php if(isset($test['test_title'])): ?>
                <div class="test-name">Test: <?= htmlspecialchars($test['test_title']) ?></div>
                <?php endif; ?>
            </div>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-plus-circle"></i>
                <h2 class="section-title">Add New Question</h2>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="question_text">Question Text:</label>
                    <textarea name="question_text" id="question_text" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="points">Points:</label>
                        <input type="number" name="points" id="points" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label for="correct_option">Correct Option:</label>
                        <select name="correct_option" id="correct_option" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Answer Options:</label>
                    
                    <div class="option-row">
                        <div class="option-label">A</div>
                        <input type="text" name="option_a" class="option-input" required>
                    </div>
                    
                    <div class="option-row">
                        <div class="option-label">B</div>
                        <input type="text" name="option_b" class="option-input" required>
                    </div>
                    
                    <div class="option-row">
                        <div class="option-label">C</div>
                        <input type="text" name="option_c" class="option-input" required>
                    </div>
                    
                    <div class="option-row">
                        <div class="option-label">D</div>
                        <input type="text" name="option_d" class="option-input" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
            </form>
        </div>
        
        <div class="questions-list">
            <div class="section-header">
                <i class="fas fa-list"></i>
                <h2 class="section-title">All Questions (<?= count($questions) ?>)</h2>
            </div>
            
            <?php if (count($questions) === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <p class="empty-message">No questions added yet. Start by adding your first question above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $q): ?>
                    <div class="question-item">
                        <div class="question-header">
                            <h3 class="question-text"><?= htmlspecialchars($q['question_text']) ?></h3>
                            <span class="question-points"><?= $q['points'] ?> <?= $q['points'] == 1 ? 'point' : 'points' ?></span>
                        </div>
                        
                        <ul class="options-list">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
                            $stmt->execute([$q['question_id']]);
                            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $optionLabels = ['A', 'B', 'C', 'D'];
                            
                            foreach ($options as $index => $opt):
                                $label = $optionLabels[$index] ?? $index + 1;
                            ?>
                                <li class="option-item <?= $opt['is_correct'] ? 'correct' : '' ?>">
                                    <div class="option-marker"><?= $label ?></div>
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                    <?php if($opt['is_correct']): ?>
                                        <small style="margin-left: auto; color: var(--success);">(Correct)</small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="question-actions">
                            <a href="add_question.php?test_id=<?= $test_id ?>&delete_question_id=<?= $q['question_id'] ?>" 
                               class="action-link delete"
                               onclick="return confirm('Are you sure you want to delete this question?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
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