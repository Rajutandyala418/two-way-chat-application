<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$username = $_GET['username'];

$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$stmt = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE username=?");
$stmt->bind_param("sss", $otp, $expiry, $username);
$stmt->execute();

$stmt2 = $conn->prepare("SELECT email FROM users WHERE username=?");
$stmt2->bind_param("s", $username);
$stmt2->execute();
$email = $stmt2->get_result()->fetch_assoc()['email'];

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'minichatwebapp@gmail.com';
$mail->Password = 'zeor ksda atmz bzla';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('minichatwebapp@gmail.com', 'Mini Chat App');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = "New OTP – Mini Chat App";
$mail->Body = "<h2>Your new OTP is: $otp</h2><p>Valid for 10 minutes.</p>";

if ($mail->send()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "fail"]);
}
