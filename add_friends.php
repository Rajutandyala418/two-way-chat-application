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
<title>Add Friends</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f3f6fb;}

/* HEADER BAR (2 ROWS) */
.header-bar{
    width:100%;
    background:#fff;
    border-bottom:1px solid #e4e9f1;
    position:fixed;
    top:0; left:0;
    z-index:999;
    padding:8px 12px;
    display:flex;
    flex-direction:column;
}

/* TOP ROW WITH 2 BUTTONS */
.top-row{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header-btn{
    background:#1a73e8;
    color:#fff;
    border:none;
    padding:9px 18px;
    border-radius:8px;
    display:flex;
    align-items:center;
    gap:6px;
    font-size:14px;
    cursor:pointer;
    font-weight:600;
}

/* TITLE IN NEXT LINE */
.header-title{
    width:100%;
    text-align:center;
    font-size:25px;
    font-weight:700;
    margin-top:6px;
    color:#1a73e8;
}

/* MAIN CONTENT */
.main-box{
    margin-top:95px;
    width:100%;
    background:#fff;
    border-radius:10px;
    padding:15px;
    min-height:90vh;
    border:1px solid #e4e9f1;
}

#searchUser{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
    margin-bottom:12px;
    font-size:15px;
}

/* USER LIST */
.user-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px;
    border-bottom:1px solid #f0f0f0;
}

.user-left{
    display:flex;
    align-items:center;
    gap:12px;
}

/* PROFILE PIC */
.pic{
    width:48px;height:48px;border-radius:50%;
    object-fit:cover;border:2px solid #1a73e8;
    cursor:pointer;
}
.initials{
    width:48px;height:48px;border-radius:50%;
    background:#1a73e8;color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-weight:600;font-size:16px;
    cursor:pointer;
}

/* BUTTONS */
.add, .req{
    padding:8px 14px;
    border-radius:6px;
    font-size:13px;
    border:none;
    cursor:pointer;
    font-weight:600;
}
.add{background:#1a73e8;color:#fff;}
.req{background:#6c757d;color:#fff;}

@media(max-width:600px){
    .header-btn{padding:8px 14px;font-size:13px;}
    .header-title{font-size:22px;}
    .add,.req{font-size:12px;padding:7px 12px;}
    .user-item{padding:10px;}
    .pic,.initials{width:45px;height:45px;}
}

/* IMAGE PREVIEW POPUP */
#imgOverlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.65);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:2000;
}
#imgPreview{
    max-width:88%;
    max-height:88%;
    border-radius:12px;
    margin:auto;
    display:block;
    object-fit:contain;
}
</style>
</head>
<body>

<!-- HEADER BAR -->
<div class="header-bar">

    <div class="top-row">
        <button class="header-btn" onclick="window.location.href='user_requests.php'">
            <i class="fa fa-arrow-left"></i> Back
        </button>

        <button class="header-btn" onclick="window.location.href='friends.php'">
            Friends
        </button>
    </div>

    <h2 class="header-title">Add Friends</h2>

</div>
    </br>
<!-- MAIN BOX -->
<div class="main-box">
    <input type="text" id="searchUser" placeholder="Search username...">
    <div id="searchResults"></div>
</div>

<!-- IMAGE PREVIEW -->
<div id="imgOverlay">
    <img id="imgPreview" src="">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
$("#searchUser").keyup(function(){
    let q = $(this).val().trim();
    if(q === ""){
        $("#searchResults").html("");
        return;
    }
    $.post("search_users.php",{q:q}, function(res){
        $("#searchResults").html(res);
    });
});

function addFriend(user){
    $.post("send_request.php",{user:user},function(){
        refreshSearch();
    });
}

function cancelRequest(user){
    $.post("cancel_requests.php",{sender:'<?= $me ?>', receiver:user}, function(){
        refreshSearch();
    });
}

$(document).on("click",".add",function(e){
    e.preventDefault();
    addFriend($(this).data("user"));
});

$(document).on("click",".req",function(e){
    e.preventDefault();
    cancelRequest($(this).data("user"));
});

function refreshSearch(){
    let q = $("#searchUser").val().trim();
    if(q === "") return;
    $.post("search_users.php",{q:q}, function(res){
        $("#searchResults").html(res);
    });
}

/* Image popup */
$(document).on("click",".pic, .initials",function(e){
    e.stopPropagation();
    let src = $(this).attr("src");
    if(src){
        $("#imgPreview").attr("src", src);
        $("#imgOverlay").fadeIn(150);
    }
});

$("#imgOverlay").click(function(){
    $("#imgOverlay").fadeOut(150);
});
</script>

</body>
</html>
