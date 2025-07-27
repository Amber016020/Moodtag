<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
class Chat implements MessageComponentInterface {
    protected $clients;
    private $conn;
    public function __construct() {
        $this->clients = new \SplObjectStorage;

        $hostname = "127.0.0.1";
        $username = "chatuser";
        $password = "";
        $dbname = "chatapp";
        $this->conn = mysqli_connect($hostname, $username, $password, $dbname);
        if(!$this->conn) {
            echo "Database connection error" . mysqli_connect_error();
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    // insert message
    // 如果有emotion，就insert emotion跟affectlabel
    public function insertChat($incomingUserId,$outcomingUserId, $msg, $textEmotion, $pattern, $type, $isPractice, $isMoodTag ,$typeStartTime, $time){


        $query = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg, pattern, isPractice, msg_start_time, msg_time)
                                    VALUES ({$incomingUserId}, {$outcomingUserId}, '{$msg}', '{$pattern}', $isPractice, '{$typeStartTime}', '{$time}')";

        mysqli_query($this->conn, $query) or die();

        // 獲取插入行的 msg_id
        $msgId = mysqli_insert_id($this->conn);
        echo $textEmotion;
        if( $textEmotion != null ){
            $query = "INSERT INTO emotion (msg_id, emotion, pattern, view_user_id, labeler_user_id, source, isMoodTag, emotion_time)
                        VALUES ({$msgId}, '{$textEmotion}', '{$pattern}', '{$incomingUserId}', {$outcomingUserId}, '{$type}', $isMoodTag, '{$time}')";

            if (mysqli_query($this->conn, $query)) {
                echo "Emotion inserted successfully\n";
            } else {
                // 输出错误信息
                echo "Error inserting emotion: " . mysqli_error($this->conn) . "\n";
            }   
            
            $query = "INSERT INTO affectlabel (msg_id, user_id, affectEmo,isMoodTag, emo_time)
                        VALUES ('{$msgId}', '{$outcomingUserId}', '{$textEmotion}', $isMoodTag, '{$time}')";

            mysqli_query($this->conn, $query) or die();
        }
        return $msgId;
    }

    public function insertEmo($incomingUserId,$outcomingUserId, $msgId, $textEmotion, $pattern, $type, $isPractice, $isMoodTag ,$time){
        echo $textEmotion;
        if( $textEmotion != null ){
            $query = "INSERT INTO emotion (msg_id, emotion, pattern, view_user_id, labeler_user_id, source, isMoodTag, emotion_time)
                        VALUES ({$msgId}, '{$textEmotion}', '{$pattern}', '{$incomingUserId}', {$outcomingUserId}, '{$type}', $isMoodTag, '{$time}')";

            if (mysqli_query($this->conn, $query)) {
                echo "Emotion inserted successfully\n";
            } else {
                // 输出错误信息
                echo "Error inserting emotion: " . mysqli_error($this->conn) . "\n";
            }   
            
            $query = "INSERT INTO affectlabel (msg_id, user_id, affectEmo,isMoodTag, emo_time)
                        VALUES ('{$msgId}', '{$outcomingUserId}', '{$textEmotion}', $isMoodTag, '{$time}')";
    
            mysqli_query($this->conn, $query) or die();

            $query = "INSERT INTO affectlabel (msg_id, user_id, affectEmo,isMoodTag, emo_time)
                        VALUES ('{$msgId}', '{$incomingUserId}', '{$textEmotion}', $isMoodTag, '{$time}')";

            mysqli_query($this->conn, $query) or die();
        }
        return $msgId;
    }


    public function insertSelfReport($picture_order,$picture_name, $msg_id, $user_id, $pleasure, $arousal,$time){
        $query = "INSERT INTO selfReport (picture_order,picture_name,msg_id,user_id,pleasure,arousal,report_time)
                  VALUES ($picture_order, '{$picture_name}', $msg_id, '{$user_id}', $pleasure, $arousal,'{$time}')";

        mysqli_query($this->conn, $query) or die();
    }
    

    public function selectUserData($incomingUserId){

        $query = "SELECT * FROM users WHERE unique_id = {$incomingUserId}";

        $result = mysqli_query($this->conn, $query) or die();

        // 檢查是否有查詢結果
        if ($result && mysqli_num_rows($result) > 0) {
            // 提取用戶數據並返回
            return mysqli_fetch_assoc($result);
        } else {
            // 若無查詢結果，返回空數組或其他適當的值
            return [];
        }
    }

    public function textEmotion($msg){
        $escapedData = escapeshellarg($msg);
        $pythonScript = "../ChatApp/python/endtoendchatemo.py";
        $pythonCommand = "python $pythonScript $escapedData";
        $emotion = exec($pythonCommand, $output, $returnCode);
        return $emotion;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg, true);
        
        $outComingUserData = $this->selectUserData($data['outcomingUserId']);

        $data['img'] = $outComingUserData['img'];
        // Set the time zone to Taipei
        date_default_timezone_set('Asia/Taipei'); 
        // Add current time
        $data['time'] = date('Y-m-d H:i:s');
        // GPT判斷
        if($data['type'] == 'GPT'){
            $data['msg_id'] = $this->insertChat($data['incomingUserId'],$data['outcomingUserId'],$data['msg'],null,$data['mode'],$data['type'],$data['isPractice'],$data['isMoodTag'],$data['typeStartTime'],$data['time']);
        }
        // call GPT來判斷情緒
        else if($data['type'] == 'GPT_emo'){
            $this->insertEmo($data['incomingUserId'],$data['outcomingUserId'],$data['msg_id'],$data['emotion'],$data['mode'],$data['type'],$data['isPractice'],$data['isMoodTag'],$data['time']);
        }
        // 量表類，不需要insert進message table
        else{
            // 一般訊息/臉部表情/圖片，不需要辨識文字情緒
            if($data['type'] == 'Fer' || $data['type'] == 'Control' || $data['type'] == 'emoImg'){
                $data['msg_id'] = $this->insertChat($data['incomingUserId'],$data['outcomingUserId'],$data['msg'],null,$data['mode'],$data['type'],$data['isPractice'],$data['isMoodTag'],$data['typeStartTime'],$data['time']);
            }         
        }
        foreach ($this->clients as $client) {
            if($from == $client){
                $data['from'] = 'Me';
            }
            else{
                $data['from'] = 'NotMe';
            }

            $client->send(json_encode($data));
        }   

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
  }
  ?>