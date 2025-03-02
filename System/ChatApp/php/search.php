<?php
    session_start();
    include_once "config.php";

    $outgoing_id = $_SESSION['unique_id'];
    $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);

    $sql = "SELECT * FROM users INNER JOIN partner p1 ON users.unique_id = p1.user_id  
            WHERE NOT unique_id = {$outgoing_id} 
            AND p1.groupNumber = (
                SELECT p2.groupNumber 
                FROM partner p2
                WHERE p2.user_id = {$outgoing_id} 
            ) AND (fname LIKE '%{$searchTerm}%' OR lname LIKE '%{$searchTerm}%') ";

    $output = "";
    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        include_once "data.php";
    }else{
        $output .= 'No user found related to your search term';
    }
    echo $output;
?>