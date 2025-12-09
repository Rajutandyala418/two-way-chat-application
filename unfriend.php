<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$me   = $_SESSION['username'];
$user = $_POST['user'];

// 1️⃣ DELETE all possible relations
$conn->query("
    DELETE FROM friend_requests 
    WHERE (sender='$me' AND receiver='$user') 
       OR (sender='$user' AND receiver='$me')
");

// 2️⃣ OPTIONAL (if you use a separate friends table)
$conn->query("
    DELETE FROM friends 
    WHERE (user1='$me' AND user2='$user') 
       OR (user1='$user' AND user2='$me')
");

echo "success";
