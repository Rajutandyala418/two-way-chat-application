<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "mini_chat_app";
$conn = new mysqli($servername, $username, $password, $dbname);
date_default_timezone_set('Asia/Kolkata');
$conn->query("SET time_zone = '+05:30'");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
