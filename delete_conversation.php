<?php
session_start();
if(!isset($_SESSION['username'])) exit;

include(__DIR__.'/include/db_connect.php');

$me   = $_SESSION['username'];
$user = $_POST['user'] ?? '';

if(!$user) exit;

$stmt = $conn->prepare("
    DELETE FROM messages 
    WHERE (sender = ? AND receiver = ?)
       OR (sender = ? AND receiver = ?)
");
$stmt->bind_param("ssss", $me, $user, $user, $me);
$stmt->execute();

echo "ok";
