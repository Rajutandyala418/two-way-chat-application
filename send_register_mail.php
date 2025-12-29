<?php
require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$first = $_POST['first_name'];
$last = $_POST['last_name'];
$username = $_POST['username'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];

$mail = new PHPMailer(true);

try {
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
    $mail->Subject = "Welcome to Mini Chat App";
    $mail->Body = "
        <h2>Hello $first $last 👋</h2>
        <p>Your Mini Chat account has been successfully created.</p>
        <p><b>Username:</b> $username</p>
        <p><b>Phone:</b> $mobile</p>
        <p><b>Email:</b> $email</p>
        <br>
        <a href='http://localhost/y22cm171/Two_Way_Communication_chat_app/login.php' 
            style='background:#1e3c57;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;'>
            Login Now
        </a>
        <br><br>
        <p>Happy chatting! 💬</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // Silent error - no popup needed
}
