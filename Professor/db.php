<?php
// Database configuration
$host = "localhost"; // Hostname (XAMPP uses localhost)
$user = "root";      // Default XAMPP user
$password = "";      // Default XAMPP password (usually empty)
$dbname = "project";  // Database name

// Create a connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

