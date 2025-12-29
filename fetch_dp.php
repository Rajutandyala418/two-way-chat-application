<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__.'/include/db_connect.php');

$username = trim($_POST['username']);
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($profile);
$stmt->fetch();

echo (!empty($profile)) ? 'uploads/'.$profile : '';
