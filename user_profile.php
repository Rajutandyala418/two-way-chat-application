<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include(__DIR__.'/include/db_connect.php');

if(!isset($_GET['user'])){
    header("Location: dashboard.php");
    exit;
}

$username = $_GET['user'];
$q = $conn->query("SELECT * FROM users WHERE username='$username'");
$user = $q->fetch_assoc();

if(!$user){
    echo "User not found";
    exit;
}

$dp = !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : '';
$initials = strtoupper(substr($username,0,2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - <?= $username ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#eef3f9;display:flex;justify-content:center;align-items:center;height:100vh;}

.back-btn{
    position:absolute;
    top:15px;
    left:15px;
    background:#1a73e8;
    color:#fff;
    padding:8px 16px;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
    border:none;
}

.container{
    width:360px;
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 4px 12px rgba(0,0,0,0.15);
    text-align:center;
}

.profile-pic{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #1a73e8;
    cursor:pointer;
    margin-bottom:12px;
}
.dp-initials{
    width:120px;
    height:120px;
    border-radius:50%;
    border:3px solid #1a73e8;
    background:#1a73e8;
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:38px;
    font-weight:600;
    margin:0 auto 12px;
    cursor:pointer;
}
.details{margin-top:10px;text-align:left;}
.details p{margin:10px 0;font-size:15px;}
.details strong{color:#2b3e55;}

/* Popup full screen */
#popupOverlay{
    position:fixed;
    top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.65);
    display:none;
    z-index:999;
}
#fullPopup{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    display:none;
    z-index:1500;
    text-align:center;
    justify-content:center;
    align-items:center;
}
#popupOverlay{
    overflow:hidden;
}

#fullImg{
    max-width:80%;
    max-height:80%;
    border-radius:10px;
    transition:transform 0.3s ease;
}

/* Controls pinned at top of popup */
.popup-controls{
    position:left;
    top:20px;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    gap:20px;
    z-index:2000;
}

.popup-btn{
    background:#fff;
    padding:10px 14px;
    border-radius:50%;
    cursor:pointer;
    font-weight:bold;
    font-size:18px;
}
#closePopup{
    background:#e53935;
    color:#fff;
}
</style>
</head>
<body>

<button class="back-btn" onclick="history.back()">Back</button>

<div class="container">
    <?php if($dp): ?>
        <img src="<?= $dp ?>" id="mainDP" class="profile-pic">
    <?php else: ?>
        <div id="mainDP" class="dp-initials"><?= $initials ?></div>
    <?php endif; ?>

    <h2><?= ucfirst($username) ?></h2>

    <div class="details">
        <p><strong>First Name:</strong> <?= $user['first_name'] ?></p>
        <p><strong>Last Name:</strong> <?= $user['last_name'] ?></p>
        <p><strong>Email:</strong> <?= $user['email'] ?></p>
        <p><strong>Mobile:</strong> <?= $user['mobile'] ?></p>
    </div>
</div>

<!-- Fullscreen Popup -->
<div id="popupOverlay"></div>

<div id="fullPopup">
    <div class="popup-controls">
        <div class="popup-btn" id="zoomIn">+</div>
        <div class="popup-btn" id="zoomOut">−</div>
        <div class="popup-btn" id="closePopup">X</div>
    </div>
    <img id="fullImg">
</div>

<script>
let zoom = 1;

document.getElementById("mainDP").onclick = function(){
    if(this.tagName === "IMG"){
        document.getElementById("fullImg").src = this.src;
        zoom = 1;
        document.getElementById("fullImg").style.transform = "scale(1)";
        document.getElementById("popupOverlay").style.display = "block";
        document.getElementById("fullPopup").style.display = "flex";
    }
};

document.getElementById("zoomIn").onclick = function(){
    zoom += 0.2;
    document.getElementById("fullImg").style.transform = "scale(" + zoom + ")";
};

document.getElementById("zoomOut").onclick = function(){
    zoom = Math.max(0.5, zoom - 0.2);
    document.getElementById("fullImg").style.transform = "scale(" + zoom + ")";
};

document.getElementById("closePopup").onclick = function(){
    zoom = 1;
    document.getElementById("fullImg").style.transform = "scale(1)";
    document.getElementById("popupOverlay").style.display = "none";
    document.getElementById("fullPopup").style.display = "none";
};

document.getElementById("popupOverlay").onclick = function(e){
    if(e.target.id === "popupOverlay"){
        document.getElementById("closePopup").click();
    }
};
</script>

</body>
</html>
