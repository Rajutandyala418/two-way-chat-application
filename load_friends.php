<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$me = $_SESSION['username'];

$friend = $conn->prepare("
    SELECT IF(sender=?, receiver, sender) AS friend, u.profile_pic
    FROM friend_requests fr
    JOIN users u ON u.username = IF(sender=?, receiver, sender)
    WHERE (sender=? OR receiver=?) AND status='accepted'
");
$friend->bind_param("ssss", $me, $me, $me, $me);
$friend->execute();
$f = $friend->get_result();

while($row = $f->fetch_assoc()):
$user = $row['friend'];
$pic = !empty($row['profile_pic']) ? "uploads/".$row['profile_pic'] : "";
?>

<div class="friend-item" data-friend="<?= strtolower($user) ?>">
    <div class="friend-left">
        <?php if($pic): ?>
            <img src="<?= $pic ?>" class="pic">
        <?php else: ?>
            <div class="initials"><?= strtoupper(substr($user,0,2)) ?></div>
        <?php endif; ?>
        <span><?= $user ?></span>
    </div>

    <button class="unfriend" onclick="unfriend('<?= $user ?>')">Unfriend</button>
</div>

<?php endwhile; ?>
