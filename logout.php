<?php
session_start();
include('./include/db_connect.php');

if (isset($_COOKIE['remember_token'])) {
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['remember_token']);
    $stmt->execute();

    setcookie("remember_token", "", time() - 3600, "/");
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
