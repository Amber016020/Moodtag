<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include_once "config.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        if(!empty($message)){
            $escapedData = escapeshellarg($message);
            $pythonScript = "../python/sentiment_lr_model.py";
            $pythonCommand = "python $pythonScript $escapedData";
            $emotion = exec($pythonCommand, $output, $returnCode);
            
            if ($returnCode !== 0) {
                echo "Error executing Python script.";
            } else {
                echo "Python Script Result: $emotion";
            }
            
            // Set the time zone to Taipei
            date_default_timezone_set('Asia/Taipei'); 
            // Add current time
            $currentTime = date('Y-m-d H:i:s');
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg, pattern , isPractice, msg_time)
                                        VALUES ({$incoming_id}, {$outgoing_id}, '{$message}', '{control}','{1}', '{$currentTime}')") or die();
            $msg_id = mysqli_insert_id($conn);
            $sql = mysqli_query($conn, "INSERT INTO emotion (msg_id, emotion, pattern, view_user_id, labeler_user_id, source, isMoodTag, emotion_time)
                                        VALUES ({$msg_id}, '{$emotion}', '{control}', '{$incoming_id}', {$outgoing_id}, '{Ter}','{1}', '{$currentTime}')") or die();
        }
    }else{
        header("location: ../login.php");
    }
?>