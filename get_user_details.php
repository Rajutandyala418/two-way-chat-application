<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');

header("Content-Type: application/json");

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

if (!isset($_POST['username']) || trim($_POST['username']) === "") {
    echo json_encode(["success" => false, "message" => "Username missing"]);
    exit;
}

$username = trim($_POST['username']);

// Fetch user details
$stmt = $conn->prepare("
    SELECT username, first_name, last_name, mobile, email, profile_pic
    FROM users
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

// Fix profile picture path
$pic = "";
if (!empty($user['profile_pic'])) {
    $fullPath = __DIR__ . "/uploads/" . $user['profile_pic'];

    if (file_exists($fullPath)) {
        $pic = "uploads/" . $user['profile_pic'];
    } else {
        $pic = ""; // fallback if file missing
    }
}

// Send response JSON
echo json_encode([
    "success" => true,
    "username" => $user['username'],
    "first_name" => $user['first_name'],
    "last_name" => $user['last_name'],
    "mobile" => $user['mobile'],
    "email" => $user['email'],
    "profile_pic" => $pic
]);
exit;
?>
