<?php
session_start();
include('./include/db_connect.php');

$me = $_SESSION['username'];
$action = $_POST['action'];
$user = $_POST['user'];

if($action == "approve"){
    $stmt = $conn->prepare("UPDATE friend_requests SET status='accepted' 
                             WHERE sender=? AND receiver=?");
    $stmt->bind_param("ss", $user, $me);
    $stmt->execute();
    echo "approved";
}

if($action == "reject"){
    $stmt = $conn->prepare("UPDATE friend_requests SET status='rejected' 
                             WHERE sender=? AND receiver=?");
    $stmt->bind_param("ss", $user, $me);
    $stmt->execute();
    echo "rejected";
}
