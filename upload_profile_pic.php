<?php
session_start();
include('./include/db_connect.php');

$user_id = $_SESSION['user_id'];

if(isset($_FILES['profile_pic'])){
    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $newName = "PIC_".time().".".$ext;
    $tmp = $_FILES['profile_pic']['tmp_name'];
    $uploadPath = __DIR__ . "/uploads/" . $newName;

    if(move_uploaded_file($tmp, $uploadPath)){
        $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE user_id=?");
        $stmt->bind_param("si", $newName, $user_id);
        $stmt->execute();
        echo "updated";
    } else {
        echo "error";
    }
}
?>
