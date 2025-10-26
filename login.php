<?php
session_start();
include 'config/db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Role selected in the form: 'student' or 'teacher'
    
    $table_name = ($role == 'teacher') ? 'teachers' : 'students';
    $id_field = ($role == 'teacher') ? 'teacher_id' : 'student_id';

    // 1. Check for Student or Teacher credentials
    // Note: We use the specific table name determined by $role
    $stmt = $conn->prepare("SELECT {$id_field}, password_hash, username FROM {$table_name} WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verify password hash
        if (password_verify($password, $user['password_hash'])) {
            // Login successful: Set session variables
            $_SESSION['user_id'] = $user[$id_field]; // Use teacher_id or student_id
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $role; // 'teacher' or 'student'

            // Redirect to appropriate dashboard
            if ($role == 'teacher') {
                header("Location: public/teacher_dashboard.php");
            } else { // student
                header("Location: public/student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or role mismatch.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Tando Jam University</title>
    <link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/forms.css">
</head>
<body>
    <div class="content">
        <h2>Login</h2>
        <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
        
        <form method="POST">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="role">Login As:</label><br>
            <select id="role" name="role" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select><br><br>

            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>