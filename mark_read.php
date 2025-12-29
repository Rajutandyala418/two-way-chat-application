<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
session_start();
session_write_close(); 
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');
session_write_close();

$from = $_POST['user'] ?? '';
$to   = $_SESSION['username'] ?? '';

if($from === '' || $to === '') exit;

$stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1,
        is_seen = 1
    WHERE sender = ?
      AND receiver = ?
      AND is_seen = 0
");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
?>
