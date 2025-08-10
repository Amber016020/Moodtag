<?php 
  session_start();
  include_once "config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<?php include_once "header.php"; ?>
<body class="mt-chat" data-mode="<?php echo htmlspecialchars($_GET['mode'] ?? '', ENT_QUOTES); ?>">
  <div class="wrapper">
    <div id="alertMessage" style="text-align: center; color: red; font-weight: bold; display:none;">
      聊天過程中請記得做情緒標記
    </div>

    <!-- ✅ 用 layout 包住：左邊聊天、右邊側欄 -->
    <div class="layout">
      <!-- ✅ 保留原本唯一的 chat-area（把你的原內容放進來） -->
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
          <a href="../users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
          <img class="profile round" src="images/<?php echo $row['img']; ?>" alt="">
          <div class="details">
            <span><?php echo $row['lname'] .$row['fname'] ?></span>
            <div class="faceEmo" style="display:none">
              <span>FER</span>
              <p>🫥</p>
            </div>
            <!-- ❌ 改側欄呈現後，這段可刪或常態隱藏 -->
            <!-- <span id="affectLabelSource" style="padding-left:10px; <?php if ($_GET['mode'] === 'Control') echo 'display:none;'; ?>"></span> -->
          </div>
        </header>

        <div class="chat-box"></div>

        <div class="details">
          <img class="smile" src="images/smile.png" alt="Smile">
          <p id="modeIntroText"></p>
          <img id="modeIntroImg" src="" alt="">
        </div>

        <div id="modeEmoji" style="width:350px; margin:0 auto;">
          <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f621/512.gif" alt="anger" width="34" height="34" style="margin-right:30px;">
          <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f92e/512.gif" alt="disgust" width="34" height="34" style="margin-right:30px;">
          <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f628/512.gif" alt="fear" width="34" height="34" style="margin-right:30px;">
          <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f622/512.gif" alt="sad" width="34" height="34" style="margin-right:30px;">
          <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f631/512.gif" alt="surprise" width="34" height="34">
        </div>

        <form action="#" class="typing-area">
          <input type="text" id="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
          <input type="text" id="outcoming_id" name="outcoming_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
          <input type="text" id="mode" value="<?php echo $_GET['mode']; ?>" hidden>
          <input id="isExperimenter" value="<?php echo $_SESSION['isExperimenter']; ?>" hidden>
          <input type="hidden" id="practice" value="<?php echo isset($_GET['practice']) && $_GET['practice'] === 'true' ? 1 : 0; ?>">
          <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off" id="myTextarea">
          <button><i class="fab fa-telegram-plane"></i></button>
        </form>
      </section>

      <!-- ✅ 右側：情緒標籤與 MoodTag 區 -->
      <aside class="affect-panel" id="affectPanel" style="<?php if ($_GET['mode'] === 'Control') echo 'display:none; !important;'; ?>">
        <header class="affect-panel__header">
          <h3>情緒標籤</h3>
          <small id="affectPanelModeBadge">
            <?php echo htmlspecialchars($_GET['mode'] ?? '', ENT_QUOTES); ?>
          </small>
        </header>

        <div class="affect-panel__legend">
          <div class="legend-col">
            <span class="dot dot--self" title="手動"></span>
            自己手動標記對於訊息感受到的情緒
          </div>
          <span class="legend-separator"></span>
          <div class="legend-col">
            <span class="dot dot--peer" title="MoodTag"></span>
            MoodTag基於文字的客觀情緒判斷
          </div>
        </div>


        <ol class="affect-list" id="affectList" reversed></ol>
      </aside>
    </div><!-- /.layout -->
  </div><!-- /.wrapper -->

  <!-- 任務模式說明（保持不變） -->
  <div class="overlay" id="overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>任務指南</h2>
        <h3><span id="taskTitle" class="highlightRed"></span></h3>
      </div>
      <div class="modal-body">
        <div id="perSurvey"></div>
        <p id="taskDescription">
          <div class="image-container" style="text-align: center;">
            <img id="labelGif" src="" alt="" width="350">
          </div>
        </p>
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
  <style>
/* —— 只影響這個頁面 —— */

/* —— 只影響這個頁面 —— */
:root {
  /* header + composer 大約高度（自行微調） */
  --mt-chrome: 180px;
}
/* Manual 模式隱藏 legend 裡的 MoodTag 欄位和分隔線 */
body.mt-chat[data-mode="Manual"] .affect-panel__legend .legend-separator,
body.mt-chat[data-mode="Manual"] .affect-panel__legend .legend-col:last-child {
  display: none !important;
}
body.mt-chat[data-mode="Control"] #affectPanel {
  display: none !important;
}

.mt-chat .wrapper { max-width: 1100px !important; margin: 0 auto !important; }

/* 兩欄 */
.mt-chat .layout {
  display: flex !important; flex-wrap: nowrap !important; gap: 16px !important; align-items: stretch !important;
}


/* 左欄 */
.mt-chat .layout > .chat-area { flex: 1 1 auto !important; min-width: 0 !important; }
.mt-chat .chat-area .chat-box {
  height: calc(100dvh - var(--mt-chrome));
  overflow: auto;
  padding-right: 8px;
}
.mt-chat .layout {
  display: flex;
  align-items: flex-start;
}

.mt-chat .chat-area {
  display: flex;
  flex-direction: column;
}

.mt-chat .chat-box {
  flex: 1; /* 填滿剩餘空間 */
}

.mt-chat .affect-list__spacer {
  width: 1px;        /* 幾乎看不見 */
  height: 0;         /* 高度用 JS 設 */
  pointer-events: none;
}

/* 右欄 */
.mt-chat .layout > .affect-panel {
  flex: 0 0 280px !important;
  max-width: 280px !important;
  display: flex !important;
  box-sizing: border-box;
  flex-direction: column;              /* 讓子元素垂直排列 */
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 12px;
  height: auto;      /* 不要硬算高度 */
  overflow: hidden;                    /* 這裡關掉捲動 */
  position: sticky;
  top: 12px;
  align-self: flex-start;
  background: #fff;
}

/* 左側訊息被右側 hover 到時的高亮 */
.mt-chat .chat.highlight-pair {
  outline: 2px solid rgba(0, 123, 255, 0.25);  /* 很淡的藍色 */
  background-color: rgba(0, 123, 255, 0.04);
  border-radius: 10px;
  transition: outline 120ms ease, background-color 120ms ease;
}

/* 右側自己也給個微弱回饋（可選） */
.mt-chat .affect-item.is-hover {
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15) inset;
  background: #fbfdff;
}

/* 樣式 */
.mt-chat .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; background:#ccc; vertical-align:middle; }
.mt-chat .dot--self { background:#7aa6ff; }
.mt-chat .dot--peer { background:#ff9f7a; }
.mt-chat .affect-panel__header { 
  display:flex; align-items:baseline; justify-content:space-between; 
  margin-bottom:6px; 
  flex: 0 0 auto;
}
.mt-chat .affect-panel__legend {
  display: grid; /* 關鍵：改成 grid 讓兩段分開 */
  grid-template-columns: 1fr 1fr; /* 左右平均分兩欄 */
  align-items: start;
  gap: 8px;
  white-space: normal; /* 允許文字自動換行 */
  font-size: 12px; /* 或 0.85rem，看你版面需求 */
}
.mt-chat .legend-col {
  display: flex;
  align-items: flex-start;
  gap: 6px;
}

.mt-chat .legend-separator {
  display: none; /* 如果不需要中間分隔線就關掉 */
}

/* 兩種標籤通用外觀 */
.mt-chat .affect-label { font-size: 18px; line-height: 1; }
.mt-chat .affect-label.moodtag { margin-left: 8px; opacity: .95; }

/* Manual 模式隱藏 MoodTag 標籤 */
body.mt-chat[data-mode="Manual"] .affect-label.moodtag { display: none; }


/* 右側清單容器當定位上下文，自己負責捲動 */
.mt-chat .affect-list{
  position: relative;
  list-style: none;
  padding: 0;
  margin: 0;
  overflow: auto;       /* 右欄自己捲 */
}

/* 每一列用絕對定位貼到左側訊息的位置 */
.mt-chat .affect-item{
  position: absolute;
  left: 0; right: 0;
  height: 0;            /* 用 JS 設定 */
  margin: 0;            /* 高度/間距都交給 JS */
  padding: 0;
  border: 0;
}

/* 視覺用的內層，不影響外高 */
.mt-chat .affect-item__inner{
  box-sizing: border-box;
  height: 100%;
  border-radius: 10px;
  background: #fafafa;
  border: 1px solid #e9eefb;
  display: grid;
  grid-template-columns: 1fr 1px 1fr;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
}

.mt-chat .affect-item__col{ display:flex; align-items:center; gap:8px; }
.mt-chat .affect-item__divider{ width:1px; height:60%; background:#e5e5e5; justify-self:center; }

.mt-chat .affect-emoji{ font-size:18px; line-height:1; }

/* Manual 模式隱藏右側（MoodTag）與中線 */
body.mt-chat[data-mode="Manual"] .affect-item__col--moodtag,
body.mt-chat[data-mode="Manual"] .affect-item__divider { display:none; }

/* 高亮框線效果 */
.highlight-message {
  outline: 2px solid rgba(0, 123, 255, 0.4);
  border-radius: 8px;
  background-color: rgba(0, 123, 255, 0.05);
  transition: background-color 0.2s ease, outline 0.2s ease;
}

/* 手機 */
@media (max-width: 900px) {
  .mt-chat .layout { flex-direction: column !important; }
  .mt-chat .layout > .affect-panel { position: static; height: auto; max-width: none; flex: 0 0 auto; }
}
</style>
</body>
</html>
