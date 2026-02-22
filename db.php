<?php
$host = "localhost";
$user = "root";  // change if different
$password = "root";  // XAMPP default is empty
$dbname = "velvet_vogue";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>