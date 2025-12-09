<?php
session_start();
if (!isset($_SESSION['username'])) exit;

include(__DIR__ . '/include/db_connect.php');

$current = $_SESSION['username'];
$other   = $_POST['username'] ?? '';
if ($other === '') exit;

// mark unread received messages as delivered (not read)
$conn->query("
    UPDATE messages 
    SET delivered = 1 
    WHERE sender = '$other' AND receiver = '$current' AND delivered = 0
");

$stmt = $conn->prepare("
    SELECT 
        id, sender, receiver, message, media_path, audio_path,
        created_at, is_read, delivered
    FROM messages
    WHERE (sender = ? AND receiver = ?)
       OR (sender = ? AND receiver = ?)
    ORDER BY created_at ASC
");
$stmt->bind_param("ssss", $current, $other, $other, $current);
$stmt->execute();
$res = $stmt->get_result();

$lastDate = '';

while ($row = $res->fetch_assoc()) {

    $msgDate = date('Y-m-d', strtotime($row['created_at']));
    $msgTime = date('h:i A', strtotime($row['created_at']));
    $msgId   = (int)$row['id'];
    $isRead  = (int)$row['is_read'];
    $delivered = (int)$row['delivered'];
    $class   = ($row['sender'] === $current) ? 'me' : 'them';

    if ($msgDate != $lastDate) {
        $lastDate = $msgDate;
        echo "<div class='date-separator'>$msgDate</div>";
    }

    echo "<div class='msg $class' data-id='$msgId'>";

    if (!empty($row['message']))
        echo "<div class='msg-text'>".nl2br(htmlspecialchars($row['message']))."</div>";

    if (!empty($row['media_path']) && empty($row['audio_path'])) {
        $ext = strtolower(pathinfo($row['media_path'], PATHINFO_EXTENSION));
        $safeMedia = htmlspecialchars($row['media_path']);

        if (in_array($ext, ['jpg','jpeg','png','gif','webp']))
            echo "<img src='$safeMedia' style='max-width:220px;border-radius:10px;'>";
        elseif (in_array($ext, ['mp4','webm','mkv']))
            echo "<video controls style='max-width:220px;border-radius:10px;'>
                    <source src='$safeMedia'>
                  </video>";
        else
            echo "<a href='$safeMedia' download>Download File</a>";
    }

    if (!empty($row['audio_path'])) {
        $safeAudio = htmlspecialchars($row['audio_path']);
        echo "
        <div class='msg-audio'>
            <button class='play-btn' data-audio='$safeAudio'>▶</button>
            <div class='wave' data-file='$safeAudio'></div>
            <div class='voice-time'></div>
        </div>";
    }
    echo "<div class='msg-time' style='color:#fff;'>$msgTime";

    if ($row['sender'] === $current) {
        
        if($isRead == 1){
            // READ → Blue double tick
            echo " <span class='msg-status read' style='color:#1a73e8;'>
                    <i class='fa fa-check-double'></i>
                  </span>";
        }
        elseif($delivered == 1){
            // Delivered but not read → White double tick
            echo " <span class='msg-status double' style='color:#fff;'>
                    <i class='fa fa-check-double'></i>
                  </span>";
        }
        else{
            // Sent only → Single grey tick
            echo " <span class='msg-status single' style='color:#666;'>
                    <i class='fa fa-check'></i>
                  </span>";
        }
    }

    echo "</div>";

    // delete only own message
    if ($row['sender'] === $current)
        echo "<span class='msg-delete' data-id='$msgId' style='color:#fff;'>🗑</span>";

    echo "</div>";
}
?>
