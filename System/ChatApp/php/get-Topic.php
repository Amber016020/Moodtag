<?php
    session_start();
    include_once "config.php";
    
    $mode = $_POST['mode'];
    $user_id = $_POST['user_id'];

    $sql = "SELECT * FROM testsequence inner join partner on testsequence.groupNumber = partner.groupNumber WHERE testsequence.pattern = '{$mode}' AND partner.user_id = {$user_id}";
    $query = mysqli_query($conn, $sql);
    $output = "";
    if(mysqli_num_rows($query) == 1){
        while($row = mysqli_fetch_assoc($query)){
            $output .= $row['topic'];
        }
    }
    echo $output;

?>