<?php 
session_start();
include('./include/db_connect.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

if(isset($_POST['password'])){
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
    $stmt->bind_param("si", $new_pass, $user_id);
    $stmt->execute();
    session_unset();
    session_destroy();
    echo "updated";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
    background:#eef3f9;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}
.container{
    width:100%;
    max-width:380px;
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
    position:relative;
}
h2{text-align:center;font-weight:700;color:#1a73e8;margin-bottom:20px;}
.input-group{margin-bottom:15px;}
.input-group label{font-weight:500;font-size:14px;color:#2b3e55;}
.input-group input{
    width:100%;
    padding:11px;
    border-radius:8px;
    border:1px solid #cdd7e1;
    font-size:15px;
    margin-top:6px;
    outline:none;
}
.input-group input:focus{border-color:#1a73e8;}
.btn{
    width:100%;
    padding:12px;
    margin-top:10px;
    background:#1a73e8;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
.btn:hover{background:#125ab3;}
.back-btn{
    position:absolute;
    top:15px;
    right:15px;
    background:#1a73e8;
    color:#fff;
    padding:10px 22px;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    box-shadow:0 3px 8px rgba(0,0,0,0.2);
}
.back-btn:hover{background:#125ab3;}
#passwordHelp{font-size:12px;color:#888;margin-top:4px;}
.error{color:#d9534f;font-size:13px;margin-top:4px;}
.popup{
    display:none;
    position:fixed;
    top:42%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    padding:22px 30px;
    border-radius:14px;
    text-align:center;
    box-shadow:0 4px 18px rgba(0,0,0,0.15);
    color:#1ea746;
    font-weight:700;
}
@media(max-width:480px){
    .container{
        width:90%;
        padding:20px;
    }
    .back-btn{
        font-size:13px;
        padding:8px 16px;
        top:10px;
        right:10px;
    }
}
</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">Back</a>

<div class="container">
    <h2>Update Password</h2>
    <form id="updateForm">
        <div class="input-group">
            <label>Username</label>
            <input type="text" value="<?= $username ?>" disabled>
        </div>
        <div class="input-group">
            <label>New Password</label>
            <input type="password" id="password" name="password" placeholder="Enter new password" required>
            <div id="passwordHelp">Must include: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special char.</div>
            <div id="passwordError" class="error"></div>
        </div>
        <div class="input-group">
            <label>Confirm Password</label>
            <input type="password" id="confirm_password" placeholder="Re-enter password" required>
            <div id="confirmError" class="error"></div>
        </div>
        <button type="submit" class="btn">Update Password</button>
    </form>
</div>

<div class="popup" id="successPopup">Password Updated Successfully 🎉<br><small>Redirecting...</small></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$("#password").on("keyup", function(){
    const v = $(this).val();
    const r = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    $("#passwordError").text(r.test(v) ? "" : "Password must meet all requirements");
});
$("#confirm_password, #password").on("keyup", function(){
    $("#confirmError").text(
        $("#confirm_password").val() === $("#password").val() ? "" : "Passwords do not match"
    );
});
$("#updateForm").submit(function(e){
    e.preventDefault();
    if($("#passwordError").text() || $("#confirmError").text()){
        alert("Fix errors before updating");
        return;
    }
    $.post("update_password.php", {
        password: $("#password").val()
    }, function(p){
        if(p.trim() === "updated"){
            $("#successPopup").show();
            setTimeout(()=>{ window.location.href = "login.php"; }, 2000);
        }
    });
});
</script>

</body>
</html>
