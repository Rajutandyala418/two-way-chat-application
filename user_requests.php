<?php 
session_start();
require_once __DIR__ . '/include/auth_check.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
include(__DIR__ . '/include/db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Friend Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f3f6fb;}

/* HEADER BAR */
.header-bar{
    width:100%; 
    background:#fff; 
    border-bottom:1px solid #e4e9f1;
    display:flex; 
    flex-direction:column; 
    align-items:center;
    padding:8px 15px; 
    position:fixed; 
    top:0; 
    left:0; 
    z-index:999;
}
.top-row{
    width:100%; display:flex; align-items:center; justify-content:space-between;
}
.right-btns{display:flex; gap:8px;}
.header-bar button{
    background:#1a73e8; color:#fff; border:none; padding:7px 14px;
    border-radius:6px; font-size:13px; display:flex; align-items:center;
    gap:6px; cursor:pointer;
}
.second-title{
    font-size:27px; color:#1a73e8; margin-top:6px; font-weight:600;
}

/* MAIN BOX */
.main-box{
    margin-top:90px;
    padding:15px;
}

/* SEARCH + FILTER */
.controls{
    display:flex; gap:10px; margin-bottom:10px;
}
#searchReq,#filterReq{
    width:50%; padding:10px; border-radius:6px; border:1px solid #ccc;
}

/* TABLE WRAPPER */
.table-wrapper {
    width: 100%;
    height: 70vh;            /* FIXED HEIGHT */
    overflow-y: auto;        /* SCROLL INSIDE TABLE */
    overflow-x: auto;
    background: #fff;
    border-radius: 10px;
    border:1px solid #e4e9f1;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    min-width:600px;
}

th,td{
    border-bottom:1px solid #f0f0f0;
    text-align:center;
    padding:10px;
    font-size:14px;
}
.profile-cell{
    width:55px;
    height:55px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.pic, .initials{
    width:48px;
    height:48px;
}
.pic{
    object-fit:cover;
    border:2px solid #1a73e8;
}
.initials{
    background:#1a73e8;
    color:#fff;
    font-size:16px;
}


.approve{background:#28a745;color:#fff;border:none;padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer;}
.reject{background:#dc3545;color:#fff;border:none;padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer;}

/* MOBILE FIX */
@media(max-width:600px){
    th, td { font-size:13px; padding:8px; }
    .profile-cell { width:40px; height:40px; }
    .pic, .initials { width:38px; height:38px; }
}

#imgOverlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.7);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:2000;
}

.popup-box{
    background:#fff;
    padding:12px;
    border-radius:12px;
    position:relative;
    display:flex;
    justify-content:center;
    align-items:center;
}

#imgPreview{
    max-width:40vw;
    max-height:60vh;
    border-radius:10px;
}

.close-popup{
    position:absolute;
    top:10px;
    right:10px;
    background:#ff3333;
    color:#fff;
    width:28px;
    height:28px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    cursor:pointer;
    font-weight:bold;
}

@media(max-width:600px){
    #imgPreview{
        max-width:95vw;
        max-height:75vh;
    }
}

</style>
</head>

<body>

<div class="header-bar">
    <div class="top-row">
        <button onclick="window.location.href='dashboard.php'"><i class="fa fa-arrow-left"></i> Back</button>
        <div class="right-btns">
            <button onclick="window.location.href='add_friends.php'">Add Friends</button>
            <button onclick="window.location.href='friends.php'">Friends</button>
        </div>
    </div>
    <h2 class="second-title">Friend Requests</h2>
</div>

<div class="main-box">

    <!-- SEARCH + FILTER -->
    <div class="controls">
        <input type="text" id="searchReq" placeholder="Search requests...">
        <select id="filterReq">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="reqList"></tbody>
        </table>
    </div>

</div>

<div id="imgOverlay">
    <div class="popup-box">
        <span class="close-popup">✖</span>
        <img id="imgPreview">
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
function loadRequests(){
    $.post("load_requests.php", function(data){
        $("#reqList").html(data);

        let rows = $(".reqRow").get();
        rows.sort(function(a,b){
            let order = { "pending":1, "accepted":2, "rejected":3 };
            return order[$(a).data("status")] - order[$(b).data("status")];
        });

        $("#reqList").html(rows);
        applyFilters();
    });
}

function applyFilters(){
    let q = $("#searchReq").val().toLowerCase();
    let f = $("#filterReq").val();

    $(".reqRow").each(function(){
        let name = $(this).data("name").toLowerCase();
        let status = $(this).data("status");
        $(this).toggle(name.includes(q) && (f === "all" || f === status));
    });
}

$("#searchReq").keyup(applyFilters);
$("#filterReq").change(applyFilters);

function reqAction(type,user){
    $.post("update_request_status.php",{action:type,user:user},function(){
        loadRequests();
    });
}

$(document).on("click",".pic, .initials",function(){
    let src=$(this).attr("src");
    if(src){
        $("#imgPreview").attr("src",src);
        $("#imgOverlay").fadeIn(150);
    }
});
$("#imgOverlay").click(function(){ $(this).fadeOut(150); });

loadRequests();
setInterval(loadRequests,3000);
</script>

</body>
</html>
