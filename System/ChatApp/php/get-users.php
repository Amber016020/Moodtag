<?php
    session_start();
    include_once "config.php";
    
    if(isset($_POST['mode'])){
        $mode = $_POST['mode'];
        $practice = $_POST['practice'];
        $outgoing_id = $_SESSION['unique_id'];
        $sql = "SELECT * FROM users WHERE NOT unique_id = {$outgoing_id} ORDER BY user_id DESC";
        $query = mysqli_query($conn, $sql);
        $output = "";
        if(mysqli_num_rows($query) == 0){
            $output .= "No users are available to chat";
        }elseif(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                $sql2 = "SELECT * FROM messages 
                WHERE (incoming_msg_id = {$row['unique_id']} OR outgoing_msg_id = {$row['unique_id']}) 
                AND (outgoing_msg_id = {$outgoing_id}  OR incoming_msg_id = {$outgoing_id}) 
                AND pattern = '{$mode}' 
                AND isPractice = {$practice}                
                ORDER BY msg_id DESC LIMIT 1";

                $query2 = mysqli_query($conn, $sql2);
                $row2 = mysqli_fetch_assoc($query2);

                $output .= '<option value="Control">' .$row['lname']. " " . $row['fname']. '</option>';
            }
        }
        echo $output;
    } 
?>