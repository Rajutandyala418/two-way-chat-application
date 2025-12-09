<?php 
session_start(); 
if(!isset($_SESSION['user_id'])){ 
    header("Location: login.php"); 
    exit; 
} 
include(__DIR__ . '/include/db_connect.php'); 
if(isset($_POST['fetch_dp'])){
    $username = trim($_POST['username']);

    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($profile);
    $stmt->fetch();

    echo (!empty($profile)) ? 'uploads/'.$profile : '';
    exit;
}

$username = $_SESSION['username']; 
$initials = strtoupper(substr($username, 0, 2)); 
$user = $conn->query("SELECT profile_pic FROM users WHERE user_id=".$_SESSION['user_id'])->fetch_assoc(); 
$pic = !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : ''; 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
<meta charset="UTF-8"> 
<title>Dashboard</title> 
<meta name="viewport" content="width=device-width, initial-scale=1"> 
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
<style> 
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;} 
body{display:flex;height:100vh;overflow:hidden;background:#f3f6fb;} 
.sidebar{width:28%;background:#ffffff;border-right:1px solid #e1e8ee;display:flex;flex-direction:column;overflow-y:auto;} 
.top-bar{display:flex;justify-content:space-between;align-items:center;padding:15px;border-bottom:1px solid #e1e8ee;} 
.top-bar h3{font-size:18px;font-weight:600;color:#2b3e55;} 
.search-box{padding:10px;border-bottom:1px solid #e1e8ee;} 
.search-box input{width:100%;padding:10px;border-radius:8px;border:1px solid #cdd7e1;outline:none;} 
.user-list{overflow-y:auto;flex:1;height:calc(100vh - 90px);} 
.user{padding:12px 15px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:10px;cursor:pointer;} 
.user:hover{background:#eef3f9;} 
.chat-area{flex:1;display:flex;flex-direction:column;position:relative;}

.profile-pic{width:35px;height:35px;border-radius:50%;cursor:pointer;border:2px solid #1a73e8;object-fit:cover;} 
.dp-initials{width:35px;height:35px;border-radius:50%;border:2px solid #1a73e8;display:flex;justify-content:center;align-items:center;color:#fff;font-weight:600;font-size:14px;cursor:pointer;background:#1a73e8;} 
.dropdown{position:absolute;top:60px;right:15px;background:#fff;border:1px solid #e1e8ee;border-radius:10px;width:180px;display:none;flex-direction:column;z-index:100;} 
.dropdown button{border:none;background:#fff;padding:12px;width:100%;text-align:left;font-size:14px;border-bottom:1px solid #e1e8ee;cursor:pointer;} 
.dropdown button:last-child{border-bottom:none;} 
.chat-header{display:flex;align-items:center;gap:10px;padding:10px 15px;border-bottom:1px solid #ddd;background:#fff;cursor:pointer;} 
.chat-dp{width:35px;height:35px;border-radius:50%;border:2px solid #1a73e8;object-fit:cover;} 
#chatUserName{font-size:16px;font-weight:600;color:#2b3e55;} 
.chat-box{flex:1;overflow-y:auto;padding:15px;} 
.msg{max-width:70%;margin-bottom:8px;padding:8px 10px;border-radius:10px;position:relative;word-wrap:break-word;} 
.msg.me{
    margin-left:auto;
    background:#000;
    color:#fff;
}
.msg * {
    pointer-events: none;
}
.msg-delete {
    pointer-events: auto !important; /* only trash icon clickable */
}


.msg.them{margin-right:auto;background:#e5e9f2;color:#333;} 

.chat-input{display:flex;align-items:center;gap:10px;background:#fff;padding:12px;border-top:1px solid #ddd;} 
#docBtn,#micBtn,#sendBtn{border:none;padding:10px 14px;border-radius:50%;font-size:18px;cursor:pointer;} 
#docBtn{background:#eaeff5;color:#1a73e8;} 
#micBtn{background:#eaeff5;color:#e02626;} 
#sendBtn{background:#1a73e8;color:#fff;border-radius:6px;padding:10px 18px;} 
#stopRecord{display:none;background:#ff4747;color:#fff;border-radius:6px;padding:8px 14px;} 
audio {
    height: 36px;
    border-radius: 10px;
    background:#fff;
}
.msg-audio audio::-webkit-media-controls-panel {
    background:#ffffff;
    border-radius:20px;
}
.chat-date{
    text-align:center;
    font-size:12px;
    color:#777;
    margin:10px 0;
}
.date-separator{
    text-align:center;
    font-size:12px;
    color:#777;
    margin:10px 0;
}

.msg-audio audio {
    background:#ffffff;
    border-radius:20px;
}
#popupOverlay{
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.65);
    z-index:999;
}
#fullPopup{
    position:fixed;
    top:50%; left:50%;
    transform:translate(-50%, -50%);
    z-index:1000;
}
.badge-count{
    background:#e53935;
    color:#fff;
    font-size:10px;
    border-radius:50%;
    padding:2px 6px;
    margin-left:6px;
}
#scrollDownBtn{
    transition:opacity .3s;
}
#scrollDownBtn.hide{
    opacity:0;
    pointer-events:none;
}

.msg{
    max-width:70%;
    margin-bottom:8px;
    padding:8px 10px;
    border-radius:10px;
    position:relative;   /* important */
    word-wrap:break-word;
}
.msg-delete{
    position:absolute;
    top:50%;
    right:-20px;
    transform:translateY(-50%);
    font-size:12px;
    cursor:pointer;
    opacity:0.4;
}
.msg:hover .msg-delete{
    opacity:1;
}

#fullImg{
    max-width:80%;
    max-height:80%;
    border-radius:10px;
}
.popup-controls{
    position:fixed;
    top:25px;
    right:35px;
    display:flex;
    flex-direction:column;
    gap:12px;
    z-index:1000;
}

.popup-btn{
    background:#ffffff;
    padding:10px 14px;
    border-radius:50%;
    font-size:18px;
    cursor:pointer;
    box-shadow:0 2px 6px rgba(0,0,0,0.3);
}

#closePopup{
    background:#e53935;
    color:#fff;
}

.popup-btn{
    background:#ffffff;
    padding:10px 14px;
    border-radius:50%;
    font-size:18px;
    cursor:pointer;
}
#closePopup{ background:#e53935; color:#fff; }
.msg-time {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 3px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    color:#fff;  /* time text color */
}

.msg-status i {
    font-size: 12px;
}

.msg-status.single i { color:#fff; }     /* ✓ */
.msg-status.double i { color:#fff; }     /* ✓✓ delivered */
.msg-status.read i   { color:#1a73e8; }  /* ✓✓ read blue */


.msg-status.read {
    color: #1a73e8; /* blue when read */
}

/* sidebar unread chats (you probably already use this in fetch_chats.php) */
.user.unread {
    background: #eef3f9;
}

</style> 
</head> 
<body> 

<div class="sidebar"> 
<div class="top-bar"> 
<h3>Chats</h3> 
<div style="position:relative;display:inline-block;">
    <i class="fa fa-user-plus" id="openRequests" style="cursor:pointer;font-size:18px;color:#2b3e55;"></i>
    <span id="reqBadge" 
          style="display:none;position:absolute;top:-5px;right:-8px;background:#e53935;color:#fff;
                 font-size:10px;border-radius:50%;padding:2px 5px;min-width:16px;text-align:center;">
    </span>
</div>
 
</div> 
<div class="search-box"><input type="text" id="searchUser" placeholder="Search in your chats..."></div> 
<div class="user-list" id="userList"></div> 
</div> 

<div class="chat-area"> 
<div class="top-bar" style="justify-content:flex-end;"> 
<?php if($pic): ?> 
<img src="<?= $pic ?>" class="profile-pic" id="myProfileIcon">
<?php else: ?> 
<div class="dp-initials" id="profileIcon"><?= $initials ?></div> 
<?php endif; ?> 
<div class="dropdown" id="dropdownMenu"> 
<button id="viewPhotoBtn">View Photo</button>
<button onclick="window.location.href='profile.php'">Profile</button> 
<button onclick="window.location.href='update_password.php'">Update Password</button> 
<button onclick="window.location.href='logout.php'">Logout</button> 
</div>

</div> 

<div class="chat-header" id="chatHeader" style="display:none;"> 
<img src="" id="chatUserPic" class="chat-dp"> 
<h4 id="chatUserName"></h4> 
</div> 

<div class="chat-box" id="chatBox" style="display:flex;justify-content:center;align-items:center;"> 
    <p style="color:#999;">Select a chat to start messaging</p> 
</div>

<button id="scrollDownBtn" 
        style="
            position:absolute;
            right:40px;
            bottom:80px;
            z-index:50;
            padding:8px 10px;
            border-radius:50%;
            border:none;
            display:none;
            box-shadow:0 2px 6px rgba(0,0,0,0.2);
            background:#1a73e8;
            color:#fff;
            cursor:pointer;
        ">
    <i class="fa fa-arrow-down"></i>
</button>


<div class="chat-input" id="chatInput" style="display:none;"> 
<input type="file" id="sendFile" style="display:none;"> 
<input type="file" id="docFile" style="display:none;"> 
<button id="docBtn"><i class="fa fa-paperclip"></i></button> 
<button id="micBtn"><i class="fa fa-microphone"></i></button> 
<button id="stopRecord">Stop</button> 
<input type="text" id="messageText" placeholder="Type a message..." style="flex:1;padding:10px;border-radius:6px;border:1px solid:#ccc;"> 
<button id="sendBtn" onclick="sendMessage()">Send</button> 
</div> 
</div> 
<div id="popupOverlay" style="display:none;"></div>
<div id="fullPopup" style="display:none;">
    <div class="popup-controls">
        <div class="popup-btn" id="zoomIn">+</div>
        <div class="popup-btn" id="zoomOut">−</div>
        <div class="popup-btn" id="closePopup">X</div>
    </div>
    <img id="fullImg">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> 
<script> 
let lastGlobalMsgId = 0;

// ======== SOUND OBJECTS ========
const sndSend   = new Audio('send.mp3');
const sndRecv   = new Audio('receive.mp3');
const sndNotify = new Audio('notify.mp3');

// Track last message id per opened chat
let lastMsgIdByUser = {};

// Track total unread for sidebar notify
let lastUnreadTotal = 0;

function loadChats(){
    $.post("fetch_chats.php", function(d){
        $("#userList").html(d);

let newestMsgId = 0;
$("#userList .user").each(function(){
    let id = parseInt($(this).data("lastid")) || 0;
    if(id > newestMsgId) newestMsgId = id;
});

        let currentOpen = $("#chatUserName").text().trim();

        // 🟢 If new message arrived & it's not from the open user
        if(newestMsgId > lastGlobalMsgId){
            let firstUser = $("#userList .user").first();
            let u = firstUser.data("username");
            let unread = parseInt(firstUser.find(".badge-count").text()) || 0;

if(unread > 0 && u !== currentOpen){
    sndNotify.play(); // notification sound when chat not open
}


            lastGlobalMsgId = newestMsgId;
        }
    });
}
loadChats(); 
$("#searchUser").on("keyup", function(){ 
let q = $(this).val().toLowerCase(); 
$(".user").each(function(){ $(this).toggle($(this).data("username").toLowerCase().includes(q)); }); 
}); 
setInterval(loadChats, 1000); 

$(document).on("click",".user", function(){ openChat($(this).data("username")); }); 

function openChat(user){
    $("#chatHeader").show();
    $("#chatInput").show();
    $("#chatUserName").text(user);
    $("#chatBox").css({"display":"block"});

    $.post("", {fetch_dp:1, username:user}, function(pic){
        if(pic.trim() === ""){
            $("#chatUserPic").hide();
            if(!$("#chatHeader .chat-initials").length){
                $("#chatHeader").prepend(
                    '<div class="chat-initials" style="width:35px;height:35px;border-radius:50%;background:#1a73e8;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;border:2px solid #1a73e8;">'
                    + user.substring(0,2).toUpperCase() +
                    '</div>'
                );
            }
        } else {
            $("#chatHeader .chat-initials").remove();
            $("#chatUserPic").show().attr("src", pic);
        }
    });

    loadMessages(user);

    setTimeout(()=>{
        $.post("mark_read.php", {user:user}, function(){
            loadChats(); 
        });
    }, 800);
}

$(document).on("click", function(e){
    if(!$(e.target).closest("#dropdownMenu, #profileIcon, #myProfileIcon").length){
        $("#dropdownMenu").hide();
    }
});


function sendMessage(fileBlob, type){ 
    let msg = $("#messageText").val().trim(); 
    let to = $("#chatUserName").text(); 
    let file = fileBlob ? fileBlob : $("#sendFile")[0].files[0] || $("#docFile")[0].files[0]; 

    if(msg === "" && !file) return; 

    let d = new FormData(); 
    d.append("message", msg); 
    d.append("to", to); 

    if(file){
        if(type === "audio"){
            d.append("audio", file);
        } else {
            d.append("media", file);
        }
    }

    $.ajax({ 
        url:"send_message.php", 
        type:"POST", 
        data:d, 
        contentType:false, 
        processData:false, 
success:function(){ 
    $("#messageText").val(""); 
    $("#docFile").val(""); 
    $("#sendFile").val(""); 

    sndSend.play(); // play send sound

    openChat(to); 
    loadChats(); 
} 

    }); 
}


setInterval(function(){ 
let user = $("#chatUserName").text().trim(); 
if(user !== "") loadMessages(user); 
}, 2000); 

function loadMessages(user){ 
    let chatBox = $("#chatBox"); 
    let atBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 5; 
    let prevScroll = chatBox.scrollTop(); 
    let prevHeight = chatBox[0].scrollHeight; 

    let prevLastId = lastMsgIdByUser[user] || 0;

    $.post("load_messages.php", {username:user}, function(res){ 
        chatBox.html(res); 
        initWaveSurfer();

        // Get last message element
        let lastMsg = $("#chatBox .msg").last();
        if(lastMsg.length){
            let lastId = parseInt(lastMsg.data("id")) || 0;
// If new message arrived AND it's from them -> play sound
if(lastId > prevLastId && lastMsg.hasClass("them")) {
    sndRecv.play();
}


            lastMsgIdByUser[user] = lastId;
        }

        if(atBottom){ 
            chatBox.scrollTop(chatBox[0].scrollHeight); 
        } else { 
            let newHeight = chatBox[0].scrollHeight; 
            chatBox.scrollTop(prevScroll + (newHeight - prevHeight)); 
        } 
    }); 
}

$("#docBtn").click(function(){ $("#docFile").click(); }); 

$("#docFile").change(function(){ sendMessage(); }); 

let recorder; 
let audioChunks = []; 

$("#micBtn").click(async function(){ 
audioChunks = []; 
$("#micBtn").hide(); 
$("#stopRecord").show(); 
let stream = await navigator.mediaDevices.getUserMedia({ audio: true }); 
recorder = new MediaRecorder(stream); 
recorder.start(); 
recorder.ondataavailable = e => audioChunks.push(e.data); 
}); 

$("#stopRecord").click(function(){ 
$("#stopRecord").hide(); 
$("#micBtn").show(); 
recorder.stop(); 
recorder.onstop = ()=>{ 
let blob = new Blob(audioChunks, { type: "audio/webm" }); 
sendMessage(blob,"audio"); 
}; 
}); 

$("#chatHeader").click(function(){ 
let u = $("#chatUserName").text().trim(); 
if(u !== "") window.location.href = "user_profile.php?user=" + u; 
}); 

$("#messageText").on("keypress", function(e){ 
if(e.key === "Enter"){ 
e.preventDefault(); 
sendMessage(); 
} 
}); 
let wavePlayers = [];

function initWaveSurfer() {
    $(".wave").each(function () {
        let file = $(this).data("file");

        // Destroy old wavesurfer if exists
        if($(this).data("wavesurfer")) {
            $(this).data("wavesurfer").destroy();
        }

        let wavesurfer = WaveSurfer.create({
            container: this,
            waveColor: '#8fa6c6',
            progressColor: '#1a73e8',
            barWidth: 2,
            height: 30,
            responsive: true
        });

        $(this).data("wavesurfer", wavesurfer);
        wavePlayers.push(wavesurfer);

        wavesurfer.load(file);

        $(this).siblings(".play-btn").off("click").on("click", function () {
            wavePlayers.forEach(w => w.pause());
            wavesurfer.playPause();
            $(".play-btn").text("▶");
            $(this).text(wavesurfer.isPlaying() ? "⏸" : "▶");
        });

        wavesurfer.on("finish", () => {
            $(this).siblings(".play-btn").text("▶");
        });

        wavesurfer.on("audioprocess", () => {
            let t = Math.floor(wavesurfer.getCurrentTime());
            $(this).siblings(".voice-time").text(t < 10 ? "0:0" + t : "0:" + t);
        });
    });
}

$("#openRequests").click(function(){ window.location.href = "user_requests.php"; }); 
let zoom = 1;
$("#myProfileIcon, #profileIcon").on("click", function(e){
    e.stopPropagation();
    $("#dropdownMenu").toggle();
});



// Zoom In (small step)
$("#zoomIn").click(function(){
    zoom += 0.2; // increase 20% per click
    $("#fullImg").css("transform","scale(" + zoom + ")");
});


// Zoom Out (small step)
$("#zoomOut").click(function(){
    zoom = Math.max(0.2, zoom - 0.2); 
    $("#fullImg").css("transform","scale(" + zoom + ")");
});


// Close Popup
$("#closePopup, #popupOverlay").click(function(){
    zoom = 1;
    $("#popupOverlay, #fullPopup").hide();
});

// chat item click
$(document).on("click",".user", function(){
    openChat($(this).data("username"));
});

// chat icon click also opens chat
$(document).on("click", ".user .profile-pic, .user .dp-initials", function(e){
    e.stopPropagation();
    let user = $(this).closest(".user").data("username");
    openChat(user);
});

// Sidebar DP Click => Open Popup (NOT open chat)
$(document).on("click", ".chat-dp-thumb", function(e){
    e.stopPropagation(); // stops chat opening

    let img = $(this).attr("src");
    
    // If using initials instead of img
    if(!img){
        return; // no DP, no popup
    }

    $("#fullImg").attr("src", img);
    $("#popupOverlay, #fullPopup").show();
});
function loadPendingRequestsCount(){
    $.get("pending_requests_count.php", function(count){
        count = parseInt(count || 0);
        if(count > 0){
            $("#reqBadge").text(count).show();
        }else{
            $("#reqBadge").hide();
        }
    });
}

loadPendingRequestsCount();
setInterval(loadPendingRequestsCount, 2000);

$("#chatBox").on("scroll", function(){
    let chatBox = $(this);
    let atBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 5;
    if(atBottom){
        $("#scrollDownBtn").hide();
    }else{
        $("#scrollDownBtn").show();
    }
});

$("#scrollDownBtn").click(function(){
    let chatBox = $("#chatBox");
    chatBox.scrollTop(chatBox[0].scrollHeight);
    $(this).hide();
});
// Right-click a single message
$(document).on("contextmenu", ".msg", function(e){
    e.preventDefault();
    
    let id = $(this).data("id");
    let user = $("#chatUserName").text().trim();

    if(confirm("Delete this message?")) {
        $.post("delete_message.php", {id:id}, function(res){
            if(res.trim() === "ok"){
                $(".msg[data-id='"+id+"']").fadeOut(200, function(){
                    $(this).remove();
                });
                loadMessages(user);
            } else {
                alert("Delete failed!");
            }
        });
    }
});


$(document).on("contextmenu", ".user", function(e){
    e.preventDefault();
    let user = $(this).data("username");
    if(confirm("Delete all messages with " + user + " ?")){
        $.post("delete_conversation.php", {user:user}, function(res){
            loadMessages($("#chatUserName").text().trim());
            loadChats();
            // if current chat is same user, clear window
            if($("#chatUserName").text().trim() === user){
                $("#chatBox").html('<p style="color:#999;">Start new conversation</p>');
            }
        });
    }
});
$("#viewPhotoBtn").click(function(){
    let img = $("#myProfileIcon").attr("src");

    if(!img){
        alert("No profile photo found!");
        return;
    }

    $("#fullImg").attr("src", img);
    $("#popupOverlay, #fullPopup").show();
    zoom = 1;
    $("#fullImg").css("transform", "scale(1)");
});

</script> 
<script src="https://unpkg.com/wavesurfer.js"></script>

</body> 
</html>