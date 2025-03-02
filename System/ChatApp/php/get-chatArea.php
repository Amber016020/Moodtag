<?php
include_once "config.php";

if(isset($_GET['unique_id'])){
  $unique_id = mysqli_real_escape_string($conn, $_GET['unique_id']);
  $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id='$unique_id'");
  if(mysqli_num_rows($sql) > 0){
    $row = mysqli_fetch_assoc($sql);
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'User not found']);
  }
}
?>
