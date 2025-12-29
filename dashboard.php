<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
include(__DIR__ . '/include/db_connect.php');

// return profile path when requested via AJAX (keeps compatibility)
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
$user = $conn->query("SELECT profile_pic FROM users WHERE user_id=".intval($_SESSION['user_id']))->fetch_assoc();
$pic = !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : '';

// Preselect chat from URL ?chat=username
$activeChat = isset($_GET['chat']) ? trim($_GET['chat']) : '';
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
/* --- Reset & base --- */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
html,body{height:100%;}
body{background:#f3f6fb;color:#223;}

/* --- Layout --- */
.app {
    display:flex;
    height:100vh;
    overflow:hidden;
}

/* Sidebar (left) */
.sidebar {
    width:320px;
    min-width:260px;
    background:#ffffff;
    border-right:1px solid #e1e8ee;
    display:flex;
    flex-direction:column;
    transition:transform .25s ease;
    z-index:20;
}

/* top icons row inside sidebar */
.sidebar-top {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px;
    border-bottom:1px solid #eef2f6;
    gap:8px;
}

/* left icons group */
.top-left {
    display:flex;
    align-items:center;
    gap:8px;
}

/* requests icon container (with badge above) */
.requests-wrap {
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
}
#reqBadge {
    position:absolute;
    top:-8px;
    right:-8px;
    background:#e53935;
    color:#fff;
    font-size:11px;
    border-radius:50%;
    padding:3px 6px;
    min-width:20px;
    text-align:center;
    display:none;
}

/* center title */
.sidebar-title {
    text-align:center;
    flex:1;
    font-weight:600;
    color:#2b3e55;
    font-size:16px;
}

/* profile icon on right inside sidebar top */
.mobile-profile {
    display:flex;
    align-items:center;
    gap:8px;
}
.my-profile-pic {
    width:40px;height:40px;border-radius:50%;cursor:pointer;border:2px solid #1a73e8;object-fit:cover;
}
.my-dp-initials {
    width:40px;height:40px;border-radius:50%;display:flex;justify-content:center;align-items:center;
    color:#fff;font-weight:600;font-size:15px;cursor:pointer;background:#1a73e8;border:2px solid #1a73e8;
}

/* search */
.search-box{padding:10px;border-bottom:1px solid #eef2f6;}
.search-box input{width:100%;padding:10px;border-radius:8px;border:1px solid #cdd7e1;outline:none;}

/* chats list */
.user-list{overflow:auto;flex:1;padding:6px 0;}
.user {
    padding:10px 12px;
    display:flex;
    align-items:center;
    gap:10px;
    border-bottom:1px solid #f1f1f1;
    cursor:pointer;
    background:transparent;
}
.user:hover { background:#f6fbff; }
.user .profile-pic, .user .dp-initials {
    width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid transparent;
}
.user .meta { flex:1; display:flex;flex-direction:column;gap:4px; }
.user .meta .name { font-weight:600; color:#243444; }
.user .meta .last { font-size:13px;color:#6b7785; display:flex;justify-content:space-between;align-items:center; }
.user .right { display:flex;flex-direction:column;align-items:flex-end;gap:6px; }

/* unread bubble */
.badge-count{
    background:#e53935;
    color:#fff;
    font-size:12px;
    border-radius:12px;
    padding:4px 8px;
}

/* chat area (right) */
.chat-area {
    flex:1;
    display:flex;
    flex-direction:column;
    position:relative;
    min-width:0;
}

/* chat header */
.chat-header {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 16px;
    border-bottom:1px solid #e6edf3;
    background:#fff;
}
.chat-header .back-btn{display:none;}
.chat-dp{width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid #1a73e8;cursor:pointer;}
.chat-initials {
    width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#1a73e8;color:#fff;font-weight:700;font-size:16px;cursor:pointer;border:2px solid #1a73e8;
}
.chat-header .name { font-size:16px;font-weight:700;color:#243444; cursor:pointer; }
.chat-header .meta { margin-left:auto; color:#6b7785; font-size:13px; }

/* chat box */
.chat-box {
    flex:1;
    overflow:auto;
    padding:18px;
    background: linear-gradient(180deg,#f9fbff 0,#f3f6fb 100%);
}

/* date separator */
.date-separator{
    text-align:center;
    font-size:12px;
    color:#7a8794;
    margin:12px 0;
}

/* messages */
.msg {
    max-width:70%;
    margin-bottom:10px;
    padding:10px 12px;
    border-radius:12px;
    position:relative;
    word-wrap:break-word;
    box-shadow:0 1px 0 rgba(0,0,0,0.03);
    display:block;
    width:max-content;
}

.msg.me {
    margin-left:auto;
    background:#0b2a7a;
    color:#ffffff;
}
.msg.me { margin-left:auto; margin-right:4px; }
.msg.them { margin-left:4px; }

.msg.them {
    margin-right:auto;
    background:#ece7ff; /* received background */
    color:#1a1a1a;      /* received text color */
}

/* message meta (time + status) */
.msg-time {
    display:flex;
    align-items:center;
    gap:6px;
    font-size:11px;
    opacity:0.85;
    margin-top:6px;
    justify-content:flex-end;
}
.msg .msg-delete{
    position:absolute; top:50%; right:-26px; transform:translateY(-50%); font-size:12px; cursor:pointer; opacity:0.45;
}
.msg:hover .msg-delete{ opacity:1; }

/* audio & media */
.msg-audio audio { height:36px; border-radius:10px; background:#fff; }

/* chat input */
.chat-input {
    display:flex;
    align-items:center;
    gap:8px;
    padding:12px;
    border-top:1px solid #e6edf3;
    background:#fff;
}
.chat-input input[type="text"]{flex:1;padding:10px;border-radius:8px;border:1px solid #d6dfe8;outline:none;font-size:14px;}
.chat-input button { border:none;background:transparent;padding:8px;border-radius:8px;cursor:pointer;font-size:18px; }
#sendBtn{ background:#1a73e8;color:#fff;padding:8px 14px;border-radius:8px; }

/* popup overlay for image preview */
#popupOverlay{ position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:999; display:none; }
#fullPopup{ position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1000; display:none; }
    #fullPopup.no-image {
    background:#ffffff;
    border-radius:12px;
    padding:10px;
    box-shadow:0 8px 30px rgba(0,0,0,0.25);
}

#fullImg{ max-width:90vw; max-height:80vh; border-radius:10px; }

.dropdown{
    position:absolute;
    background:#fff;
    border:1px solid #e1e8ee;
    border-radius:10px;
    width:180px;
    display:none;
    flex-direction:column;
    z-index:1000;
    box-shadow:0 4px 14px rgba(0,0,0,0.1);
}



.dropdown button{ border:none; background:#fff; padding:12px; width:100%; text-align:left; font-size:14px; border-bottom:1px solid #e1e8ee; cursor:pointer; }
.dropdown button:last-child{ border-bottom:none; }

/* responsive rules */
@media (max-width: 900px) {
    .sidebar { width:100%; position:fixed; left:0; top:0; bottom:0; height:100vh; transform:translateX(0); z-index:30; }
    .chat-area { display:none; } /* hide chat area by default on small screens; show when opening a chat */
    .chat-area.open { display:flex; position:fixed; left:0; top:0; right:0; bottom:0; z-index:40; background:#fff; }
    .sidebar-title { display:none; } /* we will show chats label below search instead */
    .chat-header .back-btn { display:inline-flex; margin-right:6px; font-size:18px; cursor:pointer; }
    .mobile-only { display:inline-block; }
}
@media (max-width: 900px){
    #emojiPicker{
        left: 5px !important;
        right: 5px;
        bottom: 65px;
        max-width: 95vw;
    }
}

/* Desktop specific small tweaks */
@media (min-width: 901px) {
    .mobile-only { display:none; }
    .sidebar { position:relative; transform:none; }
    .chat-area { display:flex; }
}
@media (max-width: 900px){
    .chat-input{
        display: flex;
        align-items: center;
        flex-wrap: nowrap;   /* 🔥 force single row */
        gap: 6px;
    }

    #messageText{
        flex: 1;             /* input expands */
        min-width: 0;        /* 🔥 important for flexbox */
        font-size: 14px;
    }

    .chat-input button{
        padding: 6px;
        font-size: 16px;
    }

    #sendBtn{
        padding: 6px 10px;
        font-size: 14px;
    }
}

</style>
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <div class="top-left">
                <div class="requests-wrap" title="User Requests">
                    <i class="fa fa-user-plus" id="openRequests" style="font-size:20px;color:#2b3e55;cursor:pointer;"></i>
                    <span id="reqBadge"></span>
                </div>
            </div>

            <div class="sidebar-title">Chats</div>

            <div class="mobile-profile" title="Profile">
                <?php if($pic): ?>
                    <img src="<?= htmlspecialchars($pic) ?>" class="my-profile-pic" id="mobileProfileIcon">
                <?php else: ?>
                    <div class="my-dp-initials" id="mobileProfileIcon"><?= htmlspecialchars($initials) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="search-box">
            <input type="text" id="searchUser" placeholder="Search in your chats...">
        </div>
        <div class="user-list" id="userList">
            <?php include('fetch_chats.php'); // this should output .user elements; each .user should carry data-username and data-lasttime and possibly .badge-count ?>
        </div>
    </div>

    <!-- CHAT AREA -->
    <div class="chat-area <?php if($activeChat) echo 'open'; ?>" id="chatArea">

        <div class="chat-header" id="chatHeader" style="<?= $activeChat ? '' : 'display:none;' ?>">
            <div class="back-btn" id="backToSidebar" style="display:none;"><i class="fa fa-arrow-left"></i></div>
            <div id="chatDpWrap">
                <img src="" id="chatUserPic" class="chat-dp" style="display:none;">
                <div class="chat-initials" id="chatInitials" style="display:none;"></div>
            </div>
            <div class="name" id="chatUserName"></div>
            <div class="meta" id="chatMeta"></div>
        </div>

        <div class="chat-box" id="chatBox">
            <p style="color:#999;">Select a chat to start messaging</p>
        </div>

        <div class="chat-input" id="chatInput" style="display:none;">
            <input type="file" id="sendFile" style="display:none;">
            <input type="file" id="docFile" style="display:none;">
            <button id="emojiBtn" title="Emoji">
    😊
</button>

            <button id="docBtn" title="Attach file"><i class="fa fa-paperclip"></i></button>
            <button id="micBtn" title="Record voice"><i class="fa fa-microphone"></i></button>
            <button id="stopRecord" style="display:none;">Stop</button>
            <input type="text" id="messageText" placeholder="Type a message..." autocomplete="off">
            <button id="sendBtn" title="Send">Send</button>
        </div>

    </div>

</div>

<!-- popup overlay for image preview -->
<div id="popupOverlay"></div>
<div id="fullPopup">
    <div style="position:relative; text-align:center;">
  <button id="closePopup" style="
    position:absolute;
    right:-12px;
    top:-12px;
    background:#ff4747;
    color:#fff;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
">✕</button>

        <img id="fullImg" style="display:none;">
        <div id="popupMessage" style="display:none;font-size:16px;color:#333;padding:20px;">
            Profile picture is not available
        </div>
    </div>
</div>


<!-- profile dropdown -->
<div class="dropdown" id="dropdownMenu">
    <button id="viewPhotoBtn">View Photo</button>
    <button onclick="window.location.href='profile.php'">Profile</button>
    <button onclick="window.location.href='update_password.php'">Update Password</button>
    <button onclick="window.location.href='logout.php'">Logout</button>
</div>
<div id="emojiPicker" style="
    position:absolute;
    display:none;
    z-index:9999;
"></div>

<script src="https://unpkg.com/emoji-mart@latest/dist/browser.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://unpkg.com/wavesurfer.js"></script>
<script>
$.ajaxSetup({ cache:false });
setInterval(function(){
 $.get("update_online_status.php");
}, 5000);

/* ======================
   Dashboard main script
   ====================== */
let chatOpenedOnce = {};
let replyTo = null;

const sndSend = new Audio('send.mp3');
const sndRecv = new Audio('receive.mp3');
const sndNotify = new Audio('notify.mp3');

let currentChat = "<?php echo htmlspecialchars($activeChat); ?>";
let lastMsgIdByUser = {};
let chatPollInterval = 3500;
let chatsPollInterval = 4000;
let wavesInitialized = false;

let loadingChats = false;

function loadChats(callback){
    if(loadingChats) return;
    loadingChats = true;
$.ajax({
   url:"fetch_chats.php",
   method:"POST",
   data:{load:1, _t:Date.now()},
   cache:false,
        success: function(data){
            if($.trim(data) !== ""){
                $("#userList").html(data || "");
				applyUserSearch();
				sortChatsByLastTime();


                if(currentChat){
                    $(".user[data-username='"+currentChat+"']")
                        .find(".badge-count").remove();
                }
                applyUserSearch();
                sortChatsByLastTime();
            }
        },
        complete: function(){
            loadingChats = false;
            if(typeof callback === "function") callback();
        }
    });
}
$("#mobileProfileIcon").on("click", function (e) {
    e.stopPropagation();

    let dropdown = $("#dropdownMenu");

    if (dropdown.is(":visible")) {
        dropdown.hide();
        return;
    }

    let icon = this.getBoundingClientRect();
    let dropdownWidth = dropdown.outerWidth();
    let screenWidth = window.innerWidth;

    let leftPos;

    // 🔹 MOBILE: open dropdown to LEFT side
    if (screenWidth <= 900) {
        leftPos = icon.right - dropdownWidth;
        if (leftPos < 8) leftPos = 8; // safety margin
    } 
    // 🔹 DESKTOP: keep current behavior
    else {
        leftPos = icon.left;
    }

    dropdown.css({
        top: icon.bottom + 8 + "px",
        left: leftPos + "px",
        display: "flex"
    });
});


function sortChatsByLastTime(){
    let items = $("#userList .user").get();

    items.sort(function(a,b){
        let ta = $(a).data('lasttime');
        let tb = $(b).data('lasttime');

        ta = parseInt(ta) || 0;
        tb = parseInt(tb) || 0;

        return tb - ta;
    });

    $.each(items, function(i, el){
        $("#userList").append(el);
    });
}


/* apply search in sidebar */
$("#searchUser").on("input", function(){
    applyUserSearch();
});
function applyUserSearch(){
    let q = $("#searchUser").val().toLowerCase();
    $("#userList .user").each(function(){
        let name = $(this).data("username") ? $(this).data("username").toString().toLowerCase() : $(this).find(".name").text().toLowerCase();
        $(this).toggle(name.includes(q));
    });
}

/* show pending requests count */
function loadPendingRequestsCount(){
    $.get("pending_requests_count.php?time="+Date.now(), function(count){
        count = parseInt(count || 0);
        if(count > 0) $("#reqBadge").text(count).show();
        else $("#reqBadge").hide();
    });
}

/* ------------------------
   Opening a chat
   ------------------------ */
function openChat(username){
    currentChat = username;
    // update url
    try {
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('chat', username);
        history.replaceState({}, '', newUrl);
    } catch(e){}
    // show chat header and input
    $("#chatHeader").show();
    $("#chatInput").show();
    $("#chatArea").addClass('open');

    // mobile: hide sidebar
    if(window.innerWidth <= 900){
        $("#sidebar").hide();
        $("#chatArea").addClass('open');
        $("#backToSidebar").show();
    } else {
        $("#backToSidebar").hide();
        $("#sidebar").show();
    }
$("#messageText").val("");
    // set header name
    $("#chatUserName").text(username).attr("data-user", username);
    $("#chatHeader").attr("data-user", username);

    // clear prev avatars in header
    $("#chatUserPic").hide().attr("src", "");
    $("#chatInitials").hide();

    // fetch dp for user
    $.post("fetch_dp.php", {username: username}, function(pic){
        pic = pic.trim();
        if(pic === ""){
            $("#chatUserPic").hide();
            $("#chatInitials").text(username.substring(0,2).toUpperCase()).show();
        } else {
            $("#chatInitials").hide();
            $("#chatUserPic").attr("src", pic).show();
        }
    });
    $.post("user_status.php",{user:username},function(status){
    $("#chatMeta").html(status);
});


    // hide badge for that user
    $(".user[data-username='"+username+"']").find(".badge-count").hide();
let sidebarItem = $(".user[data-username='"+username+"']");
let lastId = parseInt(sidebarItem.data("lastid")) || 0;
lastMsgIdByUser[username] = lastId;

    loadMessages(username, true);
    markAsRead(username);
}

/* back button on mobile to show sidebar */
$("#backToSidebar").on("click", function(){
    $("#sidebar").show();
    $("#chatArea").removeClass('open');
    $("#chatHeader").hide();
    $("#chatInput").hide();
    // remove query param
    try {
        const newUrl = new URL(window.location);
        newUrl.searchParams.delete('chat');
        history.replaceState({}, '', newUrl);
    }catch(e){}
});

/* clicking header name opens user_profile */
$(document).on("click", "#chatHeader .name, #chatHeader .chat-initials, #chatHeader .chat-dp", function(){
    let u = $("#chatUserName").attr("data-user");
    if(u && u !== "") window.location.href = "user_profile.php?user=" + encodeURIComponent(u);
});
/* click on initials (no image available) */
$(document).on("click", ".dp-initials, .chat-initials, .my-dp-initials", function(e){
    e.stopPropagation();

    $("#dropdownMenu").hide();
$("#fullPopup").addClass("no-image");
$("#fullImg").hide();
$("#popupMessage").show().text("Profile picture is not available");

$("#popupOverlay, #fullPopup").fadeIn(150);

});



$(document).on("click", function(e){
    if(!$(e.target).closest("#dropdownMenu, #mobileProfileIcon, #myProfileIcon").length){
        $("#dropdownMenu").hide();
    }
});
$("#viewPhotoBtn").on("click", function(e){
    e.stopPropagation();

    $("#dropdownMenu").hide();

    let img = $("#mobileProfileIcon").attr("src") || $("#myProfileIcon").attr("src");

    if(img && img.trim() !== ""){
        $("#fullPopup").removeClass("no-image");
        $("#fullImg").attr("src", img).show();
        $("#popupMessage").hide();
    } else {
        $("#fullPopup").addClass("no-image");
        $("#fullImg").hide();
        $("#popupMessage").show().text("Profile picture is not available");
    }

    $("#popupOverlay, #fullPopup").fadeIn(150);
});


/* image popup close */
$("#closePopup, #popupOverlay").on("click", function(){
    $("#popupOverlay, #fullPopup").hide();
    $("#fullPopup").removeClass("no-image");
});

/* ------------------------
   Load messages
   ------------------------ */
function loadMessages(user, scrollToBottom){
    if(!user) return;

    $.post("load_messages.php?t=" + Date.now(), { user: user }, function(res){

        // 1️⃣ Inject messages
        $("#chatBox").html(res);

        // 2️⃣ IMPORTANT: ensure messages stay read (reload / hosting fix)
        markAsRead(user);

        // 3️⃣ UI formatting
        insertDateSeparators();
        initWaveSurfer();

        // 4️⃣ Scroll handling
        let chatBox = $("#chatBox");
        if(scrollToBottom){
            chatBox.scrollTop(chatBox[0].scrollHeight);
        }

        // 5️⃣ Incoming sound logic (ONLY for real new messages)
        let lastMsg = $("#chatBox .msg").last();

        if(lastMsg.length){
            let lastId = parseInt(lastMsg.data("id")) || 0;

            // FIRST time opening this chat → NO sound
            if(!chatOpenedOnce[user]){
                lastMsgIdByUser[user] = lastId;
                chatOpenedOnce[user] = true;
                return;
            }

            let prevLast = lastMsgIdByUser[user] || 0;

            // Play sound ONLY if new message arrives while chat is open
            if(lastId > prevLast && lastMsg.hasClass("them")){
                sndRecv.play();
            }

            lastMsgIdByUser[user] = lastId;
        }

    });
}


/* insert date separators between messages (best-effort).
   This function reads each .msg[data-time] where data-time is ISO or timestamp.
*/
function insertDateSeparators(){
    let lastDate = null;
    $("#chatBox .msg").each(function(){
        let t = $(this).data('time') || $(this).attr('data-time') || $(this).find('.msg-time').attr('data-time');
        if(!t) return;
        let d = new Date(t);
        if(isNaN(d.getTime())) {
            // try parse as numeric seconds
            let n = parseInt(t);
            if(!isNaN(n)) d = new Date(n*1000);
        }
        if(isNaN(d.getTime())) return;
        let day = d.toDateString();
        if(lastDate !== day){
            $(this).before('<div class="date-separator">'+ formatDateForSeparator(d) +'</div>');
            lastDate = day;
        }
        // attach nicely formatted time into msg-time element if present
        let timeStr = formatTime(d);
        $(this).find('.msg-time .time').text(timeStr);
    });
}

function formatDateForSeparator(d){
    let now = new Date();
    const oneDay = 24*3600*1000;
    if(d.toDateString() === now.toDateString()) return "Today";
    let yesterday = new Date(now.getTime() - oneDay);
    if(d.toDateString() === yesterday.toDateString()) return "Yesterday";
    // else print date
    return d.toLocaleDateString();
}
function formatTime(d){
    // returns HH:MM in 24-hour or 12-hour as you prefer — we'll use 12-hour with AM/PM
    let hh = d.getHours();
    let mm = d.getMinutes();
    let ampm = hh >= 12 ? 'PM' : 'AM';
    hh = hh % 12; hh = hh ? hh : 12;
    mm = mm < 10 ? '0'+mm : mm;
    return hh + ':' + mm + ' ' + ampm;
}

/* ------------------------
   WaveSurfer init for audio messages
   ------------------------ */
let wavePlayers = [];
function initWaveSurfer(){
    $(".wave").each(function () {
        let file = $(this).data("file");
        if(!file) return;
        if($(this).data("wavesurfer")) {
            try { $(this).data("wavesurfer").destroy(); } catch(e){}
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
            $(".play-btn").text(wavesurfer.isPlaying() ? "⏸" : "▶");
        });
        wavesurfer.on("finish", () => {
            $(this).siblings(".play-btn").text("▶");
        });
    });
}

/* ------------------------
   Send message
   ------------------------ */
    $(document).on("click",".msg-reply-btn",function(){
    replyTo = $(this).closest(".msg").data("id");
    let text = $(this).closest(".msg").text().trim().substring(0,60);

    $("#replyBox").remove();

    $("#chatInput").prepend(`
        <div id="replyBox"
            style="background:#eef3ff;padding:6px;
            border-left:4px solid #1a73e8;border-radius:8px;
            margin-bottom:6px;font-size:13px;">
            Replying: ${text}
            <span id="cancelReply" 
                style="float:right;cursor:pointer;font-weight:700;">
                ✖
            </span>
        </div>
    `);
});
$(document).on("click","#cancelReply",function(){
    replyTo = null;
    $("#replyBox").remove();
});

function sendMessage(fileBlob = null, type = null){

    let msg = $("#messageText").val().trim();
    let to = $("#chatUserName").attr("data-user");

    if(!to){
        alert("Select a chat first");
        return;
    }

    let form = new FormData();
    form.append("to", to);
    form.append("message", msg);
if(replyTo){
    form.append("reply_to", replyTo);
}

    let file = null;

    if(fileBlob){
        file = fileBlob;
        if(type === "audio"){
            form.append("audio", fileBlob);
        }
    } else {
        if($("#sendFile")[0].files.length){
            file = $("#sendFile")[0].files[0];
            form.append("media", file);
        }

        if($("#docFile")[0].files.length){
            file = $("#docFile")[0].files[0];
            form.append("media", file);
        }
    }

    if(msg === "" && !file){
        alert("Please enter message or upload file");
        return;
    }

    $.ajax({
        url: "send_message.php",
        type: "POST",
        data: form,
        processData: false,
        contentType: false,
        success: function(res){
            $("#messageText").val("");
            replyTo = null;
$("#replyBox").remove();

            $("#sendFile").val("");
            $("#docFile").val("");
            sndSend.play();
            loadMessages(to, true);
            loadChats();
        }
    });
}

/* hooks for doc & mic */
$("#docBtn").click(function(){ $("#docFile").click(); });
$("#docFile").change(function(){ sendMessage(); });

let recorder; let audioChunks = [];
$("#micBtn").click(async function(){
    try {
        audioChunks = [];
        $("#micBtn").hide();
        $("#stopRecord").show();
        let stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        recorder = new MediaRecorder(stream);
        recorder.start();
        recorder.ondataavailable = e => audioChunks.push(e.data);
    } catch(e){
        alert("Microphone access denied or not available.");
        $("#micBtn").show();
        $("#stopRecord").hide();
    }
});
$("#stopRecord").click(function(){
    $("#stopRecord").hide();
    $("#micBtn").show();
    recorder.stop();
    recorder.onstop = ()=>{
       let blob = new Blob(audioChunks, { type: "audio/mp3" });
        sendMessage(blob,"audio");
    };
});

/* send on Enter */
$("#messageText").on("keypress", function(e){
    if(e.key === "Enter"){
        e.preventDefault();
        sendMessage();
    }
});
$("#sendBtn").on("click", function(){ sendMessage(); });

/* ------------------------
   Mark as read
   ------------------------ */
function markAsRead(user){
    $.post("mark_read.php", {user:user}, function(){
        // remove bubble instantly
        $(".user[data-username='"+user+"']").find(".badge-count").fadeOut();
    });
}


/* ------------------------
   Delete message / conversation (context menu already had)
   ------------------------ */
$(document).on("contextmenu", ".msg", function(e){
    e.preventDefault();
    let id = $(this).data("id");
    let user = $("#chatUserName").attr("data-user");
    if(!id) return;
    if(confirm("Delete this message?")) {
        $.post("delete_message.php", {id:id}, function(res){
            if(res.trim() === "ok"){
                $(".msg[data-id='"+id+"']").fadeOut(200, function(){ $(this).remove(); });
                loadMessages(user);
            } else alert("Delete failed!");
        });
    }
});
$(document).on("contextmenu", ".user", function(e){
    e.preventDefault();
    let user = $(this).data("username");
    if(!user) return;
    if(confirm("Delete all messages with " + user + " ?")){
        $.post("delete_conversation.php", {user:user}, function(res){
            loadMessages($("#chatUserName").text().trim());
            loadChats();
            if($("#chatUserName").text().trim() === user){
                $("#chatBox").html('<p style="color:#999;">Start new conversation</p>');
            }
        });
    }
});

/* ------------------------
   Periodic polling for chats & messages
   ------------------------ */
setInterval(function(){
    loadChats();
    loadPendingRequestsCount();
}, chatsPollInterval);

let loadingMessages = false;

setInterval(function(){
    if(!currentChat || loadingMessages) return;

    loadingMessages = true;
    loadMessages(currentChat, false);
    setTimeout(() => loadingMessages = false, 1200);
}, chatPollInterval);


/* initial load */
$(document).ready(function(){
    loadChats();
    loadPendingRequestsCount();

    // If URL had ?chat=username open it
    if(currentChat && currentChat.length > 0){
        openChat(currentChat);
    }
    if(currentChat && currentChat.length > 0){
    markAsRead(currentChat);
}

});

/* ensure sidebar visible on resize for desktop */
$(window).on('resize', function(){
    if(window.innerWidth > 900){
        $("#sidebar").show();
        $("#chatArea").removeClass('open');
        $("#chatHeader").show();
        $("#chatInput").show();
    } else {
        if(!currentChat) {
            $("#chatArea").removeClass('open');
            $("#chatHeader").hide();
            $("#chatInput").hide();
        } else {
            $("#sidebar").hide();
            $("#chatArea").addClass('open');
            $("#backToSidebar").show();
        }
    }
});

/* scroll handling: show a small indicator if not at bottom */
$(document).on('scroll', '#chatBox', function(){
    let chatBox = $(this);
    let atBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 5;
    // you can show/hide a scroll-down button here if desired
});

/* prevent multiple loads of wavesurfer on same elements when content reloaded */
function destroyWavePlayers(){
    try {
        wavePlayers.forEach(w => { if(w && w.destroy) w.destroy(); });
    } catch(e){}
    wavePlayers = [];
}

/* play notification when new incoming chat appears at top (optional) */
let lastTotalUnread = 0;
setInterval(function(){
    // compute unread total from badges
    let total = 0;
    $("#userList .badge-count").each(function(){
        let v = parseInt($(this).text()) || 0; total += v;
    });
    if(total > lastTotalUnread && total > 0) sndNotify.play();
    lastTotalUnread = total;
}, 3000);

/* clicking requests icon */
$("#openRequests").click(function(){ window.location.href = "user_requests.php"; });

$(document).on("click", ".profile-pic, .chat-dp, .my-profile-pic", function(e){
    e.stopPropagation();

    $("#dropdownMenu").hide();

    let img = $(this).attr("src");

   if(img && img.trim() !== ""){
    $("#fullPopup").removeClass("no-image");
    $("#fullImg").attr("src", img).show();
    $("#popupMessage").hide();
} else {
    $("#fullPopup").addClass("no-image");
    $("#fullImg").hide();
    $("#popupMessage").show().text("Profile picture is not available");
}

$("#popupOverlay, #fullPopup").fadeIn(150);

});


/* defensive: ensure dropdown is hidden on scroll */
$(window).on("scroll", function(){ $("#dropdownMenu").hide(); });

$(document).on("click", ".user", function () {
    let username = $(this).data("username");

    if(!username){
        console.warn("Username missing in chat item");
        return;
    }

    openChat(username);
});


$(document).ready(function () {

    let emojiPickerVisible = false;

    const picker = new EmojiMart.Picker({
        theme: "light",
        onEmojiSelect: function (emoji) {
            let input = $("#messageText");
            input.val(input.val() + emoji.native);
            input.focus();
        }
    });

    $("#emojiPicker").empty().append(picker);

$("#emojiBtn").on("click", function (e) {
    e.stopPropagation();

    const btn = this.getBoundingClientRect();
    const picker = $("#emojiPicker");

    if (picker.is(":visible")) {
        picker.hide();
        return;
    }

    // Desktop positioning
    if (window.innerWidth > 900) {
        picker.css({
            top: window.scrollY + btn.top - picker.outerHeight() - 8 + "px",
            left: window.scrollX + btn.left + "px"
        });
    }
    // Mobile positioning (keep existing behavior)
    else {
        picker.css({
            left: "5px",
            right: "5px",
            bottom: "65px",
            top: "auto"
        });
    }

    picker.show();
});


    // Close when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest("#emojiPicker, #emojiBtn").length) {
            $("#emojiPicker").hide();
            emojiPickerVisible = false;
        }
    });

});


</script>


</body>
</html>
