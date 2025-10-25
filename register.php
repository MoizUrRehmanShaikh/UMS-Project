<?php
session_start();
include 'config/db.php'; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect common fields
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_raw = $_POST['password'];
    $role = $_POST['role'];
    $name = $_POST['name'];
    
    // Collect specific student fields ONLY if role is student
    $reg_num = ($role == 'student' && isset($_POST['reg_num'])) ? $_POST['reg_num'] : null;
    $department = ($role == 'student' && isset($_POST['dept'])) ? $_POST['dept'] : null;
    $year = ($role == 'student' && isset($_POST['year'])) ? $_POST['year'] : null;
    
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
        // 1. Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password_hash, $email, $role);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();

        // 2. Insert into specific role table
        if ($role == 'teacher') {
            $stmt = $conn->prepare("INSERT INTO teachers (teacher_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $name);
            $stmt->execute();
            $stmt->close();
            $message = "Teacher account created successfully! Please log in.";
        } elseif ($role == 'student') {
            // UPDATED SQL: Includes registration_number, department, and year
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, registration_number, department, year) VALUES (?, ?, ?, ?, ?)");
            
            // UPDATED bind_param: i(ID), s(Name), s(Reg Num), s(Dept), s(Year)
            $stmt->bind_param("issss", $user_id, $name, $reg_num, $department, $year);
            $stmt->execute();
            $stmt->close();
            $message = "Student account created successfully! Registration No: $reg_num. Please log in.";
        } else {
            throw new Exception("Invalid role selected.");
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        
        // Check for duplicate entry error (username, email, or registration_number)
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $message = "Error: Username, Email, or Registration Number already exists.";
        } else {
            // Display error for debugging only, usually $message should be generic
            $message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Tando Jam University</title>
   <link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/forms.css">
</head>
<body>
    <div class="content">
        <h2>Register New Account</h2>
        <?php if ($message) echo "<p style='color: green;'>$message</p>"; ?>
        
        <form method="POST">
            
            <label for="name">Full Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>
            
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="email">Email (Gmail):</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="role">Register As:</label><br>
            <select id="role" name="role" required onchange="toggleStudentFields()">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select><br><br>

            <div id="studentFields" class="student-fields">
                <h3>Student Details</h3>
                <label for="reg_num">Registration Number:</label><br>
                <input type="text" id="reg_num" name="reg_num"><br><br>

                <label for="dept">Department:</label><br>
                <input type="text" id="dept" name="dept"><br><br>

                <label for="year">Academic Year (e.g., 2nd Year):</label><br>
                <input type="text" id="year" name="year"><br><br>
            </div>
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        function toggleStudentFields() {
            const roleSelect = document.getElementById('role');
            const studentFieldsDiv = document.getElementById('studentFields');
            const isStudent = roleSelect.value === 'student';

            // Toggle visibility of the fields
            studentFieldsDiv.style.display = isStudent ? 'block' : 'none';

            // Set 'required' attribute dynamically
            document.getElementById('reg_num').required = isStudent;
            document.getElementById('dept').required = isStudent;
            document.getElementById('year').required = isStudent;
        }

        // Call on page load to set initial state based on default selection
        document.addEventListener('DOMContentLoaded', toggleStudentFields);
    </script>
</body>
</html>