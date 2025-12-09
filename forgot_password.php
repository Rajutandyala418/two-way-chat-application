<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$details = null;
$error_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $mobile   = trim($_POST['mobile']);
    $mobile_clean = preg_replace('/\D/', '', $mobile);

    if (empty($username) || empty($email) || empty($mobile_clean)) {
        $error_type = "required_all";
    } else {

        $stmt = $conn->prepare("SELECT username, email, mobile FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $error_type = "username_not_found";
        } else {
            $user = $res->fetch_assoc();
            $db_email  = strtolower($user['email']);
            $db_mobile = preg_replace('/\D/', '', $user['mobile']);

            if (strtolower($email) !== $db_email) {
                $error_type = "email_not_found";
            }
            elseif ($mobile_clean !== $db_mobile) {
                $error_type = "mobile_not_found";
            }
            else {
                $details = $user;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#e8f0f7;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:15px;}
.container{
    background:#ffffff;border:1px solid #d7e0ea;padding:25px;border-radius:14px;
    width:100%;max-width:390px;box-shadow:0 4px 15px rgba(0,0,0,0.08);position:relative;
}
.container h2{text-align:center;margin-bottom:20px;font-weight:700;color:#1e3c57;}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#1e3c57;}
.input-group input{
    width:100%;padding:11px;border-radius:8px;border:1px solid #b9c7d8;
    outline:none;font-size:15px;margin-top:6px;
}
.input-group input:focus{border-color:#0072ff;}
.btn{
    background:#1e3c57;color:#fff;width:100%;padding:12px;border:none;
    border-radius:8px;cursor:pointer;margin-top:8px;font-weight:600;font-size:16px;
}
.btn:disabled{background:#6a8297;cursor:not-allowed;}
.back-btn{
    position:fixed;top:18px;right:18px;background:#1e3c57;color:#fff;
    padding:8px 14px;border-radius:8px;font-size:14px;text-decoration:none;z-index:100;
}
.back-btn:hover{background:#264a6e;}
.table-box{
    background:#fff;padding:20px;border-radius:14px;box-shadow:0 4px 15px rgba(0,0,0,0.1);
    width:100%;max-width:420px;text-align:center;
}
table{width:100%;border-collapse:collapse;margin-bottom:15px;}
th,td{border:1px solid #b9c7d8;padding:10px;font-size:14px;}
th{background:#1e3c57;color:#fff;font-weight:600;}
.popup{
    display:none;position:fixed;top:50%;left:50%;
    transform:translate(-50%,-50%);background:#fff;padding:25px;
    border-radius:14px;text-align:center;box-shadow:0 4px 18px rgba(0,0,0,0.12);
    z-index:999;
}
.popup button{
    margin-top:12px;padding:8px 20px;border-radius:8px;
    border:none;background:#1e3c57;color:white;font-size:14px;
}
.popup button:hover{background:#264a6e;}
@media(max-width:420px){
    .container{max-width:92%;padding:20px;}
    .table-box{max-width:92%;padding:18px;}
}
#screenLoader{
    display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(255,255,255,0.7);backdrop-filter:blur(3px);
    z-index:9999;align-items:center;justify-content:center;
}
#screenLoader div{
    border:6px solid #e3e3e3;border-top:6px solid #1e3c57;border-radius:50%;
    width:65px;height:65px;animation:spin 1s linear infinite;
}
@keyframes spin{100% {transform:rotate(360deg);}}
</style>
</head>
<body>

<a href="login.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back</a>

<?php if(!$details): ?>
<div class="container">
<h2>Forgot Password</h2>

<?php if(!empty($error_type) && !$details): ?>
<p style="text-align:center;color:#d9534f;font-size:14px;font-weight:600;margin-bottom:10px;">
Incorrect details, please try again.
</p>
<?php endif; ?>

<form method="POST">
<div class="input-group">
<label>Enter Username</label>
<input type="text" name="username" placeholder="enter username" required>
</div>

<div class="input-group">
<label>Enter Registered Email</label>
<input type="email" name="email" placeholder="enter email" required>
</div>

<div class="input-group">
<label>Enter Registered Phone Number</label>
<input type="text" name="mobile" placeholder="enter phone" required>
</div>

<button type="submit" class="btn">Verify Details</button>
</form>
</div>
<?php endif; ?>

<?php if($details): ?>
<div class="table-box">
<h3>Account Verified ✔</h3>
<table>
<tr><th>Username</th><td><?php echo $details['username']; ?></td></tr>
<tr><th>Email</th><td><?php echo $details['email']; ?></td></tr>
<tr><th>Phone</th><td><?php echo $details['mobile']; ?></td></tr>
</table>

<button type="button" class="btn" id="resetBtn">Reset Password</button>
</div>
<?php endif; ?>

<div class="popup" id="requiredPopup">
<h4 style="color:red;">All Fields Required</h4>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="usernameNotFound">
<h4 style="color:red;">Username Not Found</h4>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="emailNotFound">
<h4 style="color:red;">Email Not Matched</h4>
<button onclick="closePopup()">OK</button>
</div>

<div class="popup" id="mobileNotFound">
<h4 style="color:red;">Phone Not Matched</h4>
<button onclick="closePopup()">OK</button>
</div>

<div id="screenLoader"><div></div></div>

<script>
<?php if($error_type === "required_all") echo 'document.getElementById("requiredPopup").style.display="block";'; ?>
<?php if($error_type === "username_not_found") echo 'document.getElementById("usernameNotFound").style.display="block";'; ?>
<?php if($error_type === "email_not_found") echo 'document.getElementById("emailNotFound").style.display="block";'; ?>
<?php if($error_type === "mobile_not_found") echo 'document.getElementById("mobileNotFound").style.display="block";'; ?>

function closePopup(){
    document.querySelectorAll(".popup").forEach(p => p.style.display = "none");
}

document.getElementById("resetBtn")?.addEventListener("click", function(){
    document.getElementById("screenLoader").style.display = "flex";
    this.disabled = true;
    document.body.style.pointerEvents = "none";
    setTimeout(() => {
        window.location.href = "send_otp.php?username=<?php echo $details['username']; ?>";
    }, 1000);
});
</script>

</body>
</html>
