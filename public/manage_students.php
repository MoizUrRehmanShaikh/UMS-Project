<?php
session_start();
include '../config/db.php';

// Security Check: Must be logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}
// ... (Your existing code for teacher_id, course_id, message, and initial security check) ...
$teacher_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;
$message = '';

// Check if a course ID is provided
if (!$course_id || !is_numeric($course_id)) {
    header("Location: teacher_dashboard.php"); // Redirect if course is missing
    exit();
}

// 1. Verify Teacher is assigned to this course (Security Check)
$stmt = $conn->prepare("SELECT c.course_name FROM courses c JOIN course_assignments ca ON c.course_id = ca.course_id WHERE c.course_id = ? AND ca.teacher_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course_info) {
    $message = "<p class='error'>❌ You are not assigned to manage this course.</p>";
    $course_name = "Unauthorized Course";
} else {
    $course_name = $course_info['course_name'];
}

// --- ATTENDANCE SUBMISSION LOGIC (POST) (Existing Code) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attendance_date'])) {
    // ... (Your existing attendance submission logic goes here) ...
    $attendance_date = $_POST['attendance_date'];
    $attendances = $_POST['attendance'] ?? []; 
    // ... (rest of attendance submission logic) ...
    $conn->begin_transaction();
    $success_count = 0;
    $error_flag = false;
    
    foreach ($attendances as $enrollment_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (enrollment_id, date, status) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE status = VALUES(status)");
        $stmt->bind_param("iss", $enrollment_id, $attendance_date, $status);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_flag = true;
        }
        $stmt->close();
    }
    
    if ($error_flag) {
        $conn->rollback();
        $message = "<p class='error'>❌ Error saving some attendance records.</p>";
    } else {
        $conn->commit();
        $message = "<p class='success'>✅ Attendance saved successfully for $success_count students on $attendance_date.</p>";
    }
}


// --- GRADES SUBMISSION LOGIC (NEW) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['grade_action']) && $_POST['grade_action'] == 'submit_grades') {
    $exam_type = $_POST['exam_type'];
    $grades = $_POST['grades'] ?? []; // Array of [enrollment_id => final_grade]
    $date_recorded = date('Y-m-d');
    
    $conn->begin_transaction();
    $success_count = 0;
    $error_flag = false;

    foreach ($grades as $enrollment_id => $final_grade) {
        if (empty($final_grade)) continue; // Skip empty grades

        // Attempt to insert or update the grade record (UNIQUE KEY on enrollment_id, exam_type)
        $stmt = $conn->prepare("INSERT INTO grades (enrollment_id, exam_type, final_grade, date_recorded) VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE final_grade = VALUES(final_grade), date_recorded = VALUES(date_recorded)");
        $stmt->bind_param("isss", $enrollment_id, $exam_type, $final_grade, $date_recorded);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_flag = true;
        }
        $stmt->close();
    }
    
    if ($error_flag) {
        $conn->rollback();
        $message = "<p class='error'>❌ Error submitting some grades.</p>";
    } else {
        $conn->commit();
        $message = "<p class='success'>✅ Grades submitted successfully for $success_count students for $exam_type.</p>";
    }
}
// --------------------------------------------------------------------------------

// Fetch all students enrolled in this course (Needed for both forms)
$students_result = null;
if ($course_info) {
    $stmt = $conn->prepare("
        SELECT 
            s.name, s.registration_number, e.enrollment_id 
        FROM students s
        JOIN enrollment e ON s.student_id = e.student_id
        WHERE e.course_id = ?
        ORDER BY s.registration_number
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage <?php echo htmlspecialchars($course_name); ?></title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .attendance-status { display: flex; gap: 10px; }
        .attendance-status label { font-weight: normal; margin-right: 5px; }
        /* Style to separate the two main management forms */
        .management-forms { display: flex; flex-direction: column; gap: 30px; }
    </style>
</head>
<body>
    <div class="content">
        <h1>Manage Course: <?php echo htmlspecialchars($course_name); ?></h1>
        <p><a href="teacher_dashboard.php">← Back to Dashboard</a></p>
        
        <?php echo $message; ?>

        <?php if ($course_info && $students_result): ?>
            
            <div class="management-forms">
                
                <div>
                    <h2>Record Attendance</h2>
                    <form method="POST" id="attendance-form">
                        <label for="attendance_date">Date:</label>
                        <input type="date" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required><br><br>

                        <h3>Enrolled Students (<?php echo $students_result->num_rows; ?>)</h3>
                
                        <?php if ($students_result->num_rows > 0): 
                            $students_result->data_seek(0); // Rewind pointer for Attendance list
                        ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td>
                                                <?php 
                                                    $e_id = $student['enrollment_id'];
                                                    $name_prefix = "attendance[{$e_id}]"; 
                                                ?>
                                                <div class="attendance-status">
                                                    <input type="radio" id="p_<?php echo $e_id; ?>" name="<?php echo $name_prefix; ?>" value="Present" required>
                                                    <label for="p_<?php echo $e_id; ?>">Present</label>

                                                    <input type="radio" id="a_<?php echo $e_id; ?>" name="<?php echo $name_prefix; ?>" value="Absent">
                                                    <label for="a_<?php echo $e_id; ?>">Absent</label>

                                                    <input type="radio" id="l_<?php echo $e_id; ?>" name="<?php echo $name_prefix; ?>" value="Late">
                                                    <label for="l_<?php echo $e_id; ?>">Late</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <input type="submit" value="Save Attendance Records" style="margin-top: 20px;">
                        <?php else: ?>
                            <p>No students are currently enrolled in this course.</p>
                        <?php endif; ?>
                    </form>
                </div> <hr>

                <div>
                    <h2>Submit Exam Results</h2>
                    <form method="POST">
                        <input type="hidden" name="grade_action" value="submit_grades">

                        <label for="exam_type">Exam/Assignment Type:</label><br>
                        <select name="exam_type" id="exam_type" required>
                            <option value="">-- Select Exam Type --</option>
                            <option value="Midterm">Midterm Exam</option>
                            <option value="Final">Final Exam</option>
                            <option value="Assignment 1">Assignment 1</option>
                            <option value="Quiz 1">Quiz 1</option>
                        </select><br><br>

                        <h3>Enter Grades (A+, B, F, etc.)</h3>
                        <?php if ($students_result->num_rows > 0): 
                            $students_result->data_seek(0); // Rewind pointer for Grades list
                        ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Student Name</th>
                                        <th>Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td>
                                                <input type="text" name="grades[<?php echo $student['enrollment_id']; ?>]" 
                                                       maxlength="5" placeholder="e.g., A+ or 85" style="width: 100px;">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <input type="submit" value="Submit Grades" style="margin-top: 20px;">
                        <?php else: ?>
                            <p>No students are currently enrolled in this course.</p>
                        <?php endif; ?>
                    </form>
                </div> </div> <?php endif; ?>
    </div>
</body>
</html>