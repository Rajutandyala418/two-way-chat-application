<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email  = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $mobile_clean = preg_replace('/\D/', '', $mobile);

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $email);
    } elseif (!empty($mobile_clean)) {
        $stmt = $conn->prepare("SELECT username, email FROM users 
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(mobile, '+91', ''), ' ', ''), '-', ''), '+', '') = ?
            LIMIT 1");
        $stmt->bind_param("s", $mobile_clean);
    } else {
        echo json_encode(["status" => "empty"]);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();
        $username = $user['username'];
        $registered_email = $user['email'];

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
            $mail->addAddress($registered_email);

            $mail->isHTML(true);
            $mail->Subject = "Username Recovery Mini Chat App";
            $mail->Body    = "
                <p>Hello User,</p>
                <p>Your username is:</p>
                <h2 style='color:#1e3c57;'>$username</h2>
                <p>Regards,<br><b>Mini Chat Support Team</b></p>
            ";

            $mail->send();
            echo json_encode(["status" => "success"]);
            exit;

        } catch (Exception $e) {
            echo json_encode(["status" => "mail_error"]);
            exit;
        }

    } else {
        echo json_encode(["status" => "notfound"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Username – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
    background:#e8f0f7;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:15px;
}
.container{
    background:#ffffff;
    border:1px solid #d7e0ea;
    padding:25px;
    border-radius:14px;
    width:100%;
    max-width:380px;
    box-shadow:0 4px 15px rgba(0,0,0,0.06);
    position:relative;
}

.container h2{
    text-align:center;
    margin-bottom:20px;
    margin-top:40px;
    font-weight:700;
    color:#1e3c57;
}
.back-btn{
    position:absolute;
    top:15px;
    right:15px;
    background:#1e3c57;
    color:#fff;
    padding:10px 18px;
    border-radius:8px;
    font-size:14px;
    cursor:pointer;
    border:none;
    display:flex;
    align-items:center;
    gap:6px;
}

.back-btn:hover{
    background:#264a6e;
}

.back-btn i{
    margin-right:4px;
}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{
    width:100%;
    padding:11px;
    border-radius:8px;
    border:1px solid #b9c7d8;
    margin-top:6px;
    font-size:15px;
    outline:none;
}
.input-group input:focus{
    border-color:#0072ff;
}
.btn{
    background:#1e3c57;
    color:#fff;
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    margin-top:8px;
    font-weight:600;
    font-size:16px;
    cursor:pointer;
}
.btn:disabled{
    background:#6d8599;
    cursor:not-allowed;
}
.popup{
    display:none;
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#ffffff;
    padding:22px 30px;
    color:#1e3c57;
    border-radius:14px;
    text-align:center;
    border:1px solid #d7e0ea;
    box-shadow:0 4px 18px rgba(0,0,0,0.12);
    z-index:999;
}
.popup button{
    margin-top:10px;
    padding:8px 20px;
    border-radius:8px;
    border:none;
    background:#1e3c57;
    color:white;
    font-size:14px;
    cursor:pointer;
}
.loader{
    display:none;
    border:5px solid #e6e6e6;
    border-radius:50%;
    border-top:5px solid #1e3c57;
    width:38px;
    height:38px;
    animation:spin 1s linear infinite;
    margin:auto;
    margin-top:15px;
}
@keyframes spin{100%{transform:rotate(360deg);}}
@media(max-width:420px){
    .container{max-width:92%;padding:20px;}
    .btn{padding:10px;font-size:15px;}
    .back-btn{font-size:13px;padding:7px 12px;}
}
</style>
</head>

<body>

<div class="container">

<button class="back-btn" onclick="window.location.href='login.php'">
    <i class="fa fa-arrow-left"></i> Back
</button>

<h2>Forgot Username</h2>

<form id="forgotForm">

<div class="input-group">
<label>Registered Email</label>
<input type="email" name="email" autocomplete="off" placeholder="enter email">
</div>

<div class="input-group">
<label>Registered Mobile Number</label>
<input type="text" name="mobile" autocomplete="off" placeholder="enter phone number">
</div>

<button type="submit" id="findBtn" class="btn">Find Username</button>
<div class="loader" id="loader"></div>

</form>
</div>

<div class="popup" id="successPopup">
<h4 style="color:#1ea746;">Username Sent!</h4>
<p>Check your email inbox 📩</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="notFoundPopup">
<h4>Not Found</h4>
<p>No account matches entered details</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="mailErrorPopup">
<h4 style="color:red;">Mail Error</h4>
<p>Unable to send email. Try later.</p>
<button onclick="closePopup()">OK</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
$("#forgotForm").submit(function(e){
    e.preventDefault();
    $("#loader").show();
    $("#findBtn").prop("disabled", true);

    $.ajax({
        url:"",
        type:"POST",
        data:$(this).serialize(),
        success:function(res){
            $("#loader").hide();
            $("#findBtn").prop("disabled", false);
            let data = JSON.parse(res);

            if(data.status === "success"){ $("#successPopup").show(); }
            else if(data.status === "mail_error"){ $("#mailErrorPopup").show(); }
            else if(data.status === "empty"){ $("#notFoundPopup").show(); }
            else { $("#notFoundPopup").show(); }
        },
        error:function(){
            $("#loader").hide();
            $("#findBtn").prop("disabled", false);
            $("#mailErrorPopup").show();
        }
    });
});

function closePopup(){ $(".popup").hide(); }
</script>

</body>
</html>
