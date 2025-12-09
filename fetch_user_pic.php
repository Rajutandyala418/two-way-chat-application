<?php
include('./include/db_connect.php');

$username = $_POST['username'];

$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If profile exists return path; else return blank
echo !empty($user['profile_pic']) ? "uploads/".$user['profile_pic'] : "";
?>
