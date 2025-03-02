<?php
    session_start();
    include_once "config.php";
    
    if(isset($_POST['mode'])){
        $mode = $_POST['mode'];
        $practice = $_POST['practice'];
        $outgoing_id = $_SESSION['unique_id'];
        $sql = "SELECT * FROM users INNER JOIN partner p1 ON users.unique_id = p1.user_id  
        WHERE NOT unique_id = {$outgoing_id} 
        AND p1.groupNumber = (
            SELECT p2.groupNumber 
            FROM partner p2
            WHERE p2.user_id = {$outgoing_id})";
        $query = mysqli_query($conn, $sql);
        $output = "";
        if(mysqli_num_rows($query) == 0){
            $output .= "No users are available to chat";
        }elseif(mysqli_num_rows($query) > 0){
            include_once "data.php";
        }
        echo $output;
    } 
    else {

        $outgoing_id = $_SESSION['unique_id'];
        $sql = "SELECT * FROM users INNER JOIN partner p1 ON users.unique_id = p1.user_id  
        WHERE NOT unique_id = {$outgoing_id} 
        AND p1.groupNumber = (
            SELECT p2.groupNumber 
            FROM partner p2
            WHERE p2.user_id = {$outgoing_id})";
        $query = mysqli_query($conn, $sql);
        $output = "";
        if(mysqli_num_rows($query) == 0){
            $output .= "No users are available to chat";
        }elseif(mysqli_num_rows($query) > 0){
            include_once "data.php";
        }
        echo $output;
    }
?>