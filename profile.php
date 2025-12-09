<?php
session_start();
include('./include/db_connect.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

$user = $conn->query("SELECT profile_pic, username, first_name, last_name, email, mobile FROM users WHERE user_id=$user_id")->fetch_assoc();
$pic = !empty($user['profile_pic']) ? "uploads/".$user['profile_pic'] : "";
$initials = strtoupper(substr($user['username'],0,2));

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

        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, mobile=?, profile_pic=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $first, $last, $email, $mobile, $imageName, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, mobile=? WHERE user_id=?");
        $stmt->bind_param("ssssi", $first, $last, $email, $mobile, $user_id);
    }
    $stmt->execute();
    $message = "Profile Updated Successfully!";
    header("refresh:1");
}

if(isset($_POST['delete_photo'])){
    $conn->query("UPDATE users SET profile_pic=NULL WHERE user_id=$user_id");
    $message = "Profile Photo Removed.";
    header("refresh:1");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile – Mini Chat App</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
    position: relative;
}
h2{
    text-align:center;
    color:#1a73e8;
    margin-bottom:15px;
    font-weight:700;
}
.profile-box{
    text-align:center;
    margin-bottom:20px;
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
}
input, button{
    width:100%;
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
    transition:.3s;
}
button:hover{
    background:#125ab3;
}
.delete-btn{
    background:#cc0000;
}
.delete-btn:hover{
    background:#990000;
}
.back-btn{
    background:#1a73e8;
    color:#fff;
    padding:8px 16px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    box-shadow:0 3px 8px rgba(0,0,0,0.2);
    display:inline-block;
    margin-bottom:15px;
}
.back-btn:hover{
    background:#125ab3;
}
.message{
    text-align:center;
    color:green;
    font-weight:600;
    margin-top:10px;
}
@media(max-width:480px){
    .container{
        padding:20px;
        max-width:95%;
    }
    h2{
        font-size:22px;
    }
    .profile-pic,.initials{
        width:120px;
        height:120px;
        font-size:38px;
    }
    .back-btn{
        font-size:12px;
        padding:7px 15px;
    }
}
</style>
</head>
<body>

<div class="container">

    <!-- moved inside container -->
    <a href="dashboard.php" class="back-btn">Back</a>

    <h2>My Profile</h2>

    <div class="profile-box">
        <?php if($pic): ?>
            <img src="<?= $pic ?>" class="profile-pic">
        <?php else: ?>
            <div class="initials"><?= $initials ?></div>
        <?php endif; ?>
    </div>

    <?php if($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label><b>Change / Upload New Photo</b></label>
        <input type="file" name="profile_pic" accept="image/*">

        <label><b>Username</b></label>
        <input type="text" value="<?= $user['username'] ?>" disabled>

        <label><b>First Name</b></label>
        <input type="text" name="first_name" value="<?= $user['first_name'] ?>" required>

        <label><b>Last Name</b></label>
        <input type="text" name="last_name" value="<?= $user['last_name'] ?>" required>

        <label><b>Email</b></label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required>

        <label><b>Phone Number</b></label>
        <input type="text" name="mobile" value="<?= $user['mobile'] ?>" required>

        <button type="submit" name="save_changes">Save Changes</button>

        <?php if($pic): ?>
        <button type="submit" name="delete_photo" class="delete-btn">Delete Photo</button>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
