<?php
session_start();
include 'config/db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Role selected in the form

    // Check if user exists and role matches
    $stmt = $conn->prepare("SELECT user_id, password_hash, role, username FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password hash
        if (password_verify($password, $user['password_hash'])) {
            // Login successful: Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to appropriate dashboard
            if ($user['role'] == 'teacher') {
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