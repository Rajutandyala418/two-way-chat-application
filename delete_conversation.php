<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
if(!isset($_SESSION['user_id'])) exit;

include(__DIR__.'/include/db_connect.php');

$me   = $_SESSION['username'];
$user = $_POST['user'] ?? '';

if($user == '') exit;

$stmt = $conn->prepare("
    DELETE FROM messages 
    WHERE (sender = ? AND receiver = ?) 
       OR (sender = ? AND receiver = ?)
");
$stmt->bind_param("ssss", $me, $user, $user, $me);
$stmt->execute();

echo "ok";
