<?php
include('./include/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $username   = trim($_POST['username']);
    $mobile     = trim($_POST['mobile']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);

    // Generate OTP & expiry
    $otp = rand(100000, 999999);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Encrypt password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into DB
    $stmt = $conn->prepare("
        INSERT INTO users 
        (first_name, last_name, username, mobile, email, password, otp, otp_expiry)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssss",
        $first_name,
        $last_name,
        $username,
        $mobile,
        $email,
        $hashedPassword,
        $otp,
        $otp_expiry
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed"]);
    }

    $stmt->close();
    $conn->close();
}
?>
