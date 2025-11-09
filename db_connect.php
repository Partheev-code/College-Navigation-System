<?php
$host = 'localhost';
$db = 'college_nav';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}
$conn->set_charset('utf8mb4');
?>