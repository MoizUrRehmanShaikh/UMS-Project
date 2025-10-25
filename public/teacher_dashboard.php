<?php
session_start();
include '../config/db.php';
// Security check: must be logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Fetch teacher data and assigned courses...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/dashboard.css">
    </head>
<body>
    <h1>Welcome, Teacher <?php echo $_SESSION['username']; ?>!</h1>

    <h2>Assigned Courses</h2>
    <?php
    // Example: Fetch courses assigned to this teacher
    $stmt = $conn->prepare("SELECT c.course_name, c.course_code FROM courses c JOIN course_assignments ca ON c.course_id = ca.course_id WHERE ca.teacher_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['course_name']} ({$row['course_code']}) - <a href='manage_students.php?course_id={$row['course_id']}'>Manage Students/Attendance</a></li>";
    }
    echo "</ul>";
    $stmt->close();
    ?>
    <p>... Additional teacher management (Grade entry, etc.) will go here ...</p>
</body>
</html>