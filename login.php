<?php
session_start();
include('./include/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT user_id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();
 
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            echo json_encode(["status" => "success"]);
            exit;
        }
    }

    echo json_encode(["status" => "error"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#e8f0f7;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:15px;}
.container{background:#ffffff;border:1px solid #d7e0ea;padding:25px;border-radius:14px;width:100%;max-width:380px;box-shadow:0 4px 15px rgba(0,0,0,0.06);}
.container h2{text-align:center;margin-bottom:20px;font-weight:700;color:#1e3c57;}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{width:100%;padding:11px;border-radius:8px;border:1px solid #b9c7d8;margin-top:6px;outline:none;font-size:15px;}
.input-group input:focus{border-color:#0072ff;}
.input-group input::placeholder{color:#9bb1c7;font-size:14px;}
.login-btn{background:#1e3c57;color:#fff;width:100%;padding:12px;border:none;border-radius:8px;cursor:pointer;margin-top:8px;font-weight:600;font-size:16px;}
.login-btn:hover{background:#264a6e;}
.back-btn{position:absolute;top:18px;right:20px;background:#1e3c57;color:#fff;border:none;font-size:16px;padding:10px 18px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:6px;}
.back-btn i{font-size:17px;}
.back-btn:hover{background:#264a6e;}
.popup{display:none;position:fixed;top:40%;left:50%;transform:translate(-50%,-50%);background:#ffffff;padding:22px 30px;color:#1e3c57;border-radius:14px;text-align:center;border:1px solid #d7e0ea;box-shadow:0 4px 18px rgba(0,0,0,0.12);}
.popup button{margin-top:10px;padding:8px 20px;border-radius:8px;border:none;background:#1e3c57;color:white;cursor:pointer;font-size:14px;}
.popup button:hover{background:#264a6e;}
.opt-btn{background:#ffffff;color:#1e3c57;border:1px solid #b9c7d8;padding:10px 12px;width:48%;border-radius:8px;font-size:13px;cursor:pointer;transition:0.3s;}
.opt-btn:hover{background:#e8eef5;}
.options{display:flex;justify-content:space-between;margin-top:15px;}
.loader{display:none;border:5px solid #e6e6e6;border-radius:50%;border-top:5px solid #1e3c57;width:38px;height:38px;animation:spin 1s linear infinite;margin:auto;margin-top:15px;}
@keyframes spin{100%{transform:rotate(360deg);}}
</style>
</head>
<body>

<button type="button" onclick="window.location.href='index.php'" class="back-btn">
    <i class="fa fa-arrow-left"></i> Back
</button>

<div class="container">
<h2>Login</h2>
<form id="loginForm" autocomplete="off">
<input type="text" style="display:none">
<input type="password" style="display:none">

<div class="input-group">
<label>Username or Email</label>
<input type="text" name="login_input" placeholder="Enter username or email" autocomplete="new-username">
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" placeholder="Enter your password" autocomplete="new-password">
</div>

<button type="submit" class="login-btn">Login</button>
<div class="loader" id="loader"></div>

<div class="options">
<button type="button" onclick="window.location.href='forgot_username.php'" class="opt-btn">Forgot Username</button>
<button type="button" onclick="window.location.href='forgot_password.php'" class="opt-btn">Forgot Password</button>
</div>
</form>
</div>

<div class="popup" id="errorPopup">
<h4>Invalid username or password</h4>
<button onclick="closePopup()">OK</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$("#loginForm").submit(function(e){
e.preventDefault();
$("#loader").show();

$.ajax({
url: "",
type: "POST",
data: $(this).serialize(),
success: function(res){
let data = JSON.parse(res);
$("#loader").hide();

if(data.status === "success"){
window.location.href = "dashboard.php";
} else {
$("#errorPopup").show();
}
},
error:function(){
$("#loader").hide();
$("#errorPopup").show();
}
});
});

function closePopup(){
$("#errorPopup").hide();
}
</script>
</body>
</html>
