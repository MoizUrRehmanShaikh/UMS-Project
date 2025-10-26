<?php
session_start();
include '../config/db.php';

// Security check: Must be logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = "Student"; // Default name

// Fetch student's full name and registration number from the students table
$stmt = $conn->prepare("SELECT name, registration_number, department, year FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $student_name = $row['name'];
    $reg_num = $row['registration_number'];
    $department = $row['department'];
    $year = $row['year'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Portal</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
        
        <div class="system-overview">
            <h2>Your Details</h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($reg_num ?? 'N/A'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($department ?? 'N/A'); ?></p>
            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($year ?? 'N/A'); ?></p>
        </div>

        <h2>Your Courses and Attendance</h2>
        <?php
        // Example: Fetch enrolled courses (Logic remains the same as it references the student_id correctly)
        $stmt = $conn->prepare("
            SELECT c.course_name, c.course_code 
            FROM enrollment e 
            JOIN courses c ON e.course_id = c.course_id 
            WHERE e.student_id = ?
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $courses_result = $stmt->get_result();
        
        if ($courses_result->num_rows > 0) {
            echo "<ul>";
            while ($row = $courses_result->fetch_assoc()) {
                echo "<li>{$row['course_name']} ({$row['course_code']}) - Attendance/Results (Logic to be implemented)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>You are not currently enrolled in any courses.</p>";
        }
        $stmt->close();
        ?>
    </div>
</body>
</html>