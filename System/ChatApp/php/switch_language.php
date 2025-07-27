<?php
session_start();

if (isset($_GET['language'])) {
  $language = $_GET['language'];

  if (in_array($language, ['en', 'zh'])) {
    $_SESSION['language'] = $language;
    
  }
}

$previous_page = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $previous_page");
?>
