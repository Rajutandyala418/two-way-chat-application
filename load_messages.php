<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
session_start();
session_write_close(); 
require_once __DIR__ . '/include/auth_check.php';
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit("login_required");
}
session_write_close();
include("include/db_connect.php");
date_default_timezone_set('Asia/Kolkata');

$me   = $_SESSION['username'];
$user = trim($_POST['user'] ?? "");

if ($user === "") {
    exit("<p style='color:#999;text-align:center;'>No chat selected.</p>");
}

$stmt = $conn->prepare("
    SELECT id, sender, receiver, message, media_path, audio_path, is_read, is_seen, reply_to, created_at 
    FROM messages 
    WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?)
    ORDER BY created_at ASC
");
$stmt->bind_param("ssss", $me, $user, $user, $me);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<p style='color:#999;text-align:center;'>Start a conversation with <strong>$user</strong></p>";
    exit;
}

$lastDate = "";

while ($row = $res->fetch_assoc()) {

    $id       = $row['id'];
    $sender   = $row['sender'];
    $msg      = $row['message'];
    $media    = $row['media_path'];
    $audio    = $row['audio_path'];
    $replyId  = $row['reply_to'];
    $ts       = strtotime($row['created_at']);
    if ($ts <= 0) $ts = time();

    $isoTime  = date("c", $ts);
    $dispTime = date("g:i A", $ts);
    $dateKey  = date("Y-m-d", $ts);

    if ($lastDate !== $dateKey) {
        $label = date("d M Y", $ts);
        echo "<div class='date-separator'>$label</div>";
        $lastDate = $dateKey;
    }

    $align = ($sender == $me) ? "me" : "them";

    echo "<div class='msg $align' data-id='$id' data-time='$isoTime'>";

    if (!empty($replyId)) {
        $q = $conn->query("SELECT sender,message FROM messages WHERE id=$replyId");
        if ($q && $q->num_rows) {
            $r = $q->fetch_assoc();
            $replySender = $r['sender'];
            $replyMsg = htmlspecialchars(mb_substr($r['message'],0,50));
            echo "<div style='background:#eef3ff;padding:6px;border-left:4px solid #1a73e8;border-radius:8px;margin-bottom:6px;font-size:13px;'>
                    <b>$replySender</b><br>$replyMsg
                  </div>";
        }
    }

    if (!empty($msg)) {
        echo nl2br(htmlspecialchars($msg));
    }

    if (!empty($media)) {
        $file = 'uploads/' . $media;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            echo "<br><img src='$file' style='max-width:230px;border-radius:12px;margin-top:8px;cursor:pointer;'>";
        } else {
            echo "<br><a href='$file' download style='margin-top:8px;color:#1a73e8;font-weight:600;display:inline-block;'>
                  <i class='fa fa-file'></i> Download File</a>";
        }
    }

    if (!empty($audio)) {
        echo "
        <div class='msg-audio' style='margin-top:8px;display:flex;align-items:center;gap:6px;'>
            <button class='play-btn'>▶</button>
            <div class='wave' data-file='uploads/$audio' style='width:170px;'></div>
        </div>";
    }

    $statusHtml = "";
    if ($sender == $me) {
        if ($row['is_seen']) $statusHtml = "<span style='color:#1a73e8'>✔✔</span>";
        else if ($row['is_read']) $statusHtml = "<span style='color:#777'>✔✔</span>";
        else $statusHtml = "<span style='color:#777'>✔</span>";
    }

    echo "
        <div class='msg-time'>
            <span class='time'>$dispTime</span>
            $statusHtml
        </div>
    ";

    echo "<div class='msg-reply-btn' data-id='$id' style='position:absolute;left:-26px;top:50%;transform:translateY(-50%);cursor:pointer;opacity:0.6;'>↩</div>";

    echo "<div class='msg-delete'><i class='fa fa-trash'></i></div>";

    echo "</div>";
}
?>
