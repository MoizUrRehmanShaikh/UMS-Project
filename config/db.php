<?php
// config/db.php

// Database connection parameters
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "tandojam_ums"; // Your existing database name
$port = 3307; // Your custom port

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    // Stop execution if connection fails
    die("Connection failed: " . $conn->connect_error);
}

// The $conn object is now available to any file that includes this script.
?>