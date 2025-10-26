<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Sindh Agriculture Univesity Tando Jam Management System</title>
    
    <link rel="stylesheet" href="css/base.css"> 
    <style>
        /* If base.css is not fully set up, include the navbar and content styles here */
        /* Otherwise, this section can be empty */
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Sindh Agriculture University Tando Jam</a>
        <a href="about.php">About</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="right">
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
                <?php 
                    $dashboard_link = '';
                    if ($_SESSION['role'] == 'admin') $dashboard_link = 'admin/admin_dashboard.php';
                    else if ($_SESSION['role'] == 'teacher') $dashboard_link = 'public/teacher_dashboard.php';
                    else $dashboard_link = 'public/student_dashboard.php';
                ?>
                <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
            </div>
        <?php else: ?>
            <div class="right">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="content">
        <h1>About the University Management System</h1>
        
        <p>This system has been developed for the comprehensive digital management of academic and administrative activities at Tando Jam University.</p>
        
        <h2>Key Features</h2>
        <ul>
            <li>**Admin Panel:** Full control over user accounts (Student/Teacher) and course assignment.</li>
            <li>**Teacher Dashboard:** Management of assigned courses, student lists, and attendance recording.</li>
            <li>**Student Portal:** Personalized view of enrollment details, attendance records, and future exam results.</li>
        </ul>
        
        <h2>Contact</h2>
        <p>For administrative inquiries, please contact the main office.</p>
    </div>
</body>
</html>