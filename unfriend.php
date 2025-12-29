<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');

if (!isset($_SESSION['username'])) {
    echo "session_error";
    exit;
}

if (!isset($_POST['user']) || empty($_POST['user'])) {
    echo "no_user";
    exit;
}

$me   = $_SESSION['username'];
$user = $_POST['user'];

/* ===========================
  1️⃣ REMOVE FROM friend_requests
=========================== */
$stmt1 = $conn->prepare("
    DELETE FROM friend_requests
    WHERE (sender=? AND receiver=?)
       OR (sender=? AND receiver=?)
");
$stmt1->bind_param("ssss", $me, $user, $user, $me);
$stmt1->execute();

/* ===========================
  2️⃣ REMOVE FROM friends table
=========================== */
$stmt2 = $conn->prepare("
    DELETE FROM friends
    WHERE (user1=? AND user2=?)
       OR (user1=? AND user2=?)
");
$stmt2->bind_param("ssss", $me, $user, $user, $me);
$stmt2->execute();

/* ===========================
  Response
=========================== */

if ($stmt2->affected_rows > 0 || $stmt1->affected_rows > 0) {
    echo "success";
} else {
    echo "not_found";
}

?>
