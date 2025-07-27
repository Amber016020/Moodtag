<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// 檢查用戶設置的語言，如果沒有設置則默認為英文
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'zh';

// 包含相應語言文件
include_once($_SERVER['DOCUMENT_ROOT'] . "/Moodtag/System/ChatApp/lang/$language.php");

?>

<!DOCTYPE html>
<!-- Coding By CodingNepal - youtube.com/codingnepal -->
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Realtime Chat App | CodingNepal</title>
  <link rel="stylesheet" href="/Moodtag/System/ChatApp/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
  <link rel="stylesheet" href="/Moodtag/System/ChatApp/font/fonts.css">
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <!-- Import Emoji Library -->
  <link rel="stylesheet" href="/Moodtag/System/ChatApp/css/emojionearea.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="/Moodtag/System/ChatApp/javascript/emojionearea.min.js"></script>
</head>