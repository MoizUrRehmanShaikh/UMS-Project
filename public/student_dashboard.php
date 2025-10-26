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
    <div class="navbar">
        <a href="../index.php">Home</a>
        <a href="student_dashboard.php"> SAU Student Portal</a>
        <div class="right">
            <a href="../logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
        </div>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
        
        <div class="system-overview">
            <h2>Your Details</h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($reg_num ?? 'N/A'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($department ?? 'N/A'); ?></p>
            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($year ?? 'N/A'); ?></p>
        </div>

        <h2>Your Courses, Attendance & Results</h2>
        
        <?php
        // Fetch all enrolled courses, including the enrollment_id for joining
        $stmt = $conn->prepare("
            SELECT c.course_id, c.course_name, c.course_code, e.enrollment_id
            FROM enrollment e 
            JOIN courses c ON e.course_id = c.course_id 
            WHERE e.student_id = ?
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $courses_result = $stmt->get_result();
        $stmt->close();
        
        if ($courses_result->num_rows > 0) {
            echo "<table><thead><tr><th>Course Name (Code)</th><th>Attendance %</th><th>Results</th></tr></thead><tbody>";
            
            while ($row = $courses_result->fetch_assoc()) {
                $course_id = $row['course_id'];
                $enrollment_id = $row['enrollment_id'];

                // --- 1. ATTENDANCE CALCULATION ---
                // Get total sessions (total unique dates recorded for this course)
                $total_sessions_stmt = $conn->prepare("
                    SELECT COUNT(DISTINCT date) AS total_sessions
                    FROM attendance
                    WHERE enrollment_id = ?
                ");
                $total_sessions_stmt->bind_param("i", $enrollment_id);
                $total_sessions_stmt->execute();
                $total_sessions = $total_sessions_stmt->get_result()->fetch_assoc()['total_sessions'] ?? 0;
                $total_sessions_stmt->close();

                // Get present/late count (considered attended)
                $present_count_stmt = $conn->prepare("
                    SELECT COUNT(*) AS present_count
                    FROM attendance
                    WHERE enrollment_id = ? AND status IN ('Present', 'Late')
                ");
                $present_count_stmt->bind_param("i", $enrollment_id);
                $present_count_stmt->execute();
                $present_count = $present_count_stmt->get_result()->fetch_assoc()['present_count'] ?? 0;
                $present_count_stmt->close();
                
                // Calculate percentage
                $attendance_percent = ($total_sessions > 0) ? round(($present_count / $total_sessions) * 100, 1) : 0;
                $attendance_display = "{$present_count} / {$total_sessions} ({$attendance_percent}%)";
                // ------------------------------------


                // --- 2. RESULTS FETCHING ---
                $results_stmt = $conn->prepare("
                    SELECT exam_type, final_grade
                    FROM grades
                    WHERE enrollment_id = ?
                    ORDER BY exam_type
                ");
                $results_stmt->bind_param("i", $enrollment_id);
                $results_stmt->execute();
                $results_result = $results_stmt->get_result();
                $results_display = [];
                
                if ($results_result->num_rows > 0) {
                    while($grade_row = $results_result->fetch_assoc()){
                        $results_display[] = "{$grade_row['exam_type']}: **{$grade_row['final_grade']}**";
                    }
                } else {
                    $results_display[] = "No results recorded yet.";
                }
                $results_stmt->close();
                // ------------------------------------

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['course_name']) . " (" . htmlspecialchars($row['course_code']) . ")</td>";
                echo "<td>{$attendance_display}</td>";
                echo "<td>" . implode('<br>', $results_display) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>You are not currently enrolled in any courses.</p>";
        }
        ?>
    </div>
</body>
</html>