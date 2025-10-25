<?php
session_start();
include '../config/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check specifically for ADMIN role
    $stmt = $conn->prepare("SELECT user_id, password_hash, username FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            // Admin Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php"); // Redirect to admin panel
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username or role.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/dashboard.css">
    </head>
<body>
    <div style="text-align: center; padding-top: 50px;">
        <h2>Tando Jam University Admin Panel Login</h2>
        <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
        
        <form method="POST">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <input type="submit" value="Login as Admin">
        </form>
    </div>
</body>
</html>