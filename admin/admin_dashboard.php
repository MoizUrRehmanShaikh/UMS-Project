<?php
session_start();
include '../config/db.php';

// Strict Security check: must be logged in as ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$section = $_GET['section'] ?? '';

// --- CRUD LOGIC FOR COURSES, USERS, AND ASSIGNMENTS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? null;

    // --- Course CRUD Logic ---
    if (in_array($action, ['add_course', 'update_course', 'delete_course'])) {
        $course_code = $_POST['course_code'] ?? '';
        $course_name = $_POST['course_name'] ?? '';
        $course_id = $_POST['course_id'] ?? null;
        
        if ($action == 'add_course') {
            $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $course_code, $course_name);
            if ($stmt->execute()) { $message = "<p class='success'>✅ Course added successfully!</p>"; } 
            else { $message = "<p class='error'>❌ Error adding course: Code likely already exists.</p>"; }
            $stmt->close();
            $section = 'manage_courses';
        } elseif ($action == 'update_course') {
            $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ? WHERE course_id = ?");
            $stmt->bind_param("ssi", $course_code, $course_name, $course_id);
            if ($stmt->execute()) { $message = "<p class='success'>✅ Course updated successfully!</p>"; } 
            else { $message = "<p class='error'>❌ Error updating course.</p>"; }
            $stmt->close();
            $section = 'manage_courses';
        } elseif ($action == 'delete_course') {
            $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            if ($stmt->execute()) { $message = "<p class='success'>✅ Course deleted successfully!</p>"; } 
            else { $message = "<p class='error'>❌ Error deleting course.</p>"; }
            $stmt->close();
            $section = 'manage_courses';
        }
    } 
    
    // --- USER DELETE Logic ---
    elseif ($action == 'delete_user' && $id && $type) {
        if ($type == 'student') {
            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { $message = "<p class='success'>✅ Student account deleted.</p>"; }
            else { $message = "<p class='error'>❌ Error deleting student.</p>"; }
        } elseif ($type == 'teacher') {
            $stmt = $conn->prepare("DELETE FROM teachers WHERE teacher_id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { $message = "<p class='success'>✅ Teacher account deleted.</p>"; }
            else { $message = "<p class='error'>❌ Error deleting teacher.</p>"; }
        }
        $stmt->close();
        $section = 'manage_users';
    }

    // --- ASSIGNMENT LOGIC ---
    elseif ($action == 'assign_teacher') {
        $teacher_id = $_POST['teacher_id'];
        $course_id = $_POST['course_id'];
        
        $stmt = $conn->prepare("INSERT INTO course_assignments (course_id, teacher_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE course_id=course_id");
        $stmt->bind_param("ii", $course_id, $teacher_id);
        
        if ($stmt->execute()) {
             if ($stmt->affected_rows > 0) {
                 $message = "<p class='success'>✅ Course assigned to teacher successfully!</p>";
             } else {
                 $message = "<p class='error'>⚠️ Assignment already exists.</p>";
             }
        } else {
            $message = "<p class='error'>❌ Error assigning course: " . $conn->error . "</p>";
        }
        $stmt->close();
        $section = 'assign_courses';

    } elseif ($action == 'enroll_student') {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];

        $stmt = $conn->prepare("INSERT INTO enrollment (student_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE student_id=student_id");
        $stmt->bind_param("ii", $student_id, $course_id);

        if ($stmt->execute()) {
             if ($stmt->affected_rows > 0) {
                 $message = "<p class='success'>✅ Student enrolled successfully!</p>";
             } else {
                 $message = "<p class='error'>⚠️ Student already enrolled in this course.</p>";
             }
        } else {
            $message = "<p class='error'>❌ Error enrolling student: " . $conn->error . "</p>";
        }
        $stmt->close();
        $section = 'assign_courses';
    }
}
// ------------------------------

// Fetch general counts for the dashboard view
$student_count = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$teacher_count = $conn->query("SELECT COUNT(*) FROM teachers")->fetch_row()[0];
$course_count = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];

// Fetch data required for ALL assignment forms (MUST BE DONE BEFORE HTML/SECTION VIEWS)
$all_teachers = $conn->query("SELECT teacher_id, name FROM teachers ORDER BY name");
$all_students = $conn->query("SELECT student_id, name, registration_number FROM students ORDER BY name");
$all_courses = $conn->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .user-table-container { margin-top: 20px; }
        .assignment-forms { display: flex; justify-content: space-between; gap: 30px; }
        .assignment-forms > div { flex: 1; padding: 20px; border: 1px solid #ccc; border-radius: 6px; }
        .full-width-table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="../index.php">Home</a>
        <a href="admin_dashboard.php">Admin Panel</a>
        <div class="right">
            <a href="../logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
        </div>
    </div>
    
    <div class="content">
        <h1>Admin Panel - Dashboard</h1>
        
        <?php echo $message; // Display success or error messages ?>

        <div class="system-overview">
            <h2>System Overview</h2>
            <p>Total Registered Students: <strong><?php echo $student_count; ?></strong></p>
            <p>Total Registered Teachers: <strong><?php echo $teacher_count; ?></strong></p>
            <p>Total Active Courses: <strong><?php echo $course_count; ?></strong></p>
        </div>

        <div class="management-tools">
            <h2>Management Tools</h2>
            <ul>
                <li><a href="?section=manage_courses">Manage Courses (Add, Edit, Delete)</a></li>
                <li><a href="?section=manage_users">Manage User Accounts (Students & Teachers)</a></li>
                <li><a href="?section=assign_courses">Assign Courses to Teachers & Enroll Students</a></li>
            </ul>
        </div>
        
        <hr>
        
        <div class="management-area">
        <?php
        // --- SECTION VIEWS ---
        
        // --- 1. MANAGE COURSES ---
        if ($section == 'manage_courses') {
            
            // Re-fetch courses here because the POST logic might have changed them
            $courses_result = $conn->query("SELECT * FROM courses ORDER BY course_code");

            // ----------------------------------------------------
            // FORM FOR ADDING/EDITING COURSES
            // ----------------------------------------------------
            $edit_course = null;
            if (isset($_GET['edit_id'])) {
                $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
                $stmt->bind_param("i", $_GET['edit_id']);
                $stmt->execute();
                $edit_course = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            ?>
            <h3><?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_course ? 'update_course' : 'add_course'; ?>">
                <?php if ($edit_course): ?>
                    <input type="hidden" name="course_id" value="<?php echo $edit_course['course_id']; ?>">
                <?php endif; ?>

                <label for="course_code">Course Code (e.g., CS101):</label><br>
                <input type="text" id="course_code" name="course_code" required value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_code']) : ''; ?>"><br><br>

                <label for="course_name">Course Name:</label><br>
                <input type="text" id="course_name" name="course_name" required value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_name']) : ''; ?>"><br><br>

                <input type="submit" value="<?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>">
                <?php if ($edit_course): ?><a href="?section=manage_courses" style="margin-left: 10px;">Cancel Edit</a><?php endif; ?>
            </form>
            <hr>
            
            <?php
            // ----------------------------------------------------
            // LIST ALL COURSES
            // ----------------------------------------------------
            echo "<h3>Current Courses</h3>";

            if ($courses_result->num_rows > 0) {
                echo "<table class='full-width-table'><thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Actions</th></tr></thead><tbody>";
                while ($row = $courses_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['course_id']}</td>";
                    echo "<td>{$row['course_code']}</td>";
                    echo "<td>{$row['course_name']}</td>";
                    echo "<td>";
                    echo "<a href='?section=manage_courses&edit_id={$row['course_id']}'>Edit</a> | ";
                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this course?');\">";
                    echo "<input type='hidden' name='action' value='delete_course'>";
                    echo "<input type='hidden' name='course_id' value='{$row['course_id']}'>";
                    echo "<input type='submit' value='Delete' style='background: none; color: red; padding: 0; margin: 0; cursor: pointer; border: none;'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No courses have been added yet.</p>";
            }


        // --- 2. MANAGE USERS ---
        } elseif ($section == 'manage_users') {
            
            echo "<h3>Manage User Accounts (Students & Teachers)</h3>";
            
            // Re-fetch users here
            $students_result = $conn->query("SELECT * FROM students ORDER BY registration_number");
            $teachers_result = $conn->query("SELECT * FROM teachers ORDER BY name");

            // --- STUDENTS LIST ---
            echo "<div class='user-table-container'>";
            echo "<h4>Registered Students</h4>";
            
            if ($students_result->num_rows > 0) {
                echo "<table class='full-width-table'><thead><tr><th>ID</th><th>Name</th><th>Reg No</th><th>Dept</th><th>Email</th><th>Actions</th></tr></thead><tbody>";
                while ($row = $students_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['student_id']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['registration_number']}</td>";
                    echo "<td>{$row['department']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete Student {$row['name']}?');\">";
                    echo "<input type='hidden' name='action' value='delete_user'>";
                    echo "<input type='hidden' name='type' value='student'>";
                    echo "<input type='hidden' name='id' value='{$row['student_id']}'>";
                    echo "<input type='submit' value='Delete' style='background: none; color: red; padding: 0; margin: 0; cursor: pointer; border: none;'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No students have registered yet.</p>";
            }
            echo "</div>"; // end student container

            // --- TEACHERS LIST ---
            echo "<div class='user-table-container'>";
            echo "<h4>Registered Teachers</h4>";
            
            if ($teachers_result->num_rows > 0) {
                echo "<table class='full-width-table'><thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Actions</th></tr></thead><tbody>";
                while ($row = $teachers_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['teacher_id']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete Teacher {$row['name']}?');\">";
                    echo "<input type='hidden' name='action' value='delete_user'>";
                    echo "<input type='hidden' name='type' value='teacher'>";
                    echo "<input type='hidden' name='id' value='{$row['teacher_id']}'>";
                    echo "<input type='submit' value='Delete' style='background: none; color: red; padding: 0; margin: 0; cursor: pointer; border: none;'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No teachers have registered yet.</p>";
            }
            echo "</div>"; // end teacher container


        // --- 3. ASSIGN COURSES ---
        } elseif ($section == 'assign_courses') {
            ?>
            <h3>Assign Courses and Enroll Students</h3>
            <div class="assignment-forms">
                
                <div>
                    <h4>Assign Course to Teacher</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign_teacher">

                        <label for="teacher_id">Select Teacher:</label><br>
                        <select name="teacher_id" id="teacher_id" required>
                            <option value="">-- Select Teacher --</option>
                            <?php 
                            // Ensure data pointer is reset for each loop
                            $all_teachers->data_seek(0);
                            while ($teacher = $all_teachers->fetch_assoc()): ?>
                                <option value="<?php echo $teacher['teacher_id']; ?>">
                                    <?php echo htmlspecialchars($teacher['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br><br>

                        <label for="course_id_teacher">Select Course:</label><br>
                        <select name="course_id" id="course_id_teacher" required>
                            <option value="">-- Select Course --</option>
                            <?php 
                            $all_courses->data_seek(0);
                            while ($course = $all_courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br><br>

                        <input type="submit" value="Assign Course">
                    </form>
                </div>

                <div>
                    <h4>Enroll Student in Course</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="enroll_student">

                        <label for="student_id">Select Student:</label><br>
                        <select name="student_id" id="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php 
                            $all_students->data_seek(0);
                            while ($student = $all_students->fetch_assoc()): ?>
                                <option value="<?php echo $student['student_id']; ?>">
                                    <?php echo htmlspecialchars($student['registration_number'] . ' - ' . $student['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br><br>

                        <label for="course_id_student">Select Course:</label><br>
                        <select name="course_id" id="course_id_student" required>
                            <option value="">-- Select Course --</option>
                            <?php 
                            $all_courses->data_seek(0);
                            while ($course = $all_courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br><br>

                        <input type="submit" value="Enroll Student">
                    </form>
                </div>
            </div> <hr>
            <h4>Current Assignments/Enrollments (Optional next task: display here)</h4>
            <p>You can add a section here to display existing assignments and enrollments with delete options.</p>

            <?php
        } else {
            echo "<p>Select a management task from the links above to begin.</p>";
        }
        // --- END SECTION VIEWS ---
        ?>
        </div>
    </div>
</body>
</html>