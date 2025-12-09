<?php
session_start();
include('./include/db_connect.php');
require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if(isset($_POST['check_validation'])){
    $username = $_POST['username'] ?? '';
    $mobile   = $_POST['mobile'] ?? '';
    $email    = $_POST['email'] ?? '';

    $response = ["username"=>"", "mobile"=>"", "email"=>""];

    if(!empty($username)){
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $response["username"] = "⚠ Username already exists.";
        }
    }

    if(!empty($mobile)){
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile = ? LIMIT 1");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $response["mobile"] = "⚠ Phone number already registered.";
        }
    }

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
    exit;
}

if(isset($_POST['final_submit'])){

    $first_name      = trim($_POST['first_name']);
    $last_name       = trim($_POST['last_name']);
    $username        = trim($_POST['username']);
    $mobile          = trim($_POST['mobile']);
    $email           = trim($_POST['email']);
    $password        = trim($_POST['password']);
    $confirm_pass    = trim($_POST['confirm_password']);

    if($password !== $confirm_pass){
        echo json_encode(["status"=>"password_mismatch"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, username, mobile, email, password)
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $mobile, $email, $hashedPassword);

    if($stmt->execute()){
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
            $mail->Subject = "Welcome to Mini Chat 🎉";

            $mail->Body = "
                <h2>Hello $first_name $last_name 👋</h2>
                <p>Thank you for registering at <b>Mini Chat</b>.</p>
                <p>Your login details are:</p>
                <b>Username:</b> $username<br>
                <b>Email:</b> $email<br>
                <b>Mobile:</b> $mobile<br><br>

                <p>Enjoy seamless chatting and secure conversations 🎉</p><br>
                <p>Regards,<br><b>Mini Chat Team</b></p>
            ";

            $mail->send();
            echo json_encode(["status"=>"success"]);

        } catch (Exception $e) {
            echo json_encode(["status"=>"mail_failed"]);
        }
    } else {
        echo json_encode(["status"=>"error"]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register – Mini Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#e8f0f7;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:15px;}
.container{background:#fff;border:1px solid #d7e0ea;padding:25px;border-radius:14px;width:100%;max-width:400px;box-shadow:0 4px 15px rgba(0,0,0,0.06);}
.container h2{text-align:center;margin-bottom:18px;font-weight:700;color:#1e3c57;}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{width:100%;padding:11px;border-radius:8px;border:1px solid #b9c7d8;margin-top:6px;font-size:15px;outline:none;}
.input-group input:focus{border-color:#0072ff;}
#errorMsg, .error{font-size:13px;color:#d9534f;margin-top:4px;}
#passwordHelp{text-align:left;color:#444;font-size:12px;margin-top:4px;}
.success-popup{display:none;position:fixed;top:40%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:20px 30px;border-radius:12px;text-align:center;border:1px solid #d7e0ea;box-shadow:0 4px 18px rgba(0,0,0,0.12);}
.loader{display:none;border:4px solid #ccc;border-radius:50%;border-top:4px solid #1e3c57;width:40px;height:40px;animation:spin 0.9s linear infinite;margin:auto;margin-top:15px;}
@keyframes spin{100%{transform:rotate(360deg);}}
.register-btn{background:#1e3c57;color:#fff;width:100%;padding:12px;border:none;border-radius:8px;font-size:16px;cursor:pointer;}
.register-btn:hover{background:#264a6e;}
.back-btn{position:absolute;top:18px;right:20px;padding:9px 16px;background:#1e3c57;color:#fff;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:6px;}
.back-btn i{font-size:16px;}
.back-btn:hover{background:#264a6e;}
#countdown{font-size:28px;font-weight:700;color:#1e3c57;padding:6px 14px;background:#d9ecff;border-radius:10px;display:inline-block;margin-left:6px;animation:pop 1s infinite;}
@keyframes pop{0%{transform:scale(1);}50%{transform:scale(1.3);}100%{transform:scale(1);}}
input::-webkit-contacts-auto-fill-button,
input::-webkit-credentials-auto-fill-button{visibility:hidden !important;display:none !important;}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus{transition:background-color 9999s ease-out,color 9999s ease-out;}
</style>
</head>

<body>

<button type="button" onclick="window.location.href='index.php'" class="back-btn">
    <i class="fa fa-arrow-left"></i> Back
</button>

<div class="container">
<h2>Create Account</h2>

<form id="registerForm" autocomplete="off">
    <input type="text" style="display:none">
    <input type="password" style="display:none">

    <div class="input-group">
        <label>First Name</label>
        <input type="text" name="first_name" placeholder="Enter first name" required autocomplete="off">
    </div>

    <div class="input-group">
        <label>Last Name</label>
        <input type="text" name="last_name" placeholder="Enter last name" required autocomplete="off">
    </div>

    <div class="input-group">
        <label>Username</label>
        <input type="text" name="username" id="username" placeholder="Choose username" required autocomplete="off">
        <div id="usernameError" class="error"></div>
    </div>

    <div class="input-group">
        <label>Phone Number</label>
        <input type="text" name="mobile" id="mobile" placeholder="Enter phone number" required autocomplete="off" inputmode="numeric">
        <div id="mobileError" class="error"></div>
    </div>

    <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" id="email" placeholder="Enter valid email" required autocomplete="off">
        <div id="emailError" class="error"></div>
    </div>

    <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" id="password" placeholder="Create password" required autocomplete="new-password">
        <div id="passwordHelp">⚠ Must have 8+ chars, uppercase, lowercase, number & special char.</div>
        <div id="passwordError" class="error"></div>
    </div>

    <div class="input-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required autocomplete="new-password">
        <div id="confirmError" class="error"></div>
    </div>

    <button type="submit" class="register-btn" id="regBtn">Register</button>
    <div class="loader" id="loader"></div>
</form>
</div>

<div class="success-popup" id="successPopup">
    <h3>🎉 Account Created Successfully 🎉</h3>
    <p id="redirectMsg">
        Redirecting in <span id="countdown">5</span> seconds...
    </p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
$("#username, #mobile, #email").on("keyup", function(){
    $.post("register.php", {
        check_validation: 1,
        username: $("#username").val(),
        mobile: $("#mobile").val(),
        email: $("#email").val()
    }, function(data){
        const res = JSON.parse(data);
        $("#usernameError").text(res.username);
        $("#mobileError").text(res.mobile);
        $("#emailError").text(res.email);
    });
});

$("#password").on("keyup", function(){
    const pass = $(this).val();
    const rules = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/;
    $("#passwordError").text(
        rules.test(pass) ? "" : "❌ Weak Password Format"
    );
});

$("#confirm_password, #password").on("keyup", function(){
    $("#confirmError").text(
        $("#password").val() === $("#confirm_password").val() ? "" : "❗ Passwords do not match"
    );
});

$("#registerForm").submit(function(e){
    e.preventDefault();

    if($("#passwordError").text() || $("#confirmError").text()){
        alert("Fix password errors before submitting.");
        return;
    }

    $("#loader").show();
    $("#regBtn").hide();
    $(".back-btn").hide();

    $.ajax({
        url: "register.php",
        type: "POST",
        data: $(this).serialize() + "&final_submit=1",
        success: function(resp){
            let res = JSON.parse(resp);

            if(res.status === "password_mismatch"){
                alert("Passwords don't match!");
                $("#loader").hide(); 
                $("#regBtn").show(); 
                $(".back-btn").show();
                return;
            }

            if(res.status === "success"){
                $("#loader").hide();
                $("#successPopup").show();

                let timeLeft = 5;
                $("#countdown").text(timeLeft);

                let timer = setInterval(() => {
                    timeLeft--;
                    $("#countdown").text(timeLeft);

                    if(timeLeft <= 0){
                        clearInterval(timer);
                        window.location = "login.php";
                    }
                }, 1000);

            } else {
                alert("Registration failed or email not sent.");
                $("#loader").hide(); 
                $("#regBtn").show(); 
                $(".back-btn").show();
            }
        }
    });
});
</script>

</body>
</html>
