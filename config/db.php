<?php
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "tandojam_ums";
$port = 3307; // Your custom port

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin Setup (Run once in your browser: http://localhost/ums_project/config/admin_setup.php)
// This file will be deleted or disabled after first use for security.
// Hashed password for 'moiz123'
$admin_username = 'moiz';
$admin_email = 'admin@tandojam.edu';
$admin_password_raw = 'moiz123';
$admin_password_hash = password_hash($admin_password_raw, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
$role = 'admin';
$stmt->bind_param("ssss", $admin_username, $admin_password_hash, $admin_email, $role);
$stmt->execute();
$stmt->close();
// echo "Admin user setup completed. Delete this file immediately!"; 
?>