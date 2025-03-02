<?php 
    $serverUrl = "ws://localhost:8765";
    $websocket = new WebSocket($serverUrl);

    $result = $websocket->receive();  // 接收 Python 送回的結果
    echo "Received result: " . $result;
?>