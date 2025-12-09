<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['username'])) {
    die("Invalid Access");
}

$username = $_GET['username'];

$stmt = $conn->prepare("SELECT email, first_name FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
$email = $user['email'];
$first_name = $user['first_name'];

$otp = rand(100000, 999999);
$expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE username = ?");
$update->bind_param("sss", $otp, $expiry_time, $username);
$update->execute();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'minichatwebapp@gmail.com';
    $mail->Password   = 'zeor ksda atmz bzla';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('minichatwebapp@gmail.com', 'Mini Chat App');
    $mail->addAddress($email, $first_name);

    $mail->isHTML(true);
    $mail->Subject = "Your OTP Code – Mini Chat App";
    $mail->Body    = "
        <p>Hello <b>$first_name</b>,</p>
        <p>Your password reset OTP is:</p>
        <h2 style='letter-spacing:3px;color:#1e3c57;'>$otp</h2>
        <p>This OTP will expire in <b>10 minutes</b>.</p>
        <br>
        <p>Regards,<br><b>Mini Chat Support Team</b></p>
    ";

    $mail->send();
    $send_status = "success";

} catch (Exception $e) {
    $send_status = "mail_error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sending OTP…</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{
    background:#e8f0f7;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:'Poppins',sans-serif;
    text-align:center;
}
.loader{
    width:65px;
    height:65px;
    border:7px solid #d9d9d9;
    border-top:7px solid #1e3c57;
    border-radius:50%;
    animation:spin 1s linear infinite;
    margin:auto;
}
@keyframes spin{100%{transform:rotate(360deg);}}
.msg{
    margin-top:15px;
    font-size:18px;
    color:#1e3c57;
    font-weight:600;
}
.overlay{
    width:100%;
    height:100%;
    background:rgba(255,255,255,0.75);
    backdrop-filter:blur(2px);
    position:fixed;
    top:0;
    left:0;
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
}
@media(max-width:480px){
    .msg{font-size:16px;}
    .loader{width:55px;height:55px;border-width:6px;}
}
</style>
</head>
<body>

<div class="overlay">
    <div>
        <div class="loader"></div>
        <p class="msg">Sending OTP to your registered email...</p>
    </div>
</div>

<script>
setTimeout(function(){
    <?php if($send_status === "success"): ?>
        window.location.href = "verify_otp.php?username=<?php echo $username; ?>";
    <?php else: ?>
        alert("OTP sending failed. Try again.");
        window.location.href = "forgot_password.php";
    <?php endif; ?>
}, 3000);
</script>

</body>
</html>
