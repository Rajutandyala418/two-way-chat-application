<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
session_start();
session_write_close(); 
include "include/db_connect.php";

if(!isset($_SESSION['username'])) exit;

$user = $_SESSION['username'];

$conn->query("
UPDATE users 
SET online_status = 1,
last_seen = NOW()
WHERE username = '$user'
");
?>
