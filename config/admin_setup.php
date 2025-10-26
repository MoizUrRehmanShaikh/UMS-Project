<?php
// config/admin_setup.php (DELETE AFTER FIRST RUN!)
// This file assumes config/db.php is in the same directory.
include 'db.php';

// --- Admin Credentials ---
$admin_username = 'moiz';
$admin_password_raw = 'moiz123';
$admin_password_hash = password_hash($admin_password_raw, PASSWORD_DEFAULT);
$admin_name = 'Moiz Admin'; // Using 'Moiz Admin' for the full_name field

// Insert or Update the Admin user directly into the 'admins' table
$stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name) VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), full_name = VALUES(full_name)");

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// 'sss' for three string parameters
$stmt->bind_param("sss", $admin_username, $admin_password_hash, $admin_name);

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