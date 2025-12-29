<?php
session_start();
include('./include/db_connect.php');

$user_id = $_SESSION['user_id'];
$new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
$stmt->bind_param("si", $new_pass, $user_id);
$stmt->execute();

echo "updated";
