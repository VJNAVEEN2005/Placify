<?php
session_start(); // Start the session

$servername = "localhost";  // Correct MySQL server hostname
$username = "root";
$password = ""; // Your MySQL password
$database = "placify";
$conn = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if ($conn) {
    echo "Connection successful";
} else {
    echo "Connection failed: " . mysqli_connect_error(); // Output the error message
}

// Fetch students data
$students_query = "SELECT * FROM students ORDER BY registration_no";
$students_result = mysqli_query($conn, $students_query);

// Handle student operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_student'])) {
        $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
        $delete_query = "DELETE FROM students WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        }
    }
}

echo $_SESSION['username'];
echo $_SESSION['password'];

$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $email = $row['email'];
    $institution = $row['institution'];
    $position = $row['position'];
} else {
    $error = "Invalid username or password!";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placify - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #7209b7;
            --secondary: #560bad;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f0fc 0%, #d8bbfd 100%);
            min-height: 100vh;
            display: flex;
            padding: 20px;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            border-radius: var(--border-radius);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 30px 0;
            color: white;
            position: fixed;
            height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header .logo {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .sidebar-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .avatar i {
            font-size: 1.5rem;
        }

        .user-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            margin: 4px 0;
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .logout {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout button {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border: none;
            padding: 12px;
            border-radius: var(--border-radius);
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout button:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .logout button i {
            margin-right: 10px;
        }

        .main-content {
            flex: 1;
            margin-left: 300px;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .welcome p {
            color: #666;
        }

        .date-time {
            background: white;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .date {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .time {
            color: #666;
            margin-top: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.1;
            color: var(--primary);
        }

        .stat-card h3 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 0.95rem;
        }

        .activity-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
        }

        .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            background: #f0f4ff;
            color: var(--primary);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .activity-meta {
            display: flex;
            font-size: 0.9rem;
            color: #888;
        }

        .activity-time {
            margin-right: 15px;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 270px;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .action-btn {
            padding: 8px;
            border: none;
            border-radius: 4px;
            margin: 0 5px;
            cursor: pointer;
            background: none;
        }

        .action-btn.edit {
            color: var(--primary);
        }

        .action-btn.delete {
            color: #dc3545;
        }

        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .test-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .test-status.active {
            background: #d1fae5;
            color: #059669;
        }

        .test-status.inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .test-details p {
            margin: 10px 0;
            color: #666;
        }

        .test-details i {
            margin-right: 10px;
            color: var(--primary);
        }

        .test-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .test-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .start-test {
            background: var(--primary);
            color: white;
        }

        .stop-test {
            background: #dc3545;
            color: white;
            opacity: 0.5;
        }

        .start-test:hover {
            background: var(--secondary);
        }

        .stop-test:not(:disabled):hover {
            background: #bb2d3b;
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }

        .actions {
            margin-left: auto;
        }

        .add-student-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .add-student-btn:hover {
            background: var(--secondary);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: var(--secondary);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #059669;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #dc2626;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo"><i class="fas fa-user-shield"></i></div>
            <h1>Placify</h1>
            <p>Admin Panel</p>
        </div>

        <div class="user-info">
            <div class="user-info-header">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h3><?php echo $firstname . ' ' . $lastname; ?></h3>
                    <p><?php echo $position; ?></p>
                </div>
            </div>
            <div class="institution">
                <i class="fas fa-building"></i> <?php echo $institution; ?>
            </div>
        </div>

        <div class="nav-menu">
            <div style=" cursor: pointer;" onclick="showSection('Dashboard_data')" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </div>
            <div style=" cursor: pointer;" onclick="showSection('Students_data')" class="nav-item">
                <i class="fas fa-users"></i> Students
            </div>
            <div style=" cursor: pointer;" onclick="showSection('Test_data')" class="nav-item">
                <i class="fas fa-book"></i> Test
            </div>
        </div>

        <div class="logout">
            <form action="logout.php" method="POST">
                <button type="submit">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- all page data is here -->
    <!-- Dashboard Section -->
<div id="Dashboard_data">
    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome">
                <h1>Welcome, <?php echo $firstname; ?>!</h1>
                <p>Here's your placement management overview.</p>
            </div>
            <div class="date-time">
                <div class="date" id="current-date">April 1, 2025</div>
                <div class="time" id="current-time">10:30 AM</div>
            </div>
        </div>

        <!-- Stats Cards - Redesigned with icons and more modern layout -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <h3>523</h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 12%
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3>42</h3>
                    <p>Companies</p>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 5%
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-content">
                    <h3>128</h3>
                    <p>Placements</p>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 18%
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                    <h3>3</h3>
                    <p>Active Tests</p>
                </div>
                <div class="stat-trend neutral">
                    <i class="fas fa-minus"></i> 0%
                </div>
            </div>
        </div>

        <!-- Dashboard Main Content - Two column layout -->
        <div class="dashboard-content">
            <!-- Left Column - Activity Feed -->
            <div class="activity-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Activities</h2>
                    <a href="#" class="view-all">View All</a>
                </div>

                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">TechCorp added 5 new job openings</div>
                            <div class="activity-meta">
                                <span class="activity-time"><i class="far fa-clock"></i> Today, 9:45 AM</span>
                                <span class="activity-category">Companies</span>
                            </div>
                        </div>
                    </li>

                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">15 new students registered for placement drive</div>
                            <div class="activity-meta">
                                <span class="activity-time"><i class="far fa-clock"></i> Yesterday, 4:30 PM</span>
                                <span class="activity-category">Students</span>
                            </div>
                        </div>
                    </li>

                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">8 students confirmed placements at InnovateTech</div>
                            <div class="activity-meta">
                                <span class="activity-time"><i class="far fa-clock"></i> March 31, 2025</span>
                                <span class="activity-category">Placements</span>
                            </div>
                        </div>
                    </li>

                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">Campus interview scheduled with GlobalSoft</div>
                            <div class="activity-meta">
                                <span class="activity-time"><i class="far fa-clock"></i> March 30, 2025</span>
                                <span class="activity-category">Events</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Right Column - Quick Access & Upcoming Events -->
            <div class="dashboard-right-column">
                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <div class="section-header">
                        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="#" class="quick-action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Student</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="fas fa-building"></i>
                            <span>Add Company</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Schedule Drive</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="fas fa-file-alt"></i>
                            <span>Create Test</span>
                        </a>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="upcoming-events-card">
                    <div class="section-header">
                        <h2><i class="fas fa-calendar"></i> Upcoming Events</h2>
                        <a href="#" class="view-all">View Calendar</a>
                    </div>
                    <div class="events-list">
                        <div class="event-item">
                            <div class="event-date">
                                <div class="event-month">Apr</div>
                                <div class="event-day">05</div>
                            </div>
                            <div class="event-details">
                                <h4>Pre-Placement Talk - TechCorp</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Auditorium | <i class="fas fa-clock"></i> 10:00 AM</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <div class="event-month">Apr</div>
                                <div class="event-day">08</div>
                            </div>
                            <div class="event-details">
                                <h4>Aptitude Test - InnovateTech</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Lab B-401 | <i class="fas fa-clock"></i> 2:00 PM</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <div class="event-month">Apr</div>
                                <div class="event-day">12</div>
                            </div>
                            <div class="event-details">
                                <h4>Campus Interview - GlobalSoft</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Placement Cell | <i class="fas fa-clock"></i> 9:00 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placement Stats -->
        <div class="placement-stats">
            <div class="section-header">
                <h2><i class="fas fa-chart-pie"></i> Placement Statistics</h2>
            </div>
            <div class="stats-cards-container">
                <div class="placement-stat-card">
                    <div class="stat-header">
                        <h3>Placement Rate</h3>
                    </div>
                    <div class="stat-percentage">78%</div>
                    <div class="stat-description">
                        <p>386 out of 523 students placed</p>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar" style="width: 78%"></div>
                    </div>
                </div>
                <div class="placement-stat-card">
                    <div class="stat-header">
                        <h3>Avg. Package</h3>
                    </div>
                    <div class="stat-percentage">₹6.2L</div>
                    <div class="stat-description">
                        <p>12% increase from last year</p>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar" style="width: 62%"></div>
                    </div>
                </div>
                <div class="placement-stat-card">
                    <div class="stat-header">
                        <h3>Top Package</h3>
                    </div>
                    <div class="stat-percentage">₹24L</div>
                    <div class="stat-description">
                        <p>Offered by TechGiant Inc.</p>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    /* New Dashboard Styles */
    .dashboard-content {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .activity-section {
        flex: 1;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 25px;
    }
    
    .dashboard-right-column {
        width: 40%;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .quick-actions-card, 
    .upcoming-events-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 25px;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .section-header h2 {
        color: var(--dark);
        font-size: 1.3rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-header h2 i {
        color: var(--primary);
    }
    
    .view-all {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .view-all:hover {
        color: var(--secondary);
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        transition: transform 0.3s;
        position: relative;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(114, 9, 183, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }
    
    .stat-icon-wrapper i {
        font-size: 1.5rem;
        color: var(--primary);
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-content h3 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 5px;
    }
    
    .stat-content p {
        color: #666;
        font-size: 0.9rem;
        margin: 0;
    }
    
    .stat-trend {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 3px;
    }
    
    .stat-trend.positive {
        color: #10b981;
    }
    
    .stat-trend.negative {
        color: #ef4444;
    }
    
    .stat-trend.neutral {
        color: #6b7280;
    }
    
    /* Activity List */
    .activity-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .activity-item {
        display: flex;
        align-items: flex-start;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .activity-icon {
        background: rgba(114, 9, 183, 0.1);
        color: var(--primary);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .activity-details {
        flex: 1;
    }
    
    .activity-title {
        font-weight: 500;
        color: var(--dark);
        margin-bottom: 5px;
    }
    
    .activity-meta {
        display: flex;
        font-size: 0.85rem;
        color: #6b7280;
        gap: 15px;
    }
    
    .activity-time i,
    .activity-category i {
        margin-right: 5px;
    }
    
    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .quick-action-btn {
        background: rgba(114, 9, 183, 0.05);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .quick-action-btn:hover {
        background: rgba(114, 9, 183, 0.1);
        transform: translateY(-3px);
    }
    
    .quick-action-btn i {
        font-size: 1.5rem;
        color: var(--primary);
    }
    
    .quick-action-btn span {
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    /* Upcoming Events */
    .events-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .event-item {
        display: flex;
        align-items: center;
        background: rgba(114, 9, 183, 0.03);
        border-radius: 10px;
        padding: 12px;
        transition: all 0.3s;
    }
    
    .event-item:hover {
        background: rgba(114, 9, 183, 0.08);
    }
    
    .event-date {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        border: 1px solid rgba(114, 9, 183, 0.2);
    }
    
    .event-month {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--primary);
        text-transform: uppercase;
    }
    
    .event-day {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark);
    }
    
    .event-details {
        flex: 1;
    }
    
    .event-details h4 {
        font-size: 1rem;
        margin-bottom: 5px;
        color: var(--dark);
    }
    
    .event-details p {
        font-size: 0.85rem;
        color: #6b7280;
        margin: 0;
    }
    
    .event-details i {
        margin-right: 3px;
        font-size: 0.8rem;
    }
    
    /* Placement Stats */
    .placement-stats {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 25px;
    }
    
    .stats-cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .placement-stat-card {
        padding: 20px;
        background: rgba(114, 9, 183, 0.03);
        border-radius: 10px;
        text-align: center;
    }
    
    .stat-header h3 {
        font-size: 1.1rem;
        color: var(--dark);
        margin-bottom: 10px;
    }
    
    .stat-percentage {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 5px;
    }
    
    .stat-description {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    
    .stat-progress {
        height: 8px;
        background: rgba(114, 9, 183, 0.1);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 4px;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .dashboard-content {
            flex-direction: column;
        }
        
        .dashboard-right-column {
            width: 100%;
        }
        
        .stats-cards-container {
            grid-template-columns: 1fr;
        }
    }
</style>
<div id="Students_data" style="display: none;">
    <div class="main-content">
        <?php
        // Display success/error messages
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <div class="dashboard-header">
            <div class="welcome">
                <h1>Student Management</h1>
                <p>View and manage all registered students.</p>
            </div>
            <div class="actions">
                <button class="add-student-btn" onclick="showAddStudentForm()">
                    <i class="fas fa-plus"></i> Add New Student
                </button>
            </div>
        </div>

        <!-- Add Student Form (Hidden by default) -->
        <div id="addStudentForm" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideAddStudentForm()">&times;</span>
                <h2>Add New Student</h2>
                <form action="../add_student.php" method="POST">
                    <div class="form-group">
                        <label for="registration_no">Registration No:</label>
                        <input type="text" id="registration_no" name="registration_no" required>
                    </div>
                    <div class="form-group">
                        <label for="student_name">Student Name:</label>
                        <input type="text" id="student_name" name="student_name" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select id="year" name="year" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Add Student</button>
                </form>
            </div>
        </div>

        <!-- Edit Student Form -->
        <div id="editStudentForm" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideEditStudentForm()">&times;</span>
                <h2>Edit Student</h2>
                <form action="update_student.php" method="POST">
                    <input type="hidden" id="edit_student_id" name="student_id">
                    <div class="form-group">
                        <label for="edit_registration_no">Registration No:</label>
                        <input type="text" id="edit_registration_no" name="registration_no" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_student_name">Student Name:</label>
                        <input type="text" id="edit_student_name" name="student_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_year">Year:</label>
                        <select id="edit_year" name="year" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Update Student</button>
                </form>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="student-controls">
            <div class="search-box">
                <input type="text" id="studentSearch" placeholder="Search students..." onkeyup="searchStudents()">
                <i class="fas fa-search"></i>
            </div>
            <div class="filter-dropdown">
                <select id="yearFilter" onchange="filterStudents()">
                    <option value="">All Years</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
        </div>

        <!-- Student Stats Cards -->
        <div class="student-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <h3><?php echo mysqli_num_rows($students_result); ?></h3>
                <p>Total Students</p>
            </div>
            
            <?php
            // Reset the result pointer to beginning
            mysqli_data_seek($students_result, 0);
            
            // Count students by year
            $yearCounts = array(
                '1st Year' => 0,
                '2nd Year' => 0,
                '3rd Year' => 0,
                '4th Year' => 0
            );
            
            while ($row = mysqli_fetch_assoc($students_result)) {
                if (isset($yearCounts[$row['year']])) {
                    $yearCounts[$row['year']]++;
                }
            }
            
            // Reset the result pointer for the table
            mysqli_data_seek($students_result, 0);
            
            // Display year stats
            foreach ($yearCounts as $year => $count) {
                $yearClass = strtolower(str_replace(' ', '-', $year));
                echo '<div class="stat-card ' . $yearClass . '">
                      <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                      <h3>' . $count . '</h3>
                      <p>' . $year . ' Students</p>
                  </div>';
            }
            ?>
        </div>

        <div class="student-list-wrapper">
            <div class="student-list">
                <table class="data-table" id="studentTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Registration No. <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1)">Student Name <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2)">Year <i class="fas fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($students_result) > 0) {
                            while ($row = mysqli_fetch_assoc($students_result)) {
                                echo "<tr class='student-row' data-year='" . htmlspecialchars($row['year']) . "'>";
                                echo "<td>" . htmlspecialchars($row['registration_no']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                                echo "<td class='action-cell'>";
                                echo "<button class='action-btn edit' onclick='editStudent(" . $row['id'] . ", \"" . htmlspecialchars($row['registration_no']) . "\", \"" . htmlspecialchars($row['student_name']) . "\", \"" . htmlspecialchars($row['year']) . "\")'><i class='fas fa-edit'></i></button>";
                                echo "<form action='' method='POST' style='display: inline;'>";
                                echo "<input type='hidden' name='student_id' value='" . $row['id'] . "'>";
                                echo "<button type='submit' name='delete_student' class='action-btn delete' onclick='return confirm(\"Are you sure you want to delete this student?\")'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center;'>No students found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <button id="prevPage" onclick="changePage(-1)" disabled><i class="fas fa-chevron-left"></i></button>
                <span id="pageInfo">Page 1 of 1</span>
                <button id="nextPage" onclick="changePage(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Additional styles for the redesigned Students section */
    .student-controls {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        font-size: 16px;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    
    .filter-dropdown select {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        font-size: 16px;
        background-color: white;
        cursor: pointer;
    }
    
    .student-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .student-stats .stat-card {
        transition: transform 0.3s;
    }
    
    .student-stats .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .student-stats .stat-card.first-year {
        border-left: 4px solid #4cc9f0;
    }
    
    .student-stats .stat-card.second-year {
        border-left: 4px solid #4895ef;
    }
    
    .student-stats .stat-card.third-year {
        border-left: 4px solid #3f37c9;
    }
    
    .student-stats .stat-card.fourth-year {
        border-left: 4px solid #7209b7;
    }
    
    .student-list-wrapper {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .data-table {
        margin-bottom: 20px;
    }
    
    .data-table th {
        cursor: pointer;
        position: relative;
    }
    
    .data-table th i {
        margin-left: 5px;
        font-size: 0.8rem;
    }
    
    .student-row {
        transition: background-color 0.2s;
    }
    
    .student-row:hover {
        background-color: #f8f0fc !important;
    }
    
    .action-cell {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .action-btn.edit {
        background-color: rgba(114, 9, 183, 0.1);
    }
    
    .action-btn.edit:hover {
        background-color: rgba(114, 9, 183, 0.2);
    }
    
    .action-btn.delete {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .action-btn.delete:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }
    
    .pagination button {
        width: 40px;
        height: 40px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .pagination button:hover:not(:disabled) {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .pagination span {
        margin: 0 15px;
        font-size: 14px;
        color: #666;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .student-controls {
            flex-direction: column;
            gap: 15px;
        }
        
        .search-box {
            max-width: 100%;
        }
        
        .student-stats {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    @media (max-width: 576px) {
        .student-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Additional JavaScript for the student section
    let currentPage = 1;
    const rowsPerPage = 10;
    let studentTable;
    let rows;
    
    function initPagination() {
        studentTable = document.getElementById('studentTable');
        rows = studentTable.querySelectorAll('tbody tr');
        
        // Calculate total pages
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
        
        // Enable/disable pagination buttons
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
        
        // Show only the rows for current page
        showPage(currentPage);
    }
    
    function showPage(page) {
        // Hide all rows
        rows.forEach((row, index) => {
            row.style.display = 'none';
            
            // Show rows for current page
            if (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) {
                row.style.display = '';
            }
        });
    }
    
    function changePage(direction) {
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        const newPage = currentPage + direction;
        
        if (newPage > 0 && newPage <= totalPages) {
            currentPage = newPage;
            showPage(currentPage);
            
            // Update page info and buttons
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages;
        }
    }
    
    function searchStudents() {
        const input = document.getElementById('studentSearch');
        const filter = input.value.toUpperCase();
        const yearFilter = document.getElementById('yearFilter').value;
        let visibleCount = 0;
        
        rows.forEach(row => {
            const name = row.cells[1].textContent.toUpperCase();
            const regNo = row.cells[0].textContent.toUpperCase();
            const yearMatch = yearFilter === '' || row.getAttribute('data-year') === yearFilter;
            
            if ((name.indexOf(filter) > -1 || regNo.indexOf(filter) > -1) && yearMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Reset pagination after search
        currentPage = 1;
        initPagination();
    }
    
    function filterStudents() {
        searchStudents(); // Reuse the search function which also checks the year filter
    }
    
    function sortTable(columnIndex) {
        const table = document.getElementById('studentTable');
        let switching = true;
        let direction = 'asc';
        let switchcount = 0;
        
        while (switching) {
            switching = false;
            const rows = table.rows;
            
            for (let i = 1; i < (rows.length - 1); i++) {
                let shouldSwitch = false;
                const x = rows[i].getElementsByTagName('td')[columnIndex];
                const y = rows[i + 1].getElementsByTagName('td')[columnIndex];
                
                if (direction === 'asc') {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                } else {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
                
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                }
            }
            
            if (switchcount === 0 && direction === 'asc') {
                direction = 'desc';
                switching = true;
            }
        }
        
        // Reset pagination after sorting
        currentPage = 1;
        initPagination();
    }
    
    function showAddStudentForm() {
        document.getElementById('addStudentForm').style.display = 'block';
    }
    
    function hideAddStudentForm() {
        document.getElementById('addStudentForm').style.display = 'none';
    }
    
    function hideEditStudentForm() {
        document.getElementById('editStudentForm').style.display = 'none';
    }
    
    function editStudent(studentId, regNo, name, year) {
        // Populate edit form
        document.getElementById('edit_student_id').value = studentId;
        document.getElementById('edit_registration_no').value = regNo;
        document.getElementById('edit_student_name').value = name;
        document.getElementById('edit_year').value = year;
        
        // Show edit form
        document.getElementById('editStudentForm').style.display = 'block';
    }
    
    // Initialize pagination when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize only if we're on the Students section
        if (document.getElementById('studentTable')) {
            initPagination();
        }
    });
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        const addModal = document.getElementById('addStudentForm');
        const editModal = document.getElementById('editStudentForm');
        
        if (event.target == addModal) {
            addModal.style.display = "none";
        }
        
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
    }
</script>
    <div id="Test_data" style="display: none;">
        <div class="main-content">
            <div class="dashboard-header">
                <div class="welcome">
                    <h1>Test Management</h1>
                    <p>Manage and monitor placement tests.</p>
                </div>
            </div>

            <div class="test-grid">
                <div class="test-card">
                    <div class="test-header">
                        <h3>Aptitude Test</h3>
                        <span class="test-status active">Active</span>
                    </div>
                    <div class="test-details">
                        <p><i class="fas fa-question-circle"></i> Questions: 50</p>
                        <p><i class="fas fa-star"></i> Total Marks: 100</p>
                        <p><i class="fas fa-clock"></i> Duration: 2 hours</p>
                    </div>
                    <div class="test-actions">
                        <button class="start-test">Start Test</button>
                        <button class="stop-test" disabled>Stop Test</button>
                    </div>
                </div>

                <div class="test-card">
                    <div class="test-header">
                        <h3>Technical Assessment</h3>
                        <span class="test-status inactive">Inactive</span>
                    </div>
                    <div class="test-details">
                        <p><i class="fas fa-question-circle"></i> Questions: 30</p>
                        <p><i class="fas fa-star"></i> Total Marks: 60</p>
                        <p><i class="fas fa-clock"></i> Duration: 1.5 hours</p>
                    </div>
                    <div class="test-actions">
                        <button class="start-test">Start Test</button>
                        <button class="stop-test" disabled>Stop Test</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update date and time
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit' };

            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Initial update
        updateDateTime();

        // Update every minute
        setInterval(updateDateTime, 60000);

        function showSection(section) {
            // Hide all sections
            document.getElementById('Dashboard_data').style.display = 'none';
            document.getElementById('Students_data').style.display = 'none';
            document.getElementById('Test_data').style.display = 'none';

            // Show selected section
            document.getElementById(section).style.display = 'block';

            // Update active state in navigation
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('onclick').includes(section)) {
                    item.classList.add('active');
                }
            });
        }

        // Test management functions
        document.addEventListener('DOMContentLoaded', function() {
            const startButtons = document.querySelectorAll('.start-test');
            const stopButtons = document.querySelectorAll('.stop-test');

            startButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    btn.disabled = true;
                    stopButtons[index].disabled = false;
                    const testCard = btn.closest('.test-card');
                    const status = testCard.querySelector('.test-status');
                    status.textContent = 'Active';
                    status.classList.remove('inactive');
                    status.classList.add('active');
                });
            });

            stopButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    btn.disabled = true;
                    startButtons[index].disabled = false;
                    const testCard = btn.closest('.test-card');
                    const status = testCard.querySelector('.test-status');
                    status.textContent = 'Inactive';
                    status.classList.remove('active');
                    status.classList.add('inactive');
                });
            });
        });

        // Add these new functions
        function showAddStudentForm() {
            document.getElementById('addStudentForm').style.display = 'block';
        }

        function hideAddStudentForm() {
            document.getElementById('addStudentForm').style.display = 'none';
        }

        function editStudent(studentId) {
            // Implement edit functionality
            alert('Edit student with ID: ' + studentId);
            // You can show a modal form similar to add student
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addStudentForm');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>