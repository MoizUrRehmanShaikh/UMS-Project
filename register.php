<?php
session_start();
include 'config/db.php'; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect common login fields
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_raw = $_POST['password'];
    $role = $_POST['role'];
    $name = $_POST['name'];
    
    // Collect specific student fields
    $reg_num = ($role == 'student' && isset($_POST['reg_num'])) ? $_POST['reg_num'] : null;
    $department = ($role == 'student' && isset($_POST['dept'])) ? $_POST['dept'] : null;
    $year = ($role == 'student' && isset($_POST['year'])) ? $_POST['year'] : null;
    
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
        // --- LOGIC IS NOW SIMPLIFIED: INSERT DIRECTLY INTO ROLE TABLE ---
        
        if ($role == 'teacher') {
            // Insert directly into the teachers table
            // Fields: username, password_hash, email, name
            $stmt = $conn->prepare("INSERT INTO teachers (username, password_hash, email, name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password_hash, $email, $name);
            $stmt->execute();
            $stmt->close();
            $message = "Teacher account created successfully! Please log in.";
            
        } elseif ($role == 'student') {
            // Insert directly into the students table
            // Fields: username, password_hash, email, name, registration_number, department, year
            $stmt = $conn->prepare("INSERT INTO students (username, password_hash, email, name, registration_number, department, year) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            // sssssss for 7 string parameters
            $stmt->bind_param("sssssss", $username, $password_hash, $email, $name, $reg_num, $department, $year);
            $stmt->execute();
            $stmt->close();
            $message = "Student account created successfully! Registration No: $reg_num. Please log in.";
            
        } else {
            throw new Exception("Invalid role selected.");
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $message = "Error: Username, Email, or Registration Number already exists.";
        } else {
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
    <style>
        .student-fields { display: none; } /* Initially hide student-specific fields */
    </style>
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