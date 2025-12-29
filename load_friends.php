<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');

$me = $_SESSION['username'];

// Fetch all accepted friends
$friend = $conn->prepare("
    SELECT 
        IF(sender=?, receiver, sender) AS friend,
        u.profile_pic
    FROM friend_requests fr
    JOIN users u ON u.username = IF(sender=?, receiver, sender)
    WHERE (sender=? OR receiver=?) AND status='accepted'
");
$friend->bind_param("ssss", $me, $me, $me, $me);
$friend->execute();
$f = $friend->get_result();

// Loop friends list
while($row = $f->fetch_assoc()):
    $user = $row['friend'];
    $pic = (!empty($row['profile_pic'])) ? "uploads/" . $row['profile_pic'] : "";
?>

<div class="friend-item"
     data-username="<?= $user ?>"
     data-friend="<?= strtolower($user) ?>">

    <div class="friend-left">

        <?php if($pic): ?>
            <img src="<?= $pic ?>" class="pic">
        <?php else: ?>
            <div class="initials"><?= strtoupper(substr($user,0,2)) ?></div>
        <?php endif; ?>

        <span class="friend-name"
              data-username="<?= $user ?>">
              <?= $user ?>
        </span>
    </div>

    <div class="friend-actions">
        <button class="chat" data-friend="<?= $user ?>">Chat</button>
        <button class="unfriend" data-friend="<?= $user ?>">Unfriend</button>
    </div>

</div>

<?php endwhile; ?>
