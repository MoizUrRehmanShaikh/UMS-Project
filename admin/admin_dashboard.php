<?php
session_start();
include '../config/db.php';
// Strict Security check: must be logged in as ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch general counts for the dashboard view
$student_count = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$teacher_count = $conn->query("SELECT COUNT(*) FROM teachers")->fetch_row()[0];
$course_count = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/dashboard.css">
    </head>
<body>
    <h1>Admin Panel - <?php echo $_SESSION['username']; ?></h1>
    <a href="../logout.php">Admin Logout</a>
    
    <h2>System Overview</h2>
    <p>Total Registered Students: <strong><?php echo $student_count; ?></strong></p>
    <p>Total Registered Teachers: <strong><?php echo $teacher_count; ?></strong></p>
    <p>Total Courses: <strong><?php echo $course_count; ?></strong></p>

    <h2>Management Tools</h2>
    <ul>
        <li><a href="?section=manage_users">Add/Edit/Delete Students & Teachers</a></li>
        <li><a href="?section=manage_courses">Add/Edit/Delete Courses</a></li>
        <li><a href="?section=assign_courses">Assign Courses to Teachers & Students</a></li>
    </ul>

    <hr>
    
    <div class="management-area">
    <?php
    // --- BASIC ADMIN LOGIC (Conceptual implementation of privileges) ---
    if (isset($_GET['section']) && $_GET['section'] == 'manage_users') {
        echo "<h3>Manage Students and Teachers</h3>";
        // 1. Display list of users
        // 2. Form to add new teacher/student
        // 3. Logic to process EDIT/DELETE actions
    } elseif (isset($_GET['section']) && $_GET['section'] == 'assign_courses') {
        echo "<h3>Assign Courses</h3>";
        // 1. Form to select a Course, then select a Teacher (Course Assignment)
        // 2. Form to select a Student, then select a Course (Enrollment)
    } else {
        echo "<p>Select a management task from the links above.</p>";
    }
    // --- END LOGIC ---
    ?>
    </div>
</body>
</html>