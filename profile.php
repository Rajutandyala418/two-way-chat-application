<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include('./include/db_connect.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$logged_user_id = $_SESSION['user_id'];
$logged_username = $_SESSION['username'];
$message = "";

$view_username = isset($_GET['user']) ? trim($_GET['user']) : "";

if ($view_username && $view_username !== $logged_username) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $view_username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) die("<h2>User not found</h2>");
    $user = $res->fetch_assoc();
    $editable = false;
} else {
    $editable = true;
    $user = $conn->query("SELECT * FROM users WHERE user_id=$logged_user_id")->fetch_assoc();

    if(isset($_POST['save_changes'])){
        $first = trim($_POST['first_name']);
        $last = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $mobile = trim($_POST['mobile']);

        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] !== ""){
            $tmp = $_FILES['profile_pic']['tmp_name'];
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $imageName = "PIC_" . time() . "." . $ext;
            $uploadPath = __DIR__ . "/uploads/" . $imageName;
            move_uploaded_file($tmp, $uploadPath);

            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, mobile=?, profile_pic=? 
            WHERE user_id=?");
            $stmt->bind_param("sssssi", $first, $last, $email, $mobile, $imageName, $logged_user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, mobile=? 
            WHERE user_id=?");
            $stmt->bind_param("ssssi", $first, $last, $email, $mobile, $logged_user_id);
        }

        $stmt->execute();
        $message = "Profile Updated Successfully!";
        header("refresh:1");
    }

    if(isset($_POST['delete_photo'])){
        $conn->query("UPDATE users SET profile_pic=NULL WHERE user_id=$logged_user_id");
        $message = "Profile Photo Removed.";
        header("refresh:1");
    }
}

$pic = !empty($user['profile_pic']) ? "uploads/".$user['profile_pic'] : "";
$initials = strtoupper(substr($user['username'],0,2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    background:#eef3f9;
    font-family:'Poppins',sans-serif;
    padding:20px;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.container{
    width:100%;
    max-width:430px;
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
    position:relative;
}
    .container h2{
        margin-top:40px;
        text-align:center;
    }
.back-btn{
    position:absolute;
    top:15px;
    right:15px;
    background:#000;
    color:#fff;
    padding:8px 16px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:6px;
}
.back-btn:hover{
    background:#333;
}


.profile-pic, .initials{
    width:140px;
    height:140px;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#1a73e8;
    color:#fff;
    font-size:45px;
    font-weight:700;
    margin:auto;
    border:3px solid #1a73e8;
    object-fit:cover;
    cursor:pointer;
}
input, button{
    width:95%;
    padding:13px;
    margin-top:15px;
    border-radius:10px;
    border:1px solid #cdd7e1;
    outline:none;
    font-size:15px;
}
button{
    background:#1a73e8;
    color:#fff;
    border:none;
    font-weight:600;
}
button:hover{ background:#125ab3; }
.delete-btn{ background:#cc0000; }
.delete-btn:hover{ background:#990000; }

.popup-bg{
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.6);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:999;
}
.popup-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    text-align:center;
    position:relative;
}
.popup-box img{
    max-width:300px;
    max-height:300px;
    border-radius:12px;
}
.close-popup{
    position:absolute;
    top:10px;
    right:10px;
    font-size:20px;
    color:red;
    cursor:pointer;
}
</style>
</head>
<body>

<div class="container">

<a href="dashboard.php" class="back-btn">
<i class="fa fa-arrow-left"></i> Back
</a>

<h2><?= ($editable ? "My Profile" : $user['username'] . " Profile") ?></h2>

<div class="profile-box">
    <?php if($pic): ?>
        <img src="<?= $pic ?>" class="profile-pic" id="profileView">
    <?php else: ?>
        <div class="initials" id="profileView"><?= $initials ?></div>
    <?php endif; ?>
</div>

<?php if($message): ?>
<p style="color:green;text-align:center;font-weight:600;">
<?= $message ?>
</p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<?php if($editable): ?>
<label><b>Change / Upload New Photo</b></label>
<input type="file" name="profile_pic" accept="image/*">
<?php endif; ?>

<label><b>Username</b></label>
<input type="text" value="<?= $user['username'] ?>" readonly>

<label><b>First Name</b></label>
<input type="text" name="first_name" value="<?= $user['first_name'] ?>" <?= $editable ? "" : "readonly" ?>>

<label><b>Last Name</b></label>
<input type="text" name="last_name" value="<?= $user['last_name'] ?>" <?= $editable ? "" : "readonly" ?>>

<label><b>Email</b></label>
<input type="email" name="email" value="<?= $user['email'] ?>" <?= $editable ? "" : "readonly" ?>>

<label><b>Phone Number</b></label>
<input type="text" name="mobile" value="<?= $user['mobile'] ?>" <?= $editable ? "" : "readonly" ?>>

<?php if($editable): ?>
<button type="submit" name="save_changes">Save Changes</button>
<?php if($pic): ?>
<button type="submit" name="delete_photo" class="delete-btn">Delete Photo</button>
<?php endif; ?>
<?php endif; ?>

</form>
</div>

<div class="popup-bg" id="popupBg">
<div class="popup-box" id="popupBox">
<span class="close-popup" onclick="closePopup()">✖</span>
<div id="popupContent"></div>
</div>
</div>

<script>
const popupBg = document.getElementById("popupBg");
const popupContent = document.getElementById("popupContent");
const profileView = document.getElementById("profileView");

profileView.addEventListener("click", ()=>{
<?php if($pic): ?>
popupContent.innerHTML = `<img src="<?= $pic ?>">`;
<?php else: ?>
popupContent.innerHTML = `<h3 style='color:#333;'>Image not available</h3>`;
<?php endif; ?>
popupBg.style.display = "flex";
});

function closePopup(){
popupBg.style.display="none";
}
</script>

</body>
</html>
