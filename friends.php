<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
include(__DIR__ . '/include/db_connect.php');
$me = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Friends</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#eef2f8;}

.header-bar{
    width:100%;
    background:#fff;
    border-bottom:1px solid #dce3ee;
    position:fixed;
    top:0;left:0;
    z-index:1000;
    padding:10px 14px;
    display:flex;
    flex-direction:column;
    gap:8px;
}
.header-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.header-left, .header-right{
    display:flex;
    align-items:center;
    gap:10px;
}
.header-btn{
    background:#1a73e8;
    color:#fff;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:8px;
    cursor:pointer;
}
.header-btn.secondary{
    background:#0b61d5;
    padding:8px 12px;
}
.header-title{
    text-align:center;
    font-size:20px;
    font-weight:700;
    color:#1a1a1a;
    margin:0 auto;
    padding:4px 0;
}

.main-box{
    margin-top:120px;
    width:100%;
    background:#fff;
    border-radius:12px;
    padding:16px;
    min-height:78vh;
    border:1px solid #e0e7f1;
    box-shadow:0 2px 10px rgba(0,0,0,0.03);
}

#searchFriend{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid #cfd7e6;
    margin-bottom:14px;
    font-size:14px;
}

.friend-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 10px;
    border-bottom:1px solid #eef2f8;
    gap:10px;
}
.friend-left{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:0;
}
.friend-name{
    font-weight:600;
    font-size:15px;
    color:#1a1a1a;
    cursor:pointer;
}

.pic{
    width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #1a73e8;cursor:pointer;
}
.initials{
    width:56px;height:56px;border-radius:50%;
    background:#1a73e8;color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;font-weight:700;cursor:pointer;
}

.friend-actions{
    display:flex;
    gap:10px;
    align-items:center;
}
.chat{
    background:#28a745;
    color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:14px;cursor:pointer;
}
.unfriend{
    background:#dc3545;
    color:#fff;border:none;padding:10px 16px;border-radius:10px;font-size:15px;cursor:pointer;font-weight:700;
}

@media(max-width:600px){
    .header-title{font-size:18px;}
    .pic, .initials{width:48px;height:48px;}
    .friend-item{flex-direction:column;align-items:flex-start;gap:8px;padding:10px;}
    .friend-actions{width:100%;display:flex;justify-content:space-between;}
    .chat,.unfriend{flex:1;padding:10px;font-size:14px;}
}

#imgOverlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.7);
    align-items:center;
    justify-content:center;
    z-index:2000;
}
#imgPreview{max-width:94%;max-height:94%;border-radius:10px;display:block;margin:auto;}
</style>
</head>
<body>

<div class="header-bar">
    <div class="header-top">
        <div class="header-left">
            <button class="header-btn" onclick="window.location.href='user_requests.php'">
                <i class="fa fa-arrow-left"></i> Back
            </button>
        </div>
    </div>
    <div>
        <h2 class="header-title">Friends</h2>
    </div>
</div>

<div class="main-box">
    <input type="text" id="searchFriend" placeholder="Search friends..." autocomplete="off">
    <div id="friendList" style="max-height:70vh;overflow-y:auto;padding-right:6px;"></div>
</div>

<div id="imgOverlay" onclick="closeImgPreview()">
    <img id="imgPreview" src="">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
function loadFriends(){
    $.post("load_friends.php", function(data){
        $("#friendList").html(data);
    });
}

$("#searchFriend").on("keyup", function(){
    let q = $(this).val().toLowerCase();
    $(".friend-item").each(function(){
        let name = $(this).data("username");
        $(this).toggle(name.toLowerCase().includes(q));
    });
});

$(document).on("click",".chat",function(){
    window.location.href = "dashboard.php?chat=" + encodeURIComponent($(this).data("friend"));
});

$(document).on("click",".unfriend",function(){
    let user = $(this).data("friend");
    if(!confirm("Remove " + user + " from friends?")) return;

    $.post("unfriend.php",{user:user},function(res){
        if(res.trim() === "success"){ loadFriends(); return; }
        alert("Unfriend failed.");
    });
});

$(document).on("click",".friend-name", function(){
    let user = $(this).data("username");
    window.location.href = "profile.php?user=" + encodeURIComponent(user);
});

function openImgPreview(src){
    $("#imgPreview").attr("src", src);
    $("#imgOverlay").fadeIn(150);
}
function closeImgPreview(){
    $("#imgOverlay").fadeOut(150);
}

$(document).on("click",".pic, .initials", function(){
    let src = $(this).attr("src");
    if(src){ openImgPreview(src); }
});

loadFriends();
setInterval(loadFriends, 4000);
</script>

</body>
</html>
