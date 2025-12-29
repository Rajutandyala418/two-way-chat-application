<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
session_start();
session_write_close(); 
include "include/db_connect.php";

$user = $_POST['user'];

$q = $conn->query("SELECT online_status, last_seen FROM users WHERE username='$user'");
$d = $q->fetch_assoc();

if($d['online_status']){
    echo "<span style='color:green;'>Online</span>";
}
else{
    $time = strtotime($d['last_seen']);
    $diff = time() - $time;

    if($diff < 60) echo "Last seen just now";
    else if($diff < 3600) echo "Last seen ".floor($diff/60)." minutes ago";
    else if($diff < 86400) echo "Last seen ".floor($diff/3600)." hours ago";
    else echo "Last seen ".date("d M h:i A",$time);
}
?>
