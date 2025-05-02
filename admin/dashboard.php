<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch created tests
$stmt = $pdo->prepare("SELECT 
    t.*, 
    COUNT(DISTINCT q.question_id) as question_count,
    COUNT(DISTINCT s.submission_id) as attempt_count 
    FROM tests t 
    LEFT JOIN questions q ON t.test_id = q.test_id
    LEFT JOIN submissions s ON t.test_id = s.test_id
    WHERE t.created_by = ?
    GROUP BY t.test_id");
$stmt->execute([$_SESSION['user_id']]);
$tests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Placify</title>
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .admin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.7;
        }
        
        .admin-heading {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .admin-subheading {
            margin: 0;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
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
            justify-content: space-between;
        }
        
        .section-heading i {
            margin-right: 0.75rem;
            color: var(--primary);
        }
        
        .action-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .action-button:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .action-button i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        
        .tests-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tests-table th, 
        .tests-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tests-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }
        
        .tests-table tr:last-child td {
            border-bottom: none;
        }
        
        .tests-table tr:hover td {
            background-color: #f8f9fa;
        }
        
        .test-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
        }
        
        .badge-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .badge-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }
        
        .badge-secondary {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .action-links {
            display: flex;
            gap: 0.5rem;
        }
        
        .link-button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.15s ease;
        }
        
        .link-primary {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .link-primary:hover {
            background-color: rgba(67, 97, 238, 0.2);
        }
        
        .link-info {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--accent);
        }
        
        .link-info:hover {
            background-color: rgba(76, 201, 240, 0.2);
        }
        
        .link-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }
        
        .link-danger:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        .link-button i {
            margin-right: 0.25rem;
            font-size: 0.9rem;
        }
        
        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }
        
        .logout-link {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border: 1px solid #ced4da;
            color: #6c757d;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        
        .logout-link:hover {
            background-color: #f8f9fa;
            color: var(--dark);
        }
        
        .logout-link i {
            margin-right: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-icon {
            font-size: 3rem;
            color: #ced4da;
            margin-bottom: 1rem;
        }
        
        .empty-message {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .tests-table {
                display: block;
                overflow-x: auto;
            }
            
            .footer-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-links {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="admin-header">
            <h2 class="admin-heading">Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>
            <p class="admin-subheading">Manage your tests and view student performance</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value"><?= count($tests); ?></div>
                <div class="stat-label">Total Tests</div>
            </div>
            
            <?php
            $totalQuestions = 0;
            $totalAttempts = 0;
            
            foreach ($tests as $test) {
                $totalQuestions += $test['question_count'];
                $totalAttempts += $test['attempt_count'];
            }
            ?>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="stat-value"><?= $totalQuestions; ?></div>
                <div class="stat-label">Total Questions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= $totalAttempts; ?></div>
                <div class="stat-label">Student Attempts</div>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-heading">
                <div>
                    <i class="fas fa-clipboard-list"></i>
                    Your Created Tests
                </div>
                <a href="create_test.php" class="action-button">
                    <i class="fas fa-plus"></i> Create New Test
                </a>
            </div>
            
            <?php if (count($tests) > 0): ?>
                <table class="tests-table">
                    <thead>
                        <tr>
                            <th>Test Title</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests as $test): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($test['test_title']); ?></div>
                                    <div style="font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem;">
                                        <?= htmlspecialchars(mb_strimwidth($test['description'], 0, 60, "...")); ?>
                                    </div>
                                </td>
                                <td><?= $test['question_count']; ?></td>
                                <td><?= $test['attempt_count']; ?></td>
                                <td>
                                    <?php if ($test['question_count'] === 0): ?>
                                        <span class="test-badge badge-warning">No Questions</span>
                                    <?php elseif ($test['is_active']): ?>
                                        <span class="test-badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="test-badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="add_question.php?test_id=<?= $test['test_id']; ?>" class="link-button link-primary">
                                            <i class="fas fa-plus"></i> Add Questions
                                        </a>
                                        <a href="view_results.php?test_id=<?= $test['test_id']; ?>" class="link-button link-info">
                                            <i class="fas fa-chart-bar"></i> Results
                                        </a>
                                        <a href="delete_test.php?test_id=<?= $test['test_id']; ?>" class="link-button link-primary">
                                            <i class="fas fa-edit"></i> Delete
                                        </a>
                                        <?php if ($test['is_active']): ?>
                                            <a href="toggle_test.php?test_id=<?= $test['test_id']; ?>&action=deactivate" class="link-button link-danger">
                                                <i class="fas fa-times-circle"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="toggle_test.php?test_id=<?= $test['test_id']; ?>&action=activate" class="link-button badge-success">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    <div class="empty-message">You haven't created any tests yet</div>
                    <a href="create_test.php" class="action-button">
                        <i class="fas fa-plus"></i> Create Your First Test
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer-actions">
            <div>
                <a href="../index.php" class="link-button link-primary">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="../help/admin_guide.php" class="link-button link-info">
                    <i class="fas fa-question-circle"></i> Help Guide
                </a>
            </div>
            <a href="../auth/logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</body>
</html>