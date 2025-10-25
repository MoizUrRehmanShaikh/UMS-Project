<?php
session_start();
include '../config/db.php';
// Security check: must be logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

// Fetch student data and enrolled courses...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Portal</title>
    <link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/dashboard.css">
    </head>
<body>
    <h1>Welcome, Student <?php echo $_SESSION['username']; ?>!</h1>
    
    <h2>Your Courses and Attendance</h2>
    <?php
    // Example: Fetch courses and attendance for the logged-in student
    $stmt = $conn->prepare("SELECT c.course_name, s.name FROM students s JOIN enrollment e ON s.student_id = e.student_id JOIN courses c ON e.course_id = c.course_id WHERE s.student_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['course_name']} - Check Exam Results / Attendance % (Logic to be implemented)</li>";
    }
    echo "</ul>";
    $stmt->close();
    ?>
    <p>... Additional student privileges (Exam results, etc.) will go here ...</p>
</body>
</html>