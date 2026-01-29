<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = "";     // Default password is empty
$dbname = "complaint_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>