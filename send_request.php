<?php
session_start();
if(!isset($_SESSION['username'])){
    echo "login_required";
    exit;
}

include(__DIR__ . '/include/db_connect.php');

$sender = $_SESSION['username'];
$receiver = $_POST['user'];

if($sender === $receiver){
    echo "same_user";
    exit;
}

// check if already exists
$check = $conn->prepare("
    SELECT id,status FROM friend_requests
    WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?)
");
$check->bind_param("ssss", $sender, $receiver, $receiver, $sender);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    $row = $res->fetch_assoc();

    // if previously rejected → allow send again by updating
    if($row['status'] == 'rejected'){
        $conn->query("UPDATE friend_requests SET status='pending', sender='$sender', receiver='$receiver' WHERE id={$row['id']}");
        echo "resent";
        exit;
    }

    // if already pending or accepted → do nothing
    echo "already_exists";
    exit;
}

// INSERT REQUEST
$stmt = $conn->prepare("INSERT INTO friend_requests (sender, receiver, status) VALUES (?,?, 'pending')");
$stmt->bind_param("ss", $sender, $receiver);
$stmt->execute();

echo "sent";
