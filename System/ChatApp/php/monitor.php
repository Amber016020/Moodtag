<?php 
  session_start();
  include_once "php/config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<?php include_once "php/header.php"; ?>
<body>
  <!-- 左邊 -->
  <div class="wrapper">
    <section class="chat-area">
      <span>名字</span>      
      <header>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img src="php/images/<?php echo $row['img']; ?>" alt="">
        <div class="details">
          <span class="userName"></span>
        </div>
      </header>
      <div id="left-chat-box"  class="chat-box">
      </div>
    </section>
  </div>
  <!-- 右邊 -->
  <div class="wrapper">
    <section class="chat-area">
      <span>名字</span>
      <header>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img src="php/images/<?php echo $row['img']; ?>" alt="">
        <div class="details">
          <span class="userName"></span>
          <span  style="padding-left: 100px;"></span>
        </div>
      </header>
      <div id="right-chat-box" class="chat-box">
      </div>
    </section>
  </div>

  <!-- 實驗人員詢問按鈕的快捷鍵按鈕 -->
  <div>
    <!-- 受測者組別 -->
    <div class="mode-select-wrapper" style="display:block!improtant">
        <label for="userGroup"><?php echo $lang['userSelect']; ?>：</label>
        <select id="userGroupSelect">

        </select>
    </div>
    <!-- 聊天模式 -->
    <div class="mode-select-wrapper" style="display:block!improtant">
      <label for="modeSelect"><?php echo $lang['select_mode']; ?>：</label>
      <select id="modeSelect">
        <option value="neutral"><?php echo $lang['neutral']; ?></option>
        <option value="control"><?php echo $lang['control']; ?></option>
        <option value="self-affect"><?php echo $lang['self-affect']; ?></option>
        <option value="Moodtag"><?php echo $lang['Moodtag']; ?></option>
      </select>
    </div>
    <!-- 練習模式 -->
    <div style="text-align: right; margin-right: 20px;">
      <label for="practiceCheckbox"><?php echo $lang['practice']; ?></label>
      <input type="checkbox" id="practiceCheckbox" name="practiceCheckbox">
    </div>
    <div></div>
    <button id="queryBtn">提交</button>

    <div id="survey">
    </div>
    <div id="survey1">
    </div>
    <div id="currentState">練習模式：self-affect</div>
    <div id="time"></div>
    <div id="buttonContainer" style="display: flex; justify-content: center; align-items: center; margin-top: 30px;">
        <button id="skipBreakButton" style="font-weight: bold; font-size: 15px;">跳過休息</button>
        <button id="timerButton" style="font-weight: bold; font-size: 15px;">計時10分鐘</button>
    </div>
  </div>
  <div id="experimentControlButton" class="button-container">
    <!-- 下一練習模式 -->
    <button id="nextPractice" class="experiment-control-button">下一練習模式</button>
    <!-- 平靜後測量 -->
    <button id="baseSurvey" class="experiment-control-button">平靜基準問卷連結</button>
    <!-- PANAS 情緒量表、PAQ 述情障礙 、ABCCT溝通技術-->
    <button id="postSurvey" class="experiment-control-button">後測問卷連結</button>
    <!-- TPA信任量表、聊天模式使用偏好調查 -->
    <button id="finalSurvey" class="experiment-control-button">最後的問卷連結</button>
  </div>
  <script type="module" src="javascript/monitor.js"></script>

</body>
</html>
