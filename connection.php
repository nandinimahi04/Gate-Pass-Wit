<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

$hostname = "localhost";
$username = "root";     
$password = "";          
$database = "wit_pass";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Something went wrong. Please try again later.");
}


mysqli_set_charset($conn, "utf8mb4");

?>
