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
        $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows > 0) $response["username"] = "⚠ Username already exists.";
    }

    if(!empty($mobile)){
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile = ? LIMIT 1");
        $stmt->bind_param("s", $mobile);
        $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows > 0) $response["mobile"] = "⚠ Phone number already registered.";
    }

    if(!empty($email)){
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows > 0) $response["email"] = "⚠ Email already registered.";
    }

    echo json_encode($response);
    exit;
}

if(isset($_POST['final_submit'])){
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $username     = trim($_POST['username']);
    $mobile       = trim($_POST['mobile']);
    $email        = trim($_POST['email']);
    $password     = trim($_POST['password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if($password !== $confirm_pass){
        echo json_encode(["status"=>"password_mismatch"]);
        exit;
    }

    $check = $conn->prepare("SELECT username, email, mobile FROM users 
                             WHERE username=? OR email=? OR mobile=? LIMIT 1");
    $check->bind_param("sss", $username, $email, $mobile);
    $check->execute();
    $dup = $check->get_result();

    if($dup->num_rows > 0){
        $row = $dup->fetch_assoc();
        if($row['username'] === $username){
            echo json_encode(["status"=>"username_exists"]); exit;
        }
        if($row['email'] === $email){
            echo json_encode(["status"=>"email_exists"]); exit;
        }
        if($row['mobile'] === $mobile){
            echo json_encode(["status"=>"mobile_exists"]); exit;
        }
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, username, mobile, email, password)
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $mobile, $email, $hashedPassword);

    if($stmt->execute()){
        echo json_encode(["status"=>"success"]);
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
.container h2{text-align:center;margin-bottom:18px;font-weight:700;color:#1e3c57;margin-top:40px;}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{width:100%;padding:11px;border-radius:8px;border:1px solid #b9c7d8;margin-top:6px;font-size:15px;outline:none;}
.input-group input:focus{border-color:#0072ff;}
.input-group input::placeholder{color:#8fa6bb;}
.error{font-size:13px;color:#d9534f;margin-top:4px;}
#passwordHelp{color:#444;font-size:12px;margin-top:4px;}

.register-btn{background:#1e3c57;color:#fff;width:100%;padding:12px;border:none;border-radius:8px;font-size:16px;cursor:pointer;}
.register-btn:hover{background:#264a6e;}
.back-btn{
    position:absolute;
    top:15px;
    right:15px;
    background:#1e3c57;
    color:#fff;
    border:none;
    font-size:16px;
    padding:10px 18px;
    border-radius:8px;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:6px;
}

.back-btn:hover{
    background:#264a6e;
}

.container{
    position:relative;
}



.success-popup, .error-popup{
    display:none;position:fixed;top:40%;left:50%;transform:translate(-50%,-50%);
    background:#fff;padding:22px 28px;border-radius:12px;text-align:center;
    border:1px solid #d7e0ea;box-shadow:0 4px 15px rgba(0,0,0,0.15);
}
.popup-btn{
    margin-top:12px;padding:8px 18px;border:none;background:#1e3c57;
    color:white;border-radius:8px;font-size:14px;cursor:pointer;
}
.popup-btn:hover{background:#264a6e;}

.loader{display:none;border:4px solid #ccc;border-radius:50%;border-top:4px solid #1e3c57;width:40px;height:40px;animation:spin 0.9s linear infinite;margin:auto;margin-top:15px;}
@keyframes spin{100%{transform:rotate(360deg);}}

#countdown{font-size:26px;font-weight:700;color:#1e3c57;padding:6px 14px;background:#d9ecff;border-radius:10px;display:inline-block;}

@media(max-width:480px){
    .container{padding:20px;border-radius:12px;width:95%;}
    .input-group input{font-size:14px;padding:10px;}
    .register-btn{font-size:15px;padding:11px;}
    .back-btn{padding:8px 14px;font-size:14px;}
    #passwordHelp{font-size:11px;}
}
</style>
</head>

<body>



<div class="container">
<button type="button" onclick="window.location.href='index.php'" class="back-btn">
    <i class="fa fa-arrow-left"></i> Back
</button>

<h2>Create Account</h2>

<form id="registerForm" autocomplete="off">

<div class="input-group">
<label>First Name</label>
<input type="text" name="first_name" placeholder="Enter first name" required>
</div>

<div class="input-group">
<label>Last Name</label>
<input type="text" name="last_name" placeholder="Enter last name" required>
</div>

<div class="input-group">
<label>Username</label>
<input type="text" name="username" id="username" placeholder="Choose a username" required>
<div id="usernameError" class="error"></div>
</div>

<div class="input-group">
<label>Phone Number</label>
<input type="text" name="mobile" id="mobile" placeholder="Enter phone number" required inputmode="numeric">
<div id="mobileError" class="error"></div>
</div>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" id="email" placeholder="Enter your email" required>
<div id="emailError" class="error"></div>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" id="password" placeholder="Create a password" required>
<div id="passwordHelp">⚠ Must include uppercase, lowercase, number, special char.</div>
<div id="passwordError" class="error"></div>
</div>

<div class="input-group">
<label>Confirm Password</label>
<input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required>
<div id="confirmError" class="error"></div>
</div>

<button type="submit" class="register-btn" id="regBtn">Register</button>
<div class="loader" id="loader"></div>

</form>
</div>

<div class="success-popup" id="successPopup">
<h3>🎉 Account Created Successfully 🎉</h3>
<p>Redirecting in <span id="countdown">5</span> seconds...</p>
<button class="popup-btn" onclick="window.location.href='login.php'">OK</button>
</div>

<div class="error-popup" id="errorPopup">
<h3 id="errorMsgText"></h3>
<button class="popup-btn" onclick="document.getElementById('errorPopup').style.display='none'">OK</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
function showError(msg){
    document.getElementById("errorMsgText").innerText = msg;
    document.getElementById("errorPopup").style.display = "block";
}

$("#username, #mobile, #email").on("keyup", function(){
    $.post("register.php", {
        check_validation: 1,
        username: $("#username").val(),
        mobile: $("#mobile").val(),
        email: $("#email").val()
    }, function(data){
        const r = JSON.parse(data);
        $("#usernameError").text(r.username);
        $("#mobileError").text(r.mobile);
        $("#emailError").text(r.email);
    });
});

$("#password").on("keyup", function(){
    const pass = $(this).val();
    const rules = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/;
    $("#passwordError").text(rules.test(pass) ? "" : "❌ Weak Password Format");
});

$("#confirm_password, #password").on("keyup", function(){
    $("#confirmError").text(
        $("#password").val() === $("#confirm_password").val() ? "" : "❗ Passwords do not match"
    );
});

$("#registerForm").submit(function(e){
    e.preventDefault();

    if($("#usernameError").text() || $("#emailError").text() || $("#mobileError").text()){
        showError("Fix the highlighted errors before submitting.");
        return;
    }

    $("#loader").show();
    $("#regBtn").hide();
    $(".back-btn").hide();

    $.ajax({
        url: "register.php",
        type: "POST",
        data: $(this).serialize() + "&final_submit=1",
        success: function(res){
            let r = JSON.parse(res);

            $("#loader").hide();
            $("#regBtn").show();
            $(".back-btn").show();

            if(r.status === "username_exists"){ showError("⚠ Username already exists!"); return; }
            if(r.status === "email_exists"){ showError("⚠ Email already registered!"); return; }
            if(r.status === "mobile_exists"){ showError("⚠ Phone number already registered!"); return; }
            if(r.status === "password_mismatch"){ showError("Passwords do not match!"); return; }

            if(r.status === "success"){
                $("#successPopup").show();
                let timeLeft = 5;
                let timer = setInterval(()=>{
                    timeLeft--;
                    $("#countdown").text(timeLeft);
                    if(timeLeft <= 0){
                        clearInterval(timer);
                        window.location="login.php";
                    }
                },1000);
                return;
            }

            showError("Registration failed.");
        }
    });
});
</script>

</body>
</html>
