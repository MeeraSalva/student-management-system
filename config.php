<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function clean($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}
?>