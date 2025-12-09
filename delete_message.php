<?php
session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) exit;

include(__DIR__.'/include/db_connect.php');

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$id       = (int)($_POST['id'] ?? 0);

if(!$id) exit;

// delete if sender matches username OR user_id
$stmt = $conn->prepare("
    DELETE FROM messages 
    WHERE id = ?
      AND (sender = ? OR sender = ?)
");
$stmt->bind_param("iss", $id, $username, $userId);
$stmt->execute();

if($stmt->affected_rows > 0){
    echo "ok";
} else {
    echo "failed";
}
