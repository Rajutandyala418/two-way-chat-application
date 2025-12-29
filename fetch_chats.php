<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');

$me = $conn->real_escape_string($_SESSION['username']);
date_default_timezone_set('Asia/Kolkata');

/* ===================================
   MAIN CHAT LIST QUERY
==================================== */
$query = "
SELECT 
    f.friend,
    u.profile_pic,
    u.online_status,
    u.last_seen,

    lm.last_msg,
    lm.last_time,
    lm.last_id,

    COALESCE(uc.unread_count, 0) AS unread_count,
    CASE WHEN lm.last_id IS NULL THEN 1 ELSE 0 END AS no_chat
FROM (
    SELECT 
        CASE WHEN sender = '$me' THEN receiver ELSE sender END AS friend
        FROM friend_requests
        WHERE (sender = '$me' OR receiver = '$me')
        AND status = 'accepted'
) AS f

JOIN users u 
    ON u.username = f.friend

LEFT JOIN (
    SELECT 
        m1.friend,
        m1.message AS last_msg,
        m1.created_at AS last_time,
        m1.id AS last_id
    FROM (
        SELECT 
            CASE WHEN sender = '$me' THEN receiver ELSE sender END AS friend,
            message,
            created_at,
            id
        FROM messages
        WHERE sender = '$me' OR receiver = '$me'
    ) AS m1
    INNER JOIN (
        SELECT 
            CASE WHEN sender = '$me' THEN receiver ELSE sender END AS friend,
            MAX(id) AS max_id
        FROM messages
        WHERE sender = '$me' OR receiver = '$me'
        GROUP BY friend
    ) AS m2 
    ON m1.id = m2.max_id
) AS lm 
ON lm.friend = f.friend

LEFT JOIN (
    SELECT 
        sender AS friend, 
        COUNT(*) AS unread_count
    FROM messages
    WHERE receiver = '$me'
    AND is_read = 0
    GROUP BY sender
) AS uc 
ON uc.friend = f.friend

ORDER BY 
    no_chat ASC,
    lm.last_time IS NULL ASC,
    lm.last_time DESC
";

$res = $conn->query($query);
if (!$res) exit;

while ($row = $res->fetch_assoc()):
$friend = htmlspecialchars($row['friend']);
$pic = !empty($row['profile_pic']) ? 'uploads/'.$row['profile_pic'] : '';

$last_msg = $row['last_msg'] ?: "No messages yet";
$preview = mb_strlen($last_msg) > 22 ? mb_substr($last_msg, 0, 22) . "..." : $last_msg;

$unread = (int)$row['unread_count'];
$last_id = (int)$row['last_id'];

/* ---------- Time Formatting ---------- */
$lt  = $row['last_time'] ? strtotime($row['last_time']) : 0;

/* 🔥 VERY IMPORTANT — numeric timestamp for sorting */
$iso = $lt ? ($lt * 1000) : (time() * 1000);

if ($lt) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $msgDate = date('Y-m-d', $lt);

    if ($msgDate === $today)
        $last_time = date('g:i A', $lt);
    elseif ($msgDate === $yesterday)
        $last_time = "Yesterday";
    else
        $last_time = date('d M', $lt);
} else {
    $last_time = "";
}

$online = $row['online_status'];
?>
<div class="user <?= $unread > 0 ? 'unread' : '' ?>"
     data-username="<?= $friend ?>"
     data-lasttime="<?= $iso ?>"
     data-lastid="<?= $last_id ?>">

    <div style="display:flex;align-items:center;gap:12px;">

        <?php if ($pic): ?>
        <div style="position:relative;">
            <img src="<?= htmlspecialchars($pic) ?>"
            class="profile-pic"
            style="width:46px;height:46px;border-radius:50%;
            object-fit:cover;border:2px solid #1a73e8;">

            <?php if($online): ?>
            <span style="position:absolute;bottom:-2px;right:-2px;
            width:12px;height:12px;background:#22c55e;
            border:2px solid #fff;border-radius:50%;"></span>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div style="position:relative;">
            <div class="dp-initials"
            style="width:46px;height:46px;border-radius:50%;
            background:#1a73e8;color:#fff;display:flex;
            align-items:center;justify-content:center;
            font-weight:600;font-size:14px;
            border:2px solid #1a73e8;">
            <?= strtoupper(substr($friend,0,2)) ?>
            </div>

            <?php if($online): ?>
            <span style="position:absolute;bottom:-2px;right:-2px;
            width:12px;height:12px;background:#22c55e;
            border:2px solid #fff;border-radius:50%;"></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div>
            <div class="name" style="font-weight:600;color:#243444;">
                <?= $friend ?>
            </div>

            <div class="last"
            style="font-size:13px;color:#6b7785;
            max-width:140px;overflow:hidden;
            text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars($preview) ?>
            </div>
        </div>
    </div>

    <div style="text-align:right;">
        <div style="font-size:11px;color:#999;">
            <?= $last_time ?>
        </div>

        <?php if ($unread > 0): ?>
        <span class="badge-count"><?= $unread ?></span>
        <?php endif; ?>
    </div>

</div>
<?php endwhile; ?>
