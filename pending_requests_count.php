<?php
session_start();
if(!isset($_SESSION['user_id'])){ exit; }

include(__DIR__.'/include/db_connect.php');

$me = $_SESSION['username'];

$stmt = $conn->prepare("
    SELECT COUNT(*) AS pending_count 
    FROM friend_requests 
    WHERE receiver=? AND status='pending'
");
$stmt->bind_param("s", $me);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

echo (int)$res['pending_count'];
