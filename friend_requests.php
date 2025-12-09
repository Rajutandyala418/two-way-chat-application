<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit('login_required');
}
include(__DIR__ . '/include/db_connect.php');

$sender   = $_SESSION['username'];
$receiver = $_POST['to_user'];

if($receiver == "" || $receiver == $sender){
    exit("invalid");
}

// check if already requested / friends
$chk = $conn->prepare("
    SELECT status FROM friend_requests
    WHERE (sender=? AND receiver=?)
       OR (sender=? AND receiver=?)
    ORDER BY id DESC LIMIT 1
");
$chk->bind_param("ssss", $sender, $receiver, $receiver, $sender);
$chk->execute();
$exists = $chk->get_result()->fetch_assoc();

if($exists){
    if($exists['status']=='pending'){
        exit("already_requested");
    }
    if($exists['status']=='accepted'){
        exit("already_friends");
    }
}

// insert new request
$q = $conn->prepare("INSERT INTO friend_requests (sender, receiver, status) VALUES (?, ?, 'pending')");
$q->bind_param("ss", $sender, $receiver);
$q->execute();

echo "ok";
