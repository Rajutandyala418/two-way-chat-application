<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

require __DIR__ . '/include/php_mailer/PHPMailer.php';
require __DIR__ . '/include/php_mailer/SMTP.php';
require __DIR__ . '/include/php_mailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['username'])) die("Invalid Access");

$username = $_GET['username'];
$error_type = "";

// Check user exists
$stmt = $conn->prepare("SELECT email, first_name FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found");

$email = $user['email'];
$first_name = $user['first_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        $error_type = "mismatch";
    }
    elseif (strlen($password) < 8 ||
            !preg_match("/[A-Z]/", $password) ||
            !preg_match("/[a-z]/", $password) ||
            !preg_match("/[0-9]/", $password) ||
            !preg_match("/[\W]/", $password)) {
        $error_type = "weak";
    }
    else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $update = $conn->prepare("UPDATE users SET password=?, otp=NULL, otp_expiry=NULL WHERE username=?");
        $update->bind_param("ss", $hashed, $username);
        $update->execute();

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host='smtp.gmail.com';
            $mail->SMTPAuth=true;
            $mail->Username='minichatwebapp@gmail.com';
            $mail->Password='zeor ksda atmz bzla';  // replace with app password
            $mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port=587;
            $mail->setFrom('minichatwebapp@gmail.com', 'Mini Chat App');
            $mail->addAddress($email, $first_name);
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Successful";
            $mail->Body = "<p>Hello $first_name,</p><p>Your password has been successfully updated.</p>";

            $mail->send();
            $error_type = "success";
        }
        catch(Exception $e){
            $error_type = "emailfail";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
    background:#e8f0f7;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:15px;
}
.container{
    background:#ffffff;
    border:1px solid #d7e0ea;
    padding:25px;
    border-radius:14px;
    width:100%;
    max-width:390px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}
h2{text-align:center;margin-bottom:20px;color:#1e3c57;}

.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{
    width:100%;padding:11px;border-radius:8px;border:1px solid #b9c7d8;
    outline:none;font-size:15px;margin-top:6px;
}
.input-group input:focus{border-color:#0072ff;}

.rule{font-size:12px;margin-top:6px;color:#ff4d4d;}
.rule.valid{color:green;}

.btn{
    background:#1e3c57;color:#fff;width:100%;padding:12px;border:none;
    border-radius:8px;cursor:pointer;margin-top:8px;font-weight:600;font-size:16px;
}
.btn:hover{background:#264a6e;}

.popup{
    display:none;position:fixed;top:50%;left:50%;
    transform:translate(-50%,-50%);
    background:#fff;padding:25px;border-radius:14px;text-align:center;
    box-shadow:0 4px 18px rgba(0,0,0,0.15);z-index:999;
}
.popup button{
    padding:8px 20px;margin-top:10px;border:none;background:#1e3c57;
    color:white;border-radius:8px;cursor:pointer;
}

#overlayLoader{
    display:none;
    width:100%;
    height:100%;
    background:rgba(255,255,255,0.7);
    position:fixed;
    top:0;
    left:0;
    z-index:1000;
    justify-content:center;
    align-items:center;
    backdrop-filter:blur(3px);
}

.spin{
    width:70px;height:70px;border:7px solid #ddd;border-top:7px solid #1e3c57;
    border-radius:50%;animation:spin 1s linear infinite;
}
@keyframes spin{100%{transform:rotate(360deg);} }

</style>
</head>
<body>

<div id="overlayLoader"><div class="spin"></div></div>

<div class="container">
<h2>Reset Password</h2>
<form method="POST" onsubmit="showLoader()">
<div class="input-group">
<label>Username</label>
<input type="text" value="<?php echo $username; ?>" readonly>
</div>

<div class="input-group">
<label>New Password</label>
<input type="password" name="password" id="password" placeholder="Enter new password" required>
<div id="r1" class="rule">• Minimum 8 characters</div>
<div id="r2" class="rule">• At least 1 uppercase</div>
<div id="r3" class="rule">• At least 1 lowercase</div>
<div id="r4" class="rule">• At least 1 number</div>
<div id="r5" class="rule">• At least 1 special symbol</div>
</div>

<div class="input-group">
<label>Confirm Password</label>
<input type="password" name="confirm" placeholder="Re-enter password" required>
</div>

<button type="submit" class="btn">Update Password</button>
</form>
</div>

<!-- POPUPS -->
<div class="popup" id="successPopup">
<h3 style="color:green;">Password Updated Successfully</h3>
<p>Redirecting...</p>
</div>

<div class="popup" id="mismatchPopup">
<h3 style="color:red;">Password Mismatch</h3>
<p>Both passwords must match.</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="weakPopup">
<h3 style="color:red;">Weak Password</h3>
<p>Follow password rules shown.</p>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="emailPopup">
<h3 style="color:red;">Email Could Not Be Sent</h3>
<p>Password updated but email failed.</p>
<button onclick="redirectLogin()">OK</button>
</div>
<?php if($error_type === "mismatch" || $error_type === "weak" || $error_type === "emailfail") : ?>
<script>
document.getElementById("overlayLoader").style.display="none";
</script>
<?php endif; ?>

<script>
function showLoader(){
    document.querySelectorAll(".popup").forEach(p => p.style.display="none");
    document.getElementById("overlayLoader").style.display = "flex";
}

function closePopup(){
    document.querySelectorAll(".popup").forEach(p => p.style.display="none");
}

// Redirect
function redirectLogin(){ window.location.href="login.php"; }

// Dynamic rule color
password.addEventListener("input", () => {
    let p = password.value;
    document.getElementById("r1").className = p.length >= 8 ? "rule valid" : "rule";
    document.getElementById("r2").className = /[A-Z]/.test(p) ? "rule valid" : "rule";
    document.getElementById("r3").className = /[a-z]/.test(p) ? "rule valid" : "rule";
    document.getElementById("r4").className = /[0-9]/.test(p) ? "rule valid" : "rule";
    document.getElementById("r5").className = /[\W]/.test(p) ? "rule valid" : "rule";
});

// Show popups after PHP result
<?php if($error_type === "success") : ?>
document.getElementById("overlayLoader").style.display="none";
document.getElementById("successPopup").style.display="block";
setTimeout(()=>{ window.location.href="login.php"; },2000);
<?php endif; ?>

<?php if($error_type === "mismatch") : ?>
document.getElementById("overlayLoader").style.display="none";
document.getElementById("mismatchPopup").style.display="block";
<?php endif; ?>

<?php if($error_type === "weak") : ?>
document.getElementById("overlayLoader").style.display="none";
document.getElementById("weakPopup").style.display="block";
<?php endif; ?>

<?php if($error_type === "emailfail") : ?>
document.getElementById("overlayLoader").style.display="none";
document.getElementById("emailPopup").style.display="block";
<?php endif; ?>

</script>

</body>
</html>
