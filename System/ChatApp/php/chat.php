<?php 
  session_start();
  include_once "config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<?php include_once "header.php"; ?>
<body>
  <div class="wrapper">
    <div id="alertMessage" style="text-align: center; color: red; font-weight: bold; display:none;">
      聊天過程中請記得做情緒標記
    </div>
    <section class="chat-area">
      <header>
        <?php 
          $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
          $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");
          if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
          }else{
            header("location: users.php");
          }
        ?>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img class="profile round" src="images/<?php echo $row['img']; ?>" alt="">
        <div class="details">
          <span><?php echo $row['lname'] .$row['fname'] ?></span>
          <div class="faceEmo" style="display:none">
            <span>FER</span>
            <p>🫥</p>
          </div> 
          <span id="affectLabelSource" style="padding-left: 10px; <?php if ($_GET['mode'] === 'Control') echo 'display: none;'; ?>"></span>
        </div>
      </header>
      <div class="chat-box">

      </div>
      <div class="details">    
        <img class="smile" src="images/smile.png" alt="Smile">
        <p id="modeIntroText" ></p>
        <img id="modeIntroImg" src="" alt="">
      </div>
      <div id="modeEmoji" style="width: 350px; margin: 0 auto;">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f621/512.gif" alt="anger" width="34" height="34" style="margin-right: 30px;">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f92e/512.gif" alt="disgust" width="34" height="34" style="margin-right: 30px;">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f628/512.gif" alt="fear" width="34" height="34" style="margin-right: 30px;">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f622/512.gif" alt="sad" width="34" height="34" style="margin-right: 30px;">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f631/512.gif" alt="surprise" width="34" height="34" >
      </div>
      <form action="#" class="typing-area">
        <input type="text" id="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
        <input type="text" id="outcoming_id" name="outcoming_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
        <input type="text" id="mode" value="<?php echo $_GET['mode']; ?>" hidden>
        <input id="isExperimenter" value="<?php echo $_SESSION['isExperimenter']; ?>" hidden>
        <input type="hidden" id="practice" value="<?php echo isset($_GET['practice']) && $_GET['practice'] === 'true' ? 1 : 0; ?>">
        <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off" id = "myTextarea">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </div>
  <!-- 任務模式說明 -->
  <div class="overlay" id="overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>任務指南</h2>
        <h3><span id="taskTitle" class="highlightRed"></span></h3>
      </div>
      <div class="modal-body">
        <div id="perSurvey"></div>
        <p id="taskDescription">
          <!-- 插入圖片 -->
          <div class="image-container" style="text-align: center;">
            <img id="labelGif" src="" alt="" width="350" style="">
          </div>
        </br></p>
      </div>
      <div class="modal-footer">
        <div id="waitMessage" style="display:none">...等待對方完成問卷...</div>
        <button id="confirmButton" style="display:none">開始</button>
        <a id="nextModeLink" href="#" style="display:none">
          <button id="stratButton">開始</button>
        </a> 
      </div>
    </div>
  </div>

  <script type="module" src="../javascript/chat.js"></script>
  <script src="https://github.com/eligrey/FileSaver.js/"></script>

</body>
</html>
