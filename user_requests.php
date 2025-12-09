<?php
session_start();
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
<title>User Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f3f6fb;}
.container-row{display:flex;gap:10px;padding:20px;}
.box{width:33.33%;background:#fff;border-radius:10px;padding:15px;height:92vh;border:1px solid #e4e9f1;}
input{width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;margin-bottom:10px;}
.user-item,.friend-item{display:flex;align-items:center;justify-content:space-between;padding:8px;border-bottom:1px solid #eee;cursor:pointer;}
.user-left,.friend-left{display:flex;align-items:center;gap:10px;}
.pic{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #1a73e8;cursor:pointer;}
.initials{width:40px;height:40px;border-radius:50%;background:#1a73e8;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;}
button{padding:5px 8px;font-size:12px;border:none;border-radius:6px;cursor:pointer;}
.add{background:#1a73e8;color:#fff;}
.req{background:#9ea8b6;color:#fff;}
#reqList td img {
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #1a73e8;
    display:block;
    margin:auto;
}


#reqList tr {
    height:65px;
}
#reqList tr {
    border-bottom:1px solid #e4e9f1;
}
#reqList tr {
    cursor: default !important;
}


#reqList td {
    padding:10px 0;
}
#reqList td {
    text-align:center;
    vertical-align:middle;
}

#reqList .initials {
    width:45px;
    height:45px;
    line-height:45px;
    font-size:15px;
    border-radius:50%;
    background:#1a73e8;
    color:#fff;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}
.img-controls-fixed{
    position:fixed;
    top:20px;
    right:30px;
    display:flex;
    gap:12px;
    z-index:4000; /* stay above zoom image */
}

.img-controls-fixed .ctrl-btn{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#ffffff;
    color:#000;
    font-size:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    box-shadow:0 3px 10px rgba(0,0,0,0.4);
}


.approve{background:#28a745;color:#fff;}
.reject{background:#dc3545;color:#fff;}
.unfriend{background:#dc3545;color:#fff;}
table tr td, table tr th {border-bottom:1px solid #f0f0f0;text-align:center;font-size:13px;}
#reqList tr:hover {background:#f5faff;}
#imgOverlay{position:fixed;inset:0;background:rgba(0,0,0,0.65);display:none;align-items:center;justify-content:center;z-index:2000;}
#imgPreviewWrap{position:relative;max-width:90%;max-height:90%;}
#imgPreview{max-width:50%;max-height:100%;border-radius:10px;display:block;margin:auto;transform:scale(1);transition:transform 0.2s ease;}
.ctrl-btn{width:34px;height:34px;border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;background:#ffffff;box-shadow:0 2px 6px rgba(0,0,0,0.3);cursor:pointer;font-size:18px;}
.ctrl-btn:focus{outline:none;}
.header-bar{width:100%;height:55px;background:#fff;border-bottom:1px solid #e4e9f1;display:flex;align-items:center;justify-content:space-between;padding:0 20px;position:fixed;top:0;left:0;z-index:999;}
.header-bar button{background:#1a73e8;color:#fff;border:none;padding:7px 14px;border-radius:6px;display:flex;align-items:center;gap:6px;font-size:13px;}
.header-bar h2{font-size:18px;font-weight:600;}
@media (max-width: 900px){
.container-row{flex-direction:column;}
.box{width:100%;height:auto;}
}
</style>
</head>
<body>
<div class="header-bar">
    <button onclick="window.location.href='dashboard.php'">
        <i class="fa fa-arrow-left"></i> Back
    </button>
    <h2>User Requests</h2>
    <div style="width:40px;"></div>
</div>

<div class="container-row" style="margin-top:70px;">
    <div class="box">
        <h2>Add Friends</h2>
        <input type="text" id="searchUser" placeholder="Search username...">
        <div id="searchResults"></div>
    </div>

    <div class="box">
        <h2>Friend Requests</h2>
        <input type="text" id="searchReq" placeholder="Search requests...">
        <div style="overflow-x:auto;overflow-y:auto;height: calc(92vh - 120px);border:1px solid #ddd;border-radius:6px;">
            <table style="width:100%; border-collapse:collapse; min-width:420px;">
                <thead>
                    <tr style="background:#eef3f9;">
      <th style="padding:8px; width:70px; text-align:center;">Profile</th>
                        <th style="padding:8px;">Username</th>
                        <th style="padding:8px;">Status</th>
                        <th style="padding:8px;">Action</th>
                    </tr>
                </thead>
                <tbody id="reqList"></tbody>
            </table>
        </div>
    </div>

    <div class="box">
        <h2>Friends</h2>
        <input type="text" id="searchFriend" placeholder="Search friends...">
        <div id="friendList"></div>
    </div>
</div>

<div id="imgOverlay">
    <div id="imgPreviewWrap">
        <img id="imgPreview" src="">
    </div>

    <div class="img-controls-fixed">
        <button class="ctrl-btn" id="zoomInBtn">+</button>
        <button class="ctrl-btn" id="zoomOutBtn">−</button>
        <button class="ctrl-btn" id="closeImgBtn">×</button>
    </div>
</div>


</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
let zoomLevel = 1;
function showImagePopup(src){
    if(!src) return;
    zoomLevel = 1;
    $("#imgPreview").attr("src", src).css("transform","scale(1)");
    $("#imgOverlay").css("display","flex");
}
function closeImagePopup(){
    zoomLevel = 1;
    $("#imgOverlay").hide();
    $("#imgPreview").attr("src","").css("transform","scale(1)");
}
$("#zoomInBtn").on("click", function(e){
    e.stopPropagation();
    zoomLevel += 0.2;
    $("#imgPreview").css("transform","scale("+zoomLevel+")");
});
$("#zoomOutBtn").on("click", function(e){
    e.stopPropagation();
    zoomLevel = Math.max(0.4, zoomLevel - 0.2);
    $("#imgPreview").css("transform","scale("+zoomLevel+")");
});
$("#closeImgBtn").on("click", function(e){
    e.stopPropagation();
    closeImagePopup();
});
$("#imgOverlay").on("click", function(e){
    if(e.target.id === "imgOverlay"){
        closeImagePopup();
    }
});


$(document).on("click", "#searchResults .user-left, #searchResults .initials, #friendList .user-left, #friendList .initials", function(e){
    e.preventDefault();
    e.stopPropagation();
});



$("#searchUser").keyup(function(){
    let q = $(this).val().trim();
    if(q === ""){
        $("#searchResults").html("");
        return;
    }
    $.post("search_users.php",{q:q}, function(res){
        $("#searchResults").html(res);
        $("#searchResults .user-item").off("click");
        $("#searchResults .username").off("click");
    });
});

$(document).on("click", "#friendList .pic, #friendList .friend-left", function(e){
    e.preventDefault();
    e.stopPropagation();
});
$(document).on("click", "#friendList .username", function(){
    openChat($(this).data("friend"));
});


$("#searchFriend").keyup(function(){
    let q = $(this).val().toLowerCase();
    $(".friend-item").each(function(){
        $(this).toggle($(this).data("friend").toLowerCase().includes(q));
    });
});

function addFriend(user){
    if($("button[data-user='"+user+"']").hasClass("add")){
        $.post("send_request.php",{user:user},function(){
            loadRequests();
            loadFriends();
            loadSearch();
        });
    } 
    else {
        cancelRequest(user);
    }
}
$(document).on("click", "button.req", function(e){
    e.preventDefault();
    e.stopPropagation();
    cancelRequest($(this).data("user"));
});

$(document).on("click", "button.add", function(e){
    e.preventDefault();
    e.stopPropagation();
    addFriend($(this).data("user"));
});


function cancelRequest(user){
$.post("cancel_requests.php",{sender:'<?= $me ?>', receiver:user}, function(){
    loadRequests();
    loadFriends();
    loadSearch();
});
}

function reqAction(type,user){
    $.post("update_request_status.php",{action:type,user:user},function(){
        loadRequests();
        loadFriends();
        loadSearch();
    });
}

function unfriend(user){
    $.post("unfriend.php",{user:user},function(){
        loadFriends();
        $("#searchResults").html("");
    });
}

function openChat(user){
    window.location.href = "dashboard.php?chat=" + encodeURIComponent(user);
}

function loadRequests(){
    $.post("load_requests.php",function(data){
        $("#reqList").html(data);
    });
}

function loadFriends(){
    $.post("load_friends.php",function(data){
        $("#friendList").html(data);
    });
}

function loadSearch(){
    let q = $("#searchUser").val().trim();
    if(q === "") return;
    $.post("search_users.php",{q:q},function(data){
        $("#searchResults").html(data);
        $("#searchResults .user-item").removeAttr("onclick");
    });
}

$("#searchReq").keyup(function(){
    let q = $(this).val().toLowerCase();
    $(".reqRow").each(function(){
        $(this).toggle($(this).data("name").toLowerCase().includes(q));
    });
});

loadRequests();
loadFriends();
setInterval(loadFriends, 3000);
let lastReqCount = 0;
$(document).on("click", "#searchResults .pic, #friendList .pic, #reqList .pic", function(e){
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    showImagePopup($(this).attr("src"));
});

$(document).on("click", "#reqList tr", function(e){
    e.preventDefault();
    e.stopPropagation();
});


</script>
</body>
</html>

