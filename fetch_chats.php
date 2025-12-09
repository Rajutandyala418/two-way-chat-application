<?php
session_start();
include(__DIR__.'/include/db_connect.php');

$me = $_SESSION['username'];

$query = "
SELECT 
    f.friend,
    u.profile_pic,
    lm.last_msg,
    lm.last_time,
    lm.last_id,
    uc.unread_count,
    CASE WHEN lm.last_time IS NULL THEN 1 ELSE 0 END AS no_chat
FROM
(
    SELECT 
        CASE WHEN sender='$me' THEN receiver ELSE sender END AS friend
    FROM friend_requests
    WHERE (sender='$me' OR receiver='$me') AND status='accepted'
) AS f

JOIN users u ON u.username = f.friend

LEFT JOIN (
    SELECT 
        t.friend,
        t.message AS last_msg,
        t.last_time,
        t.id AS last_id
    FROM (
        SELECT
            id,
            CASE WHEN sender='$me' THEN receiver ELSE sender END AS friend,
            message,
            created_at AS last_time,
            ROW_NUMBER() OVER (
                PARTITION BY CASE WHEN sender='$me' THEN receiver ELSE sender END
                ORDER BY created_at DESC
            ) AS rn
        FROM messages
        WHERE sender='$me' OR receiver='$me'
    ) AS t
    WHERE t.rn = 1
) AS lm ON lm.friend = f.friend

LEFT JOIN (
    SELECT sender AS friend, COUNT(*) AS unread_count
    FROM messages 
    WHERE receiver='$me' AND is_read=0
    GROUP BY sender
) AS uc ON uc.friend = f.friend

ORDER BY no_chat ASC, last_time DESC;
";

$res = $conn->query($query);

while($row = $res->fetch_assoc()):
$friend = $row['friend'];
$pic = $row['profile_pic'] ? 'uploads/'.$row['profile_pic'] : '';
$last_msg = $row['last_msg'] ?: "No messages yet";
$last_time = $row['last_time'] ? date("h:i A", strtotime($row['last_time'])) : "";
$unread = isset($row['unread_count']) ? (int)$row['unread_count'] : 0;
$last_id = $row['last_id'] ?: 0;

// message preview trim
$preview = strlen($last_msg) > 22 ? substr($last_msg, 0, 22) . "..." : $last_msg;
?>

<!-- ======================= CHAT USER LIST ITEM ======================= -->
<div class="user <?= $unread > 0 ? 'unread' : '' ?>" 
     data-username="<?= $friend ?>"
     data-lastid="<?= $last_id ?>"
     style="display:flex;justify-content:space-between;align-items:center;">

    <div style="display:flex;align-items:center;gap:10px;">
        <?php if($pic): ?>
            <img src="<?= $pic ?>" class="profile-pic chat-dp-thumb" style="width:38px;height:38px;">
        <?php else: ?>
            <div class="dp-initials chat-dp-thumb" style="width:38px;height:38px;">
                <?= strtoupper(substr($friend, 0, 2)) ?>
            </div>
        <?php endif; ?>

        <div>
            <strong><?= $friend ?></strong><br>
            <span style="font-size:12px;color:#777;max-width:140px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars($preview) ?>
            </span>
        </div>
    </div>

    <div style="text-align:right;">
        <div style="font-size:11px;color:#999;"><?= $last_time ?></div>

        <?php if($unread > 0): ?>
            <span class="badge-count"><?= $unread ?></span>
        <?php endif; ?>
    </div>
</div>

<?php endwhile; ?>
