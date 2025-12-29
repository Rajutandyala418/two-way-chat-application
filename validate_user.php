<?php
include('./include/db_connect.php');

$username = $_POST['username'] ?? '';
$mobile   = $_POST['mobile'] ?? '';
$email    = $_POST['email'] ?? '';

$response = [
    "username" => "",
    "mobile"   => "",
    "email"    => ""
];

// Validate username
if(!empty($username)){
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $response["username"] = "⚠ Username already exists.";
    }
}

// Validate mobile
if(!empty($mobile)){
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile = ? LIMIT 1");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $response["mobile"] = "⚠ Phone number already registered.";
    }
}

// Validate email
if(!empty($email)){
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $response["email"] = "⚠ Email already registered.";
    }
}

echo json_encode($response);
?>
