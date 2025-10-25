<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tando Jam University Management System</title>
   <link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/forms.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Tando Jam University</a>
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
        <h1>Welcome to Tando Jam University Management System</h1>
        <p>Your centralized portal for academic management.</p>
        <p>Use the navigation bar to register or log in.</p>
    </div>
</body>
</html>