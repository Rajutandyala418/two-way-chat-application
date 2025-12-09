<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$me   = $_SESSION['username'];
$from = $_POST['user'] ?? '';

if($from == '') exit;

// When chat opened → mark all their sent messages as read
$conn->query("
    UPDATE messages 
    SET is_read = 1, delivered = 1
    WHERE sender = '$from' AND receiver = '$me' AND is_read = 0
");

echo "ok";
