<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';
include(__DIR__ . '/include/db_connect.php');

$q = $_POST['q'];
$me = $_SESSION['username'];

$stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE username LIKE CONCAT('%',?,'%') AND username!=?");
$stmt->bind_param("ss", $q, $me);
$stmt->execute();
$res = $stmt->get_result();

while($u = $res->fetch_assoc()):
$user = $u['username'];
$pic = !empty($u['profile_pic']) ? "uploads/".$u['profile_pic'] : "";
$isFriendQ = $conn->query("
    SELECT * FROM friend_requests 
    WHERE ((sender='$me' AND receiver='$user') OR (sender='$user' AND receiver='$me')) 
      AND status='accepted'
");

$isFriend = $isFriendQ->num_rows > 0;
?>
<div class="user-item">
<div class="user-left" onclick="openChat('<?= $user ?>')">
<?php if($pic): ?>
<img src="<?= $pic ?>" class="pic">
<?php else: ?>
<div class="initials"><?= strtoupper(substr($user,0,2)) ?></div>
<?php endif; ?>
<span><?= $user ?></span>
</div>

<?php if(!$isFriend): ?>
<button class="add" onclick="addFriend('<?= $user ?>')">Add Friend</button>
<?php endif; ?>
</div>
<?php endwhile; ?>
