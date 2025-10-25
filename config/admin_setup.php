<?php
// This file is used to set up the default Admin account.
// DELETE IT IMMEDIATELY AFTER RUNNING!

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "tandojam_ums";
$port = 3307; // Your custom port

// Create connection
// Suppress error reporting here so we can handle it gracefully
$conn = @new mysqli($host, $db_user, $db_pass, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: Could not connect to database. Check credentials/port. Error: " . $conn->connect_error);
}

// --- Admin Credentials ---
$admin_username = 'moiz';
$admin_email = 'admin@tandojam.edu';
$admin_password_raw = 'moiz123';
$admin_password_hash = password_hash($admin_password_raw, PASSWORD_DEFAULT);
$role = 'admin';

// Insert or Update the Admin user
// ON DUPLICATE KEY UPDATE ensures it works even if you run it twice
$stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("ssss", $admin_username, $admin_password_hash, $admin_email, $role);

if ($stmt->execute()) {
    echo "✅ Admin user setup completed successfully!<br>";
    echo "Username: <strong>moiz</strong><br>";
    echo "Password: <strong>moiz123</strong><br><br>";
    echo "🔴 **ACTION REQUIRED:** DELETE this file (config/admin_setup.php) immediately for security!";
} else {
    echo "❌ Error setting up admin user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>