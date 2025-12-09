<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('login_required');
}

include(__DIR__ . '/include/db_connect.php');

$from    = $_SESSION['username'];
$to      = isset($_POST['to']) ? trim($_POST['to']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($to === '' || $to === $from) {
    exit('invalid');
}

$media_path = null;
$audio_path = null;

if (!empty($_FILES['media']['name'])) {
    $dir = __DIR__ . '/uploads/chat_media/';
    if(!is_dir($dir)){
        mkdir($dir, 0777, true);
    }
    $fname  = time() . '_' . basename($_FILES['media']['name']);
    $target = $dir . $fname;
    if (move_uploaded_file($_FILES['media']['tmp_name'], $target)) {
        $media_path = 'uploads/chat_media/' . $fname;
    }
}

if (!empty($_FILES['audio']['name'])) {
    $dir = __DIR__ . '/uploads/chat_audio/';
    if(!is_dir($dir)){
        mkdir($dir, 0777, true);
    }
    $fname  = time() . '_' . basename($_FILES['audio']['name']);
    $target = $dir . $fname;
    if (move_uploaded_file($_FILES['audio']['tmp_name'], $target)) {
        $audio_path = 'uploads/chat_audio/' . $fname;
    }
}

if ($message === '' && !$media_path && !$audio_path) {
    exit('empty');
}

$stmt = $conn->prepare("
    INSERT INTO messages (sender, receiver, message, media_path, audio_path)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $from, $to, $message, $media_path, $audio_path);
$stmt->execute();

echo 'ok';
