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

// --- CRUD LOGIC FOR COURSES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $course_code = $_POST['course_code'] ?? '';
    $course_name = $_POST['course_name'] ?? '';
    $course_id = $_POST['course_id'] ?? null;

    if ($action == 'add_course') {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_code, $course_name);
        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Course added successfully!</p>";
        } else {
            $message = "<p class='error'>❌ Error adding course: Code likely already exists.</p>";
        }
        $stmt->close();
        $section = 'manage_courses'; // Stay on the management page

    } elseif ($action == 'update_course') {
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ? WHERE course_id = ?");
        $stmt->bind_param("ssi", $course_code, $course_name, $course_id);
        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Course updated successfully!</p>";
        } else {
            $message = "<p class='error'>❌ Error updating course.</p>";
        }
        $stmt->close();
        $section = 'manage_courses';

    } elseif ($action == 'delete_course') {
        // Deleting a course will cascade delete assignments and enrollments (if table structure is correct)
        $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Course deleted successfully!</p>";
        } else {
            $message = "<p class='error'>❌ Error deleting course.</p>";
        }
        $stmt->close();
        $section = 'manage_courses';
    }
}
// ------------------------------

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
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
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
        if ($section == 'manage_courses') {
            
            // ----------------------------------------------------
            // 1. FORM FOR ADDING/EDITING COURSES
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
            // 2. LIST ALL COURSES
            // ----------------------------------------------------
            echo "<h3>Current Courses</h3>";
            $courses_result = $conn->query("SELECT * FROM courses ORDER BY course_code");

            if ($courses_result->num_rows > 0) {
                echo "<table><thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Actions</th></tr></thead><tbody>";
                while ($row = $courses_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['course_id']}</td>";
                    echo "<td>{$row['course_code']}</td>";
                    echo "<td>{$row['course_name']}</td>";
                    echo "<td>";
                    echo "<a href='?section=manage_courses&edit_id={$row['course_id']}'>Edit</a> | ";
                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this course? This will remove all related assignments and enrollments.');\">";
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


        } elseif ($section == 'manage_users') {
            echo "<h3>Manage Students and Teachers</h3>";
            echo "<p>This section will list students and teachers with options to edit/delete their accounts.</p>";
        } elseif ($section == 'assign_courses') {
            echo "<h3>Assign Courses</h3>";
            echo "<p>This section will contain forms to link Teachers to Courses and Students to Courses.</p>";
        } else {
            echo "<p>Select a management task from the links above to begin.</p>";
        }
        // --- END SECTION VIEWS ---
        ?>
        </div>
    </div>
</body>
</html>