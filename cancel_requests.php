<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$sender = $_POST['sender'];     // logged in user
$receiver = $_POST['receiver']; // user they sent request to

$stmt = $conn->prepare("
    DELETE FROM friend_requests 
    WHERE sender=? AND receiver=?
");
$stmt->bind_param("ss", $sender, $receiver);
$stmt->execute();

echo "cancelled";
