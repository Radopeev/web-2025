<?php
$host = 'localhost';
$user = 'root'; // or your MySQL username
$password = 'root'; // or your MySQL password
$dbname = 'project_manager'; // name of your database

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
