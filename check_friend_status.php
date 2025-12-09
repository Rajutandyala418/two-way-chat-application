<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('login_required');
}

include(__DIR__ . '/include/db_connect.php');

$current = $_SESSION['username'];
$other   = isset($_POST['username']) ? trim($_POST['username']) : '';

if ($other === '' || $other === $current) {
    exit('none');
}

$stmt = $conn->prepare("
    SELECT sender, receiver, status 
    FROM friend_requests
    WHERE (sender = ? AND receiver = ?) 
       OR (sender = ? AND receiver = ?)
    ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("ssss", $current, $other, $other, $current);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc()) {
    echo 'none';
    exit;
}

// If any accepted row exists → they are friends
if ($row['status'] == 'accepted') {
    echo 'friends';
    exit;
}

// If there is a pending request sent by CURRENT user
if ($row['status'] == 'pending' && $row['sender'] == $current) {
    echo 'requested';
    exit;
}

// For anything else, treat as no relation (you may handle incoming later)
echo 'none';
