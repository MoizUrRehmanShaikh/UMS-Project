<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sindh Agriculture University Tando Jam  Management System</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/forms.css">
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
    
    <div class="hero">
        <div class="content">
            <h1>Welcome to Sindh Agriculture University Tando Jam Management System</h1>
            <p class="tagline">Your centralized portal for academic management and growth.</p>
            
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">Login to Portal</a>
                <a href="register.php" class="btn btn-secondary">Register New Account</a>
            </div>
        </div>
    </div>
    
    <div class="info-section">
        <div class="content">
            <h2>Seamless Management for Every Role</h2>
            <div class="roles-grid">
                <div>
                    <h3>Admin</h3>
                    <p>Total control over courses, users, and assignments.</p>
                </div>
                <div>
                    <h3>Teacher</h3>
                    <p>Manage assigned classes, record attendance, and submit grades.</p>
                </div>
                <div>
                    <h3>Student</h3>
                    <p>View personal attendance record and exam results instantly.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>