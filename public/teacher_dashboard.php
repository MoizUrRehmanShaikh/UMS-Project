<?php
session_start();
include '../config/db.php';

// Security check: Must be logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = "Teacher"; // Default name

// Fetch teacher's full name from the teachers table
$stmt = $conn->prepare("SELECT name FROM teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) {
    $teacher_name = $row['name'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>SAU Teacher Dashboard</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="navbar">
        <a href="../index.php">Home</a>
        <a href="teacher_dashboard.php">Teacher Dashboard</a>
        <div class="right">
            <a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </div>
    
    <div class="content">
        <h1>Welcome, Teacher <?php echo htmlspecialchars($teacher_name); ?>!</h1>

        <h2>Assigned Courses</h2>
        <?php
        // Fetch courses assigned to this teacher
        $stmt = $conn->prepare("
            SELECT c.course_name, c.course_code, c.course_id 
            FROM courses c 
            JOIN course_assignments ca ON c.course_id = ca.course_id 
            WHERE ca.teacher_id = ?
        ");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $courses_result = $stmt->get_result();
        
        if ($courses_result->num_rows > 0) {
            echo "<p>Select a course to manage students and attendance:</p>";
            echo "<ul>";
            while ($row = $courses_result->fetch_assoc()) {
                echo "<li>{$row['course_name']} ({$row['course_code']}) - 
                      <a href='manage_students.php?course_id={$row['course_id']}'>Manage Students/Attendance</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No courses have been assigned to you yet.</p>";
        }
        $stmt->close();
        ?>
    </div>
</body>
</html>