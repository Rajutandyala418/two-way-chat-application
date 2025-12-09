<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

if (!isset($_GET['username'])) {
    die("Invalid Access");
}

$username = $_GET['username'];

$stmt = $conn->prepare("SELECT otp, otp_expiry, email FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("User not found");
}

$user = $res->fetch_assoc();
$db_otp     = $user['otp'];
$db_expiry  = $user['otp_expiry'];
$email      = $user['email'];

$error_type = "";

if (isset($_POST['verify'])) {
    $entered_otp = trim($_POST['otp']);

    if (strtotime($db_expiry) < time()) {
        $error_type = "expired";
    } elseif ($entered_otp === $db_otp) {
        $_SESSION['otp_attempts'] = 0;
        header("Location: update_password1.php?username=$username");
        exit;
    } else {
        $_SESSION['otp_attempts']++;
        if ($_SESSION['otp_attempts'] >= 3) {
            $error_type = "block";
            $conn->query("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE username='$username'");
        } else {
            $error_type = "wrong";
        }
    }
}

if (isset($_POST['ajax_resend'])) {
    $otp = rand(100000, 999999);
    $expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $update = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE username=?");
    $update->bind_param("sss", $otp, $expiry_time, $username);
    $update->execute();

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
        $mail->Subject = "New OTP – Mini Chat App";
        $mail->Body = "<h2>Your new OTP is: $otp</h2><p>Valid for 10 minutes.</p>";
        $mail->send();

        $_SESSION['otp_attempts'] = 0;
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "fail"]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#e8f0f7;height:100vh;display:flex;justify-content:center;align-items:center;padding:15px;}
.container{
    background:#ffffff;border:1px solid #d7e0ea;padding:25px;border-radius:14px;
    width:100%;max-width:380px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
.container h2{font-weight:700;color:#1e3c57;margin-bottom:10px;}
.container p{font-size:14px;color:#455a70;margin-bottom:15px;}
input{
    width:100%;padding:12px;border-radius:8px;border:1px solid #b9c7d8;
    font-size:18px;text-align:center;letter-spacing:5px;font-weight:600;outline:none;
}
input:focus{border-color:#0072ff;}
.btn{
    width:100%;padding:12px;border-radius:8px;border:none;color:#fff;
    background:#1e3c57;margin-top:10px;font-size:16px;font-weight:600;cursor:pointer;
}
.btn:hover{background:#264a6e;}
.resend-btn{background:#0084ff;}
.resend-btn:hover{background:#006bc7;}
.resend-btn:disabled{background:#7baedc;cursor:not-allowed;}
.back-btn{
    position:fixed;top:18px;right:18px;background:#1e3c57;color:#fff;
    padding:8px 14px;border-radius:8px;font-size:14px;text-decoration:none;z-index:100;
}
.back-btn:hover{background:#264a6e;}
.popup{
    display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
    background:#fff;padding:25px 28px;text-align:center;border-radius:14px;
    box-shadow:0 4px 18px rgba(0,0,0,0.15);z-index:9999;min-width:260px;
}
.popup h3{margin-bottom:8px;font-size:18px;}
.popup p{font-size:14px;color:#555;}
.popup button{
    padding:8px 20px;margin-top:12px;border:none;background:#1e3c57;
    color:white;border-radius:8px;cursor:pointer;font-size:14px;
}
.popup button:hover{background:#264a6e;}
#overlayLoader{
    display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(255,255,255,0.75);backdrop-filter:blur(3px);
    z-index:9998;align-items:center;justify-content:center;
}
#overlayLoader .spinner{
    width:65px;height:65px;border-radius:50%;
    border:6px solid #dde3ea;border-top:6px solid #1e3c57;
    animation:spin 1s linear infinite;
}
@keyframes spin {100% { transform: rotate(360deg); }}
@media(max-width:420px){
    .container{max-width:92%;padding:20px;}
    input{font-size:16px;}
}
</style>
</head>
<body>

<a href="login.php" class="back-btn">Back to Login</a>

<div class="container">
<h2>Verify OTP</h2>
<p>Enter the 6-digit OTP sent to your registered email</p>

<form method="POST">
<input type="text" maxlength="6" name="otp" placeholder="Enter OTP" required autocomplete="off">
<button name="verify" class="btn" type="submit">Verify OTP</button>
</form>

<button type="button" class="btn resend-btn" id="resendBtn">Resend OTP</button>
</div>

<div id="overlayLoader">
    <div>
        <div class="spinner"></div>
    </div>
</div>

<div class="popup" id="wrongOTP">
<h3 style="color:red;">Incorrect OTP</h3>
<p>Please check and try again.</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="expiredOTP">
<h3 style="color:red;">OTP Expired</h3>
<p>Request a new OTP to continue.</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="blockedOTP">
<h3 style="color:red;">Too Many Attempts</h3>
<p>You entered wrong OTP 3 times. Please restart reset process.</p>
<button onclick="redirectLogin()">OK</button>
</div>

<div class="popup" id="resentOTP">
<h3 style="color:green;">New OTP Sent</h3>
<p>Check your email for the latest OTP.</p>
<button onclick="closePopup()">OK</button>
</div>

<script>
<?php if($error_type==="wrong") echo 'document.getElementById("wrongOTP").style.display="block";'; ?>
<?php if($error_type==="expired") echo 'document.getElementById("expiredOTP").style.display="block";'; ?>
<?php if($error_type==="block") echo 'document.getElementById("blockedOTP").style.display="block";'; ?>

function closePopup(){
    document.querySelectorAll('.popup').forEach(p=>p.style.display='none');
}

function redirectLogin(){
    window.location.href="login.php";
}

document.getElementById("resendBtn").addEventListener("click", function(){
    var btn = this;
    var overlay = document.getElementById("overlayLoader");
    overlay.style.display = "flex";
    btn.disabled = true;

    fetch("verify_otp.php?username=<?php echo $username; ?>", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "ajax_resend=1"
    })
    .then(res => res.json())
    .then(data => {
        overlay.style.display = "none";
        btn.disabled = false;
        if(data.status === "success"){
            document.getElementById("resentOTP").style.display = "block";
        } else {
            alert("Error sending OTP. Try again.");
        }
    })
    .catch(() => {
        overlay.style.display = "none";
        btn.disabled = false;
        alert("Network error. Try again.");
    });
});
</script>

</body>
</html>
