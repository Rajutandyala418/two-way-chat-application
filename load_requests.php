<?php
session_start();
include(__DIR__ . '/include/db_connect.php');

$me = $_SESSION['username'];

$req = $conn->prepare("
    SELECT fr.id, fr.sender, fr.status, u.profile_pic 
    FROM friend_requests fr
    JOIN users u ON fr.sender = u.username
    WHERE fr.receiver=? 
    ORDER BY FIELD(fr.status,'pending','accepted','rejected'), fr.id DESC
");
$req->bind_param("s", $me);
$req->execute();
$res = $req->get_result();

while($r = $res->fetch_assoc()):
$u = $r['sender'];
$pic = !empty($r['profile_pic']) ? "uploads/".$r['profile_pic'] : "";
?>
<tr class="reqRow" data-name="<?= strtolower($u) ?>" style="cursor:default !important;">
    <td>
        <?php if($pic): ?>
            <img class="pic" src="<?= $pic ?>" 
                 style="width:45px;height:45px;border-radius:50%;
                 border:2px solid #1a73e8;object-fit:cover;cursor:zoom-in;">
        <?php else: ?>
            <div class="initials" 
                 style="width:45px;height:45px;border-radius:50%;
                 background:#1a73e8;color:#fff;display:flex;
                 align-items:center;justify-content:center;
                 font-weight:600;font-size:15px;cursor:zoom-in;">
                 <?= strtoupper(substr($u,0,2)) ?>
            </div>
        <?php endif; ?>
    </td>

    <td style="text-align:center;"><?= $u ?></td>
    <td style="text-transform:capitalize;text-align:center;"><?= $r['status'] ?></td>

    <td style="text-align:center;">
        <?php if($r['status']=="pending"): ?>
            <button class="approve" onclick="reqAction('approve','<?= $u ?>')">Accept</button>
            <button class="reject" onclick="reqAction('reject','<?= $u ?>')">Reject</button>

        <?php elseif($r['status']=="accepted"): ?>
            <span style="color:green;font-weight:600;">Accepted</span>

        <?php else: ?>
            <span style="color:#999;">Rejected</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
