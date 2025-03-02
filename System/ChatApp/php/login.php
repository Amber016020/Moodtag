<?php 
    session_start();
    include_once "config.php";
    $account = mysqli_real_escape_string($conn, $_POST['account']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    if(!empty($account) && !empty($password)){
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE account = '{$account}'");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
            $user_pass = md5($password);
            $enc_pass = $row['password'];
            if($user_pass === $enc_pass){
                $status = "Active now";
                $sql2 = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id = {$row['unique_id']}");
                if($sql2){
                    $_SESSION['unique_id'] = $row['unique_id'];
                    $_SESSION['isExperimenter'] = $row['isExperimenter'];
                    echo "success";
                }else{
                    echo "Something went wrong. Please try again!";
                }
            }else{
                echo "account or Password is Incorrect!";
            }
        }else{
            echo "$account - This account not Exist!";
        }
    }else{
        echo "All input fields are required!";
    }
?>