<?php 
    session_start();
    include_once "config.php";
    // 檢查是否收到 POST 請求
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['mode'])) {
            $mode = mysqli_real_escape_string($conn, $_POST['mode']);
        }
        else{
            $mode = 'first';
        }
        $user = mysqli_real_escape_string($conn, $_POST['user']);
        $scaleName = mysqli_real_escape_string($conn, $_POST['scaleName']);
        $testOrder = mysqli_real_escape_string($conn, $_POST['testOrder']);
        $question = mysqli_real_escape_string($conn, $_POST['question']);
        $answer = mysqli_real_escape_string($conn, $_POST['answer']);
    
        // 設置時區為台北
        date_default_timezone_set('Asia/Taipei');
        $currentTime = date('Y-m-d H:i:s');
        

        $sql = "INSERT INTO survey (user_id, scaleName,testOrder, mode, question_number	, response, response_date) VALUES ('$user','$scaleName','$testOrder', '$mode', '$question', '$answer', '$currentTime')";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Error: " . mysqli_error($conn));
        }
    
        echo "Data inserted successfully!";
    } else {
        // 如果不是 POST 請求，返回錯誤
        http_response_code(405);
        echo 'Method Not Allowed';
    }
?>