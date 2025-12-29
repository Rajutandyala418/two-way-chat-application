<?php
session_start();
require_once __DIR__ . '/include/auth_check.php';

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('login_required');
}

include(__DIR__ . '/include/db_connect.php');

$from = $_SESSION['username'];
$to = trim($_POST['to'] ?? '');
$message = trim($_POST['message'] ?? '');
$reply = $_POST['reply_to'] ?? NULL;

if ($to === '' || $to === $from) exit('invalid');

$media = NULL;
$audio = NULL;

if (!empty($_FILES['media']['name'])) {
    $dir = __DIR__ . "/uploads/chat_media/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
    $file = time() . '_' . uniqid() . '.' . $ext;

    if (move_uploaded_file($_FILES['media']['tmp_name'], $dir . $file)) {
        $media = "chat_media/" . $file;
    }
}

if (!empty($_FILES['audio']['name'])) {
    $dir = __DIR__ . "/uploads/chat_audio/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $file = time() . '_' . uniqid() . ".mp3";

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $dir . $file)) {
        $audio = "chat_audio/" . $file;
    }
}

if ($message === '' && !$media && !$audio) exit("empty");

$stmt = $conn->prepare("
    INSERT INTO messages (sender, receiver, message, media_path, audio_path, reply_to, is_read, is_seen)
    VALUES (?, ?, ?, ?, ?, ?, 0, 0)
");
$stmt->bind_param("sssssi", $from, $to, $message, $media, $audio, $reply);
$stmt->execute();

echo "ok";
?>
