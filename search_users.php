<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$me = $_SESSION['username'];
$q = $_POST['q'];

$stmt = $conn->prepare("
    SELECT username, profile_pic 
    FROM users 
    WHERE username LIKE CONCAT('%', ?, '%')
    AND username != ?
    ORDER BY username ASC
");
$stmt->bind_param("ss", $q, $me);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()):
$user = $row['username'];
$pic = !empty($row['profile_pic']) ? "uploads/".$row['profile_pic'] : "";

$check = $conn->query("
    SELECT status 
    FROM friend_requests 
    WHERE (sender='$me' AND receiver='$user') 
       OR (sender='$user' AND receiver='$me')
");

$status = $check->num_rows ? $check->fetch_assoc()['status'] : '';
?>

<?php if($status !== "accepted"): ?>
<div class="user-item">
    <div class="user-left">
        <?php if($pic): ?>
            <img src="<?= $pic ?>" class="pic">
        <?php else: ?>
            <div class="initials"><?= strtoupper(substr($user,0,2)) ?></div>
        <?php endif; ?>
        <span><?= $user ?></span>
    </div>

    <?php if($status == "pending"): ?>
        <button class="req" data-user="<?= $user ?>">Requested</button>

    <?php elseif(!$status || $status == "rejected"): ?>
        <button class="add" data-user="<?= $user ?>">Add Friend</button>
    <?php endif; ?>

</div>
<?php endif; ?>


<?php endwhile; ?>
