<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include_once "config.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $pleasure = mysqli_real_escape_string($conn, $_POST['pleasure']);
        $arousal = mysqli_real_escape_string($conn, $_POST['arousal']);
        $picture_order = mysqli_real_escape_string($conn, $_POST['picture_order']);
        $picture_name = mysqli_real_escape_string($conn, $_POST['picture_name']);
        $msg_id = mysqli_real_escape_string($conn, $_POST['msg_id']);
    
        if (!empty($message)) {
            // Set the time zone to Taipei
            date_default_timezone_set('Asia/Taipei'); 
            // Add current time
            $currentTime = date('Y-m-d H:i:s');
    
            // Inserting data into selfReport table
            $sql = "INSERT INTO selfReport (report_id, picture_order, picture_name, msg_id, user_id, pleasure, arousal, report_time)
                    VALUES (NULL, '{$picture_order}', '{$picture_name}', '{$msg_id}', '{$incoming_id}', '{$pleasure}', '{$arousal}', '{$currentTime}')";
            
            mysqli_query($conn, $sql) or die(mysqli_error($conn));
        }
    }else{
        header("location: ../login.php");
    }
?>