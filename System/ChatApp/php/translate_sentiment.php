<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收前端發送的數據
    $data = $_POST['data'];

    // 將數據傳遞給 Python 腳本並獲取結果
    $result = shell_exec("python D:/xampp/htdocs/ChatApp/python/translate_sentiment.py \"$data\"");
    
    // 返回結果給前端
    echo $result;
} else {
    echo "Invalid Request";
}
?>