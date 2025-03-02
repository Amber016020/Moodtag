var form = document.querySelector(".typing-area"),
    incoming_id = form.querySelector("#incoming_id").value, // UserID who rece message
    outcoming_id = form.querySelector("#outcoming_id").value, //UserID who sent the message
    isPractice = form.querySelector("#practice").value,
    mode = form.querySelector("#mode").value,
    sendBtn = form.querySelector("button"),
    chatBox = document.querySelector(".chat-box"),
    taskDescription = document.getElementById('taskDescription'),
    inputField  = document.querySelector('.emojionearea-editor');

// Define a variable to track whether the listener has been added
var eventListenerAdded = false;
var chooseEmoji = false;
var isRecording = true;
var lastAffectTime = new Date();
var isSendingDateTime = 0;
var timerInterval;
var totalSeconds;
var useCoze = false;
var systemName = 'Moodtag';
var selfAffect = 'self-affect';
var isComposing = false;
var conn;
var user1SurveyCompleted = false;
var user2SurveyCompleted = false;

import { startRecording } from './record.js';

import { ipAddress } from './config.js';

var emotionsEmojiDict = {
  "anger": "😡",
  "disgust": "🤮",
  "fear": "😨",
  "sad": "😢",
  "surprise": "😮" 
};

var wakeLock = null;

// 請求螢幕鎖定
async function requestWakeLock() {
  try {
    // 請求螢幕鎖定
    wakeLock = await navigator.wakeLock.request('screen');

    // 當螢幕鎖定被解除時
    wakeLock.addEventListener('release', () => {
      console.log('螢幕鎖定已解除');
      wakeLock = null;
      // 重新請求螢幕鎖定
      requestWakeLock();
    });

    console.log('螢幕鎖定已啟動');
  } catch (err) {
    // 處理錯誤，例如不支持 Wake Lock API
    console.error(`${err.name}: ${err.message}`);
  }
}

// 檢查瀏覽器是否支持 Wake Lock API
if ('wakeLock' in navigator) {
  // 當頁面加載完成後請求螢幕鎖定
  document.addEventListener('DOMContentLoaded', requestWakeLock);
} else {
  console.log('瀏覽器不支持 Wake Lock API');
}

setInterval(() => {
  // Check if the input field is found
  if (!inputField) {
    inputField = document.querySelector('.emojionearea-editor');
    // Check if the event listener has already been added
    if (!eventListenerAdded && inputField) {
      // Add event listener
      inputField.addEventListener("keydown", function(event) {
          // Check if the Enter key is pressed and the input field is not empty
          if (event.key === "Enter" && (inputField.textContent.trim() !== "" || inputField.querySelector('img') !== null)  && !isComposing) {
            // Trigger the click event of the send button
            sendBtn.click();
          }
      });
      // Set the variable to true, indicating that the event listener has been added
      eventListenerAdded = true;

      // Detect the time to start typing
      var editor = document.querySelector('.emojionearea-editor');
      // Define a function to handle the action of starting input
      var handleInput = () => {
        console.log('開始打字');
        if(isSendingDateTime === 0){
          isSendingDateTime = formatDate(new Date());
        }

      };  
      // Listen for keydown event
      editor.addEventListener('keydown', handleInput);
      
      inputField.addEventListener('compositionstart', () => {
        isComposing = true;
      });
      
      inputField.addEventListener('compositionend', () => {
        isComposing = false;

      });
    }
  }
  else{
    // Add "active" class to the send button if:child elements in the input field or the input field content is not empty
    if (inputField.querySelectorAll(".myTextarea").length > 0 || inputField.textContent.trim() !== '' || inputField.querySelector('img') !== null) {
        sendBtn.classList.add("active");
    } 
    else {
      sendBtn.classList.remove("active");
    }
  }
  // Check if the user has been doing affect labeling for more than one minute and show a warning
  if (mode === selfAffect && (new Date() - lastAffectTime) > 60000) {
    var alertMessage = document.getElementById('alertMessage');
    alertMessage.style.display = '';

    setTimeout(() => {
      alertMessage.style.display = 'none';
      lastAffectTime = new Date();
    }, 10000);
  }
  // 檢查問卷是否完成
  if ((!user1SurveyCompleted || !user2SurveyCompleted) && isPractice === '0') {
    let xhr = new XMLHttpRequest();

    // 獲取頁面上所有包含問卷的超連結
    const surveyLinks = document.querySelectorAll('a[href*="survey/"]');

    // 準備一個數組來存儲問卷信息
    const surveys = [];

    // 解析找到的所有超連結來提取 scaleName 和 testOrder
    surveyLinks.forEach(link => {
      const url = new URL(link.href);

      // 使用正則表達式從 URL 中提取出 .html 之前的那一段，設為 scaleName
      const path = url.pathname;
      const match = path.match(/\/survey\/(.+?)\.html/);
      const scaleName = match ? match[1] : "PANAS"; // 如果沒有匹配到，預設為 PANAS

      const testOrder = url.searchParams.get('testOrder');

      // 將問卷信息推入數組
      surveys.push({ scaleName, testOrder });
    });

    // 準備要發送的數據
    const requestData = {
      user_ids: [incoming_id, outcoming_id],
      mode: mode,
      isPractice: isPractice,
      surveys: surveys
    };

    xhr.open("POST", "php/get-preSurvey.php", true);
    xhr.onload = () => {
      if (xhr.status === 200) {
        let response = JSON.parse(xhr.responseText);
        console.log(response);

        for (const user_id in response) {
          const userSurveys = response[user_id].surveys;
          // 判斷該使用者是否所有問卷都已完成
          const allSurveysCompleted = userSurveys.every(survey => survey.status === 'Data available');
          if(allSurveysCompleted){
            onSurveyCompleted(user_id, userSurveys[0].testOrder);
          }
          userSurveys.forEach(survey => {
            if (survey.status === 'Data available') {
              if (outcoming_id === user_id) {
                // 根據問卷完成情況隱藏相應元素
                toggleSurveyElementsVisibility(allSurveysCompleted, survey.scaleName);
              }
            }
          });
        }
      }
    };

    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(`data=${JSON.stringify(requestData)}`);
  }
}, 500);

// 根據問卷完成情況隱藏問卷或連結
function toggleSurveyElementsVisibility(allSurveysCompleted, scaleName) {
  const surveyElements = document.querySelectorAll('.survey');
  
  surveyElements.forEach(survey => {
      if (allSurveysCompleted) {
          // 隱藏整個 survey 元素
          survey.style.display = 'none';
      } else {
          // 隱藏包含指定 scaleName 的連結
          const links = survey.getElementsByTagName('a');
          Array.from(links).forEach(link => {
              if (link.href.includes(scaleName)) {
                  link.style.display = 'none';
              }
          });
      }
  });
}

// 這裡可以放置更新問卷狀態的邏輯，例如在用戶填寫完問卷後調用以下函數
function onSurveyCompleted(user, testOrder) {
  if (user === incoming_id) {
    user1SurveyCompleted = true;
  } else if (user === outcoming_id) {
    user2SurveyCompleted = true;
    document.getElementById('waitMessage').style.display = '';
  }
  // 更新問卷完成狀態的函數
  if (user1SurveyCompleted && user2SurveyCompleted) {
    document.getElementById('waitMessage').style.display = 'none';
    if(testOrder == 'pre'){
      document.getElementById('confirmButton').style.display = ''; // 顯示按鈕
    }
  } else {
    document.getElementById('confirmButton').style.display = 'none'; // 隱藏按鈕
  }
}

function formatDate(date) {
  var year = date.getFullYear();
  var month = String(date.getMonth() + 1).padStart(2, '0'); // Months start at 0, so add 1
  var day = String(date.getDate()).padStart(2, '0');
  var hours = String(date.getHours()).padStart(2, '0');
  var minutes = String(date.getMinutes()).padStart(2, '0');
  var seconds = String(date.getSeconds()).padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function load(xhr){
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
      if(xhr.status === 200){
        let data = xhr.response;
        // Update the chat box with the received data
        chatBox.innerHTML = data;
        // Scroll to the bottom if the mouse is not inside the chat room
        if(!chatBox.classList.contains("active")){
          scrollToBottom();
        }
        bindInit();
      }
    }
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("incoming_id="+incoming_id + "&mode=" + mode + "&isPractice=" + isPractice);
}

function bindInit(){
  // Add label emotional events
  if (mode !== 'control' && mode !== 'neutral') {
    addSmileEvents(document.querySelectorAll('.chat-box .details'));
  }

  // Help manually mark emotional emoji to bind mouse movement in and out click events
  document.getElementById('modeEmoji').querySelectorAll('img').forEach(details => {

    details.addEventListener('mouseover', () => {
      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
      if(computedStyle && !chooseEmoji){
        details.classList.add('highlight-border');
      }
    }); 

    details.addEventListener('mouseout', () => {
      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
      if(computedStyle && !chooseEmoji){
        details.classList.remove('highlight-border');
      }
    });

    details.addEventListener('click', () => {
      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
      if(computedStyle){
        chooseEmoji = true;
      }
    });
  });

}

// Define a function to update the displayed time
function updateTimer() {
  let minutes = Math.floor(totalSeconds / 60);
  let seconds = totalSeconds % 60;

  // Format to two digits
  minutes = minutes < 10 ? '0' + minutes : minutes;
  seconds = seconds < 10 ? '0' + seconds : seconds;

  // Update the displayed text content
  taskDescription.textContent = `${minutes}:${seconds}`;

  totalSeconds--;
  // Stop counting down when it reaches zero
  if (totalSeconds < 0) {
      clearInterval(timerInterval);
      taskDescription.textContent = "<h5>請點擊進入下一模式</h5>";
      taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
      document.getElementById('nextModeLink').style.display = '';
  }
}

function nextModeLink(scale){
  var nextModeLink = document.getElementById('nextModeLink');
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "php/get-nextLink.php", true);
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          if(nextModeLink){
            nextModeLink.href = data;
          }
        }
    }
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send('mode=' + mode + "&practice=" + isPractice );
  document.querySelector('.modal-header').textContent = '';
  document.getElementById('overlay').style.display = '';
  document.getElementById('perSurvey').innerHTML = 
  `<div class="survey" style="text-align: center;">`+scale+`</div>`;

  document.getElementById('labelGif').style.display = 'none';

  document.getElementById('confirmButton').style.display = 'none';
}

function displayNextLinkText(){
  var practiceTexts = {
    'self-affect': '<h1>進入下一練習測試模式</h1>',
    'Moodtag': '<h1>練習結束，進入正式實驗</h1>'
  };

  var modeTexts = {
    'neutral': '<h1>進入下一模式</h1>',
    'control': '<h1>該模式已結束，進入下一模式</h1>',
    'self-affect': '<h1>該模式已結束，進入下一模式</h1>',
    'Moodtag': '<h1>該模式已結束，進入下一模式</h1>'
  };

  if (isPractice === "1") {
    return practiceTexts[mode] || '';
  } else {
    return modeTexts[mode] || '';
  }
}

async function sendRequest(data, chatHistory, incoming_id, outcoming_id, mode, isPractice) {
  var apiKey = 'XXXXXXXXXX';
  var prompt = data.msg;

  var formattedChatHistory = chatHistory.map(item => {
    return `${item.sender}: ${item.message} (時間: ${item.time})`;
  }).join('\n');

  try {
    // 發送請求，並等待其回應
    let response = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`
      },
      body: JSON.stringify({
        model: 'gpt-3.5-turbo',
        messages: [
          {
            role: 'system',
            content: 
            `請根據以下好友之間的對話內容來標記傳送訊息者的情緒（僅限於 "anger"、"disgust"、"fear"、"sad"、"surprise"），若沒有明顯情緒變化則標記為 "null"。

            重要背景：這是兩個好友之間的日常對話，他們可能會開玩笑、使用幽默或口語化表達。在進行情緒判斷時，請特別注意他們的互動模式和上下文。根據對話的流暢度及情感轉變來做出判斷。

            情緒標籤要求：
            - 請細心理解對話中的上下文，並考慮之前的對話來推斷訊息的情緒變化，即使情緒表現不強烈，也應根據細微的情緒暗示進行標記。
            - 若對話中包含台灣常見的口語化表達或髒話（如 "三小"、"靠北"），請確保根據上下文來決定是否標記負面情緒，而非僅依賴關鍵字。
            - **只有在驚訝情緒伴隨著負面情感或不安情境時，才應標記為 "surprise"**。若驚訝情緒屬於日常對話中的中性反應，則不應標記為驚訝。
            - 需要注意的是，朋友間的幽默、戲謔或非負面的表達（如 "笑死"）不應被標記為負面情緒。
            - 對於附和性回應或不帶有明顯情緒的詢問（如「怎麼了」「對啊」「沒錯」），若無明顯情緒波動，請標記為 "null"。

            回傳格式應為： 
            "anger"、"disgust"、"fear"、"sad"、"surprise" 或 "null"。`
          },
          {
            role: 'system',
            content: `**對話歷史：**
            以下是雙方的對話歷史，user 代表發送訊息者，friend 代表收訊息者：
            ${formattedChatHistory}

            請根據對話歷史和當下訊息，提供當前的情緒標記。`
          },
          {
            role: 'user',
            content: prompt
          }
        ]
      })
    });

    // 等待並解析回應
    let dataGPT = await response.json();
    var choices = dataGPT.choices;

    if (choices && choices.length > 0) {
      var result = choices[0].message.content.trim();
      console.log('Emotion:', result); 

      // 組裝 GPT 資料
      var gptData = {
        incomingUserId: incoming_id,
        outcomingUserId: outcoming_id,
        type: 'GPT_emo',
        msg: data.msg,
        msg_id: data.msg_id,
        emotion: result,
        mode: mode,
        isPractice: isPractice,
        isMoodTag: 1
      };

      // 確保 emotion 值不是 null、undefined 或空字串
      if (gptData.emotion) {
        // 傳送資料
        conn.send(JSON.stringify(gptData));
      }
    } else {
      console.log('No choices found in the response.');
    }
  } catch (error) {
    // 更詳細的錯誤信息
    console.error('Error:', error.message || error);
  }
}

// Adjust the picture according to different modes
function updateTextContent(mode) {
  var modeIntroText = document.getElementById('modeIntroText');
  var modeIntroImg = document.getElementById('modeIntroImg');
  var taskTitle = document.getElementById('taskTitle');
  var labelGif = document.getElementById('labelGif');
  var topic = "";

  document.getElementById('modeEmoji').style.display = 'none';
  modeIntroImg.setAttribute('src', mode === 'Moodtag' ? "picture/robot.svg" : "picture/pens.png");
  modeIntroImg.style.display = 'none';
  taskDescription.style.fontSize = '20px';
  document.getElementById('confirmButton').style.display = ''; // 顯示按鈕

  if(isPractice === '0' && mode !== 'neutral'){
    var surveyURL = `http://${ipAddress}/ChatApp/survey/PANAS.html?user=${outcoming_id}&mode=${mode}&testOrder=pre`;

    document.getElementById('perSurvey').innerHTML = 
    `<div class="survey" style="text-align: center; margin-top: 40px;">
        <h3>請先填寫此問卷，點擊下方連結⬇️⬇️⬇️</h3>
        <a href="${surveyURL}" target="_blank" style="font-size: 26px;">PANAS 正面和負面情緒量表</a></br></br>
      </div> `;     

    document.getElementById('confirmButton').style.display = 'none';
  }

  // Show the current topic to chat about
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "php/get-Topic.php", true);
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
      if(xhr.status === 200){
        topic = xhr.response;
        switch(mode) {
          case 'Moodtag':
            if(isPractice === '1'){
              document.querySelector('.modal-header').innerHTML = '<h1>如何使用 Moodtag 標記情緒：</h1>';

              taskDescription.textContent = 
              `<b><h3>接下來的任務：</h3>隨意與對方傳送訊息，並試著修改或加上情緒標籤。</b>
              
              <b style="color: #ff6600;"><h3>重點：</h3>Step 1：Moodtag 會自動辨識訊息的情緒，如果你不認同 Moodtag 的標記，你可以修改或刪除標記
                  Step 2：對於 Moodtag 沒有辨識到的訊息，你也可以手動加上情緒標籤 </b>
              
              <b style="text-align: center; display: block; color: #FF0000;">更改情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
              taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
              
              labelGif.style = "border: 3px solid #FF0000";
              labelGif.src = "picture/demo/labelChange.gif";
              break;
            }
            document.getElementById('affectLabelSource').innerText = '';
            modeIntroText.textContent = "MoodTag 會即時分析你的文字，並為你標示出潛在的情緒，幫助你更好地理解自己的感受。如果有問題請隨時告訴實驗人員！";
            modeIntroText.classList.add('mode-intro-text-M'); 
            modeIntroImg.classList.add('mode-intro-img-M'); 
            taskTitle.innerText = 'Moodtag標記';
            labelGif.src = "picture/demo/labelChange.gif";
            modeIntroImg.style.display = 'block';
            taskDescription.textContent = 
            `<b>接下來的任務：需與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
            <h3 style="color: #001eff;">➡️話題：` + topic + `⬅️</h3>
            <b style="color: #ff6600;"><h3>重點：</h3>Step 1：Moodtag 會自動辨識訊息的情緒，如果你不認同 Moodtag 的標記，你可以修改或刪除標記
                Step 2：對於 Moodtag 沒有辨識到的訊息，你也可以手動加上情緒標籤 </b>
            
            <b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束。</b>
      
            <b style="text-align: center; display: block; color: #FF0000;">更改情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
      
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            labelGif.style = "border: 3px solid #FF0000";
            break;
      
          case 'self-affect':
            if(isPractice === '1'){
              document.querySelector('.modal-header').innerHTML = '<h1>如何自己標記情緒：</h1>';
              taskDescription.textContent = 
              `<b>接下來的任務：隨意與對方傳送訊息，並試著在訊息加上情緒標籤</b>

              <b style="color: #ff6600;">重點：
                 Step 1：自己傳訊息時所，若感受到負面情緒，將感受到的情緒標記下來
                 Step 2：對方傳來的訊息若激發到自己的情緒也標記下來
              </b>
        
              <b style="text-align: center; display: block; color: #FF0000;">情緒標籤操作方式如下⬇️⬇️⬇️</b>`;

              taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
              labelGif.style = "border: 3px solid #FF0000";
              labelGif.src = "picture/demo/labelDone.gif";
              break;
            }
            document.getElementById('affectLabelSource').innerText = '';
            modeIntroText.textContent = "請自己透過標記當下情緒來調節情緒。如果有問題請隨時告訴實驗人員！";
            modeIntroText.classList.add('mode-intro-text-P'); 
            modeIntroImg.classList.add('mode-intro-img-P'); 
            taskTitle.innerText = '自我標記';
            labelGif.src = "picture/demo/labelDone.gif";
            modeIntroImg.style.display = 'block'
      
            taskDescription.textContent = 
            `<b>接下來的任務：與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
            <h3 style="color: #001eff;">➡️話題：` + topic + `⬅️</h3>
            <b style="color: #ff6600;"><h3>重點</h3>
              Step 1：自己傳訊息時所，若感受到負面情緒，將感受到的情緒標記下來
              Step 2：對方傳來的訊息若激發到自己的情緒也標記下來
            </b>
      
            <b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束。</b>
      
            <b style="text-align: center; display: block; color: #FF0000;">情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            labelGif.style = "border: 3px solid #FF0000";
            break;
          case 'control':
            taskTitle.innerText = '一般模式';
            taskDescription.textContent = 
            `<b>接下來的任務：在該階段中，需與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
            <h3 style="color: #001eff;">➡️話題：` + topic + `⬅️</h3>
            <b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束</b>`;
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            labelGif.src = "";
            break;
      
          case 'neutral':
            taskTitle.innerText = '平靜模式';
            taskDescription.textContent = 
            `<b><h3>接下來的任務：聊聊實驗前在做甚麼，以恢復到平靜情緒</h3></b>

            <b>聊天時間3分鐘，時間到會由實驗人員提醒並填答問卷</b>`;
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            labelGif.src = "";
            break;
        }
      }
    } 
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("user_id="+incoming_id + "&mode=" + mode);
}

// Establish a WebSocket connection
function connectWebSocket() {

  // Establish a WebSocket connection
  conn = new WebSocket(`ws://${ipAddress}:8119`);


  conn.onopen = function() {
    console.log("Connection established!");
  };

  conn.onclose = function() {
    console.log("Connection closed, attempting to reconnect...");
    // Attempt to reconnect after 1 second
    setTimeout(connectWebSocket, 1000);
  };

  conn.onerror = function(error) {
      console.error("WebSocket error:", error);
  };

  // When a message is received from the connection
  conn.onmessage = function(e) {
    var data = JSON.parse(e.data);
    console.log(data);

    if(data.type === 'scale'){
      user1SurveyCompleted = false;
      user2SurveyCompleted = false;
      totalSeconds = 6 * 60;
      taskDescription.style.fontSize = '50px';
      taskDescription.style.fontWeight = 'bold';
      taskDescription.style.textAlign = 'center';
      taskDescription.textContent = '06:00';
      clearInterval(timerInterval);
      timerInterval = setInterval(updateTimer, 1000);
      taskDescription.insertAdjacentHTML('afterend', `<div id="buttonContainer" style="position: absolute; bottom: 10px; right: 10px;">
                                                          <button id="skipBreakButton" style="font-weight: bold; font-size: 10px;">跳過休息</button>
                                                      </div>`);

      document.getElementById('skipBreakButton').addEventListener('click', function() {
        totalSeconds = 0;
        document.getElementById('skipBreakButton').style.display = 'none';
      });
      nextModeLink(data.msg.replace(/replaceUserId/g, outcoming_id));
      document.getElementById('nextModeLink').style.display = 'none';
      scrollToBottom();

      if(data.testOrder === 'final'){
        taskDescription.textContent = "";
        document.getElementById('skipBreakButton').style.display = 'none';
        clearInterval(timerInterval);
      }
      return;
    }

    if(data.type === 'nextPage'){
      document.getElementById('nextModeLink').style.display = '';
      nextModeLink(data.msg.replace(/replaceUserId/g, outcoming_id));
      taskDescription.textContent = displayNextLinkText();
      taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
      return;
    }
    // coze returns the recognition result
    if (data.type === 'GPT_emo') {
      if ((data.incomingUserId === outcoming_id && data.outcomingUserId === incoming_id) || 
          data.incomingUserId === incoming_id && data.outcomingUserId === outcoming_id) {
        console.log('GPT結果回傳');
        document.getElementById(data.msg_id).querySelector('#affectLabel').textContent = emotionsEmojiDict[data.emotion];
        if(mode === systemName){
          document.getElementById(data.msg_id).querySelector('#emoTag').textContent = emotionsEmojiDict[data.emotion];
        }
      }
      return;
    }

    // 取得所有聊天紀錄
    const chatHistory = [];

    // 遍歷每一條聊天訊息
    document.querySelectorAll('.chat-box .chat').forEach(chat => {
      // 判斷訊息是 outgoing 還是 incoming
      const isOutgoing = chat.classList.contains('outgoing');
      const sender = isOutgoing ? 'user' : 'friend';

      // 取得訊息內容
      const messageContent = chat.querySelector('.details p').innerText;

      // 取得訊息時間
      const time = chat.querySelector('.details .time').innerText;

      // 將資料加入到聊天歷史紀錄中
      chatHistory.push({
        sender: sender,
        message: messageContent,
        time: time
      });
    });

    // When sending a message yourself, enter coze to identify the emotion of the text.
    if(data.type==='GPT' && data.from === 'Me'){
      if(useCoze){
        console.log('php/coze.php');
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "php/coze.php", true);
        xhr.onload = () => {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              let cozedata = xhr.response;
              console.log(cozedata);
              jsonString = JSON.parse(cozedata);
              var gptData = {
                incomingUserId : incoming_id,
                outcomingUserId : outcoming_id,
                type: 'GPT_emo',
                msg : data.msg,
                msg_id : data.msg_id,
                emotion : jsonString['subjective'] === null ? jsonString['objective'] : jsonString['subjective'],
                mode : mode,
                isPractice : isPractice,
                isMoodTag : 1
              };
              if(gptData.emotion !== null ||  gptData.emotion !== undefined ||  gptData.emotion !== ''){
                conn.send(JSON.stringify(gptData));
              }
            }
          }
        }
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("msg=" + encodeURIComponent(data.msg));          
      }
      else{
        sendRequest(data, chatHistory, incoming_id, outcoming_id, mode, isPractice);
      }
    }
    
    // Convert time format
    var dateTime = new Date(data.time);
    var formattedTime = `${dateTime.getHours().toString().padStart(2, '0')}:${dateTime.getMinutes().toString().padStart(2, '0')}`;

    var hasAffectLabel = data.textEmotion !== null && data.textEmotion !== '';
    var ferEmotion = hasAffectLabel ? emotionsEmojiDict[data.textEmotion] !== undefined ?  emotionsEmojiDict[data.textEmotion] : '' : '';

    // Messages sent by oneself are received by oneself
    if(data.from === 'Me' && data.incomingUserId === incoming_id && data.outcomingUserId === outcoming_id)
    {
      localStorage.setItem('userSentFirstMessage', 'true');  
      chat_html = '<div id="'+ data.msg_id +'" class="chat outgoing"><div class="details"><span class="time">'+ formattedTime + '</span>';
      
      // add LLM emotion recognition results
      if (hasAffectLabel && mode === systemName) {
        chat_html += '<div id="emoTag" style="font-size: 25px;">' + ferEmotion + '</div>';
      }

      chat_html += '<img class="smile" src="picture/smile.png" alt="Smile"><p>' + data.msg +
                  '</p></div><div id="affectLabel" class="affect outgoing">' + ferEmotion + '</div></div>';

    }
    // Receive messages from others
    else if (data.from === 'NotMe' && data.incomingUserId === outcoming_id && data.outcomingUserId === incoming_id)
    {
      localStorage.setItem('partnerSentFirstMessage', 'true');  
      var chat_html = '<div id="'+ data.msg_id +'" class="chat incoming"><div class="details"><img class="profile" src="php/images/' +
          data.img + '" alt=""><p>' + data.msg + '</p>';
      
      // add LLM emotion recognition results
      if (hasAffectLabel && mode === systemName) {
        chat_html += '<div id="emoTag" style="font-size: 25px;">' + ferEmotion + '</div>';
      }

      chat_html += '<div class="read-status"><span class="read">已讀</span><span class="time">' + formattedTime + '</span><img class="smile" src="picture/smile.png" alt="Smile"></div>' + 
                  '</div><div id="affectLabel" class="affect incoming">' + ferEmotion + '</div></div>';
    }
    // Messages from other chat rooms no display
    else{
      return;
    }

    // Hide content without messages
    if(document.getElementById('noMessages') !== null ){
      document.getElementById('noMessages').style.display = 'none';
    }
    // Append the generated chat HTML to the ChatRoom
    $('.chat-box').append(chat_html);

    // Locate the newly transmitted message and add emotion tagging and related events and add smile images events
    if (mode !== 'control' && mode !== 'neutral' ) {
      addSmileEvents([document.getElementById(data.msg_id)]);
    }
    // Scroll to the bottom of the chat box
    scrollToBottom();

    if (mode === 'Moodtag' && !localStorage.getItem('userSentFirstMessage') && !localStorage.getItem('partnerSentFirstMessage')) {
      localStorage.setItem('userSentFirstMessage', 'true');  
      localStorage.setItem('partnerSentFirstMessage', 'true');  
      location.reload();  // 強制重新整理頁面
    }
  };
}

$(document).ready(function(){
  updateTextContent(mode);

  // 初始設置兩方是否都已經傳出第一句話
  if (mode === 'neutral') {
    localStorage.removeItem('hasReloaded');
    localStorage.removeItem('userSentFirstMessage');
    localStorage.removeItem('partnerSentFirstMessage');
  }

  document.getElementById('confirmButton').addEventListener('click', function() {
    // 檢查是否符合 'Moodtag' 模式且不是練習模式，並且還沒重整過
    if (mode === 'Moodtag' && !localStorage.getItem('hasReloaded')) {
      // 設置為已重整過的狀態
      localStorage.setItem('hasReloaded', 'true');  
      location.reload();  // 強制重新整理頁面
    } else {
      // 不需要重整的情況下執行確認按鈕的原本邏輯
      handleConfirmAction();
    }
  });

  // 如果頁面已經重整過，則直接執行確認邏輯並自動點擊按鈕
  if (mode === 'Moodtag' && localStorage.getItem('hasReloaded')) {
    handleConfirmAction();  // 執行確認按鈕邏輯
    document.getElementById('confirmButton').click();  // 模擬按鈕點擊
  }

  // 確認按鈕點擊邏輯封裝到一個函數中
  function handleConfirmAction() {
    document.getElementById('overlay').style.display = 'none';
    scrollToBottom();
    // Check if the browser supports getUserMedia
    function isGetUserMediaSupported() {
      return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    if (isPractice === "0" && isRecording) {
      if (isGetUserMediaSupported()) {
        startRecording(outcoming_id, mode);
      } else {
        console.error('您的瀏覽器不支援攝影機訪問。');
        alert('無法訪問您的攝影機。請檢查您的瀏覽器設置或使用支援的瀏覽器。');
      }
    }
  }

  // Initialize the emoji picker
  $("#myTextarea").emojioneArea({
      pickerPosition: "bottom"
  })
  // Send a POST request to get chat messages
  let xhr = new XMLHttpRequest();

  if(mode === systemName){
    xhr.open("POST", "php/get-chatMoodTag.php", true);
    load(xhr)
  }
  else if(mode === selfAffect){
    xhr.open("POST", "php/get-chatSelfAffect.php", true);
    load(xhr)
  }
  else{
    xhr.open("POST", "php/get-chat.php", true);
    load(xhr)
  }
  // Initial connection
  connectWebSocket();
});

form.onsubmit = (e)=>{
    e.preventDefault();
}

// Send the message and clear the message bar
sendBtn.onclick = ()=>{
  var data = {
    incomingUserId : incoming_id,
    outcomingUserId : outcoming_id,
    msg : inputField.innerHTML,
    mode : mode,
    isPractice : isPractice,
    isMoodTag : 0,
    type: mode === systemName ? 'GPT' : 'control',
    typeStartTime : isSendingDateTime
  };
  isSendingDateTime = 0;

  conn.send(JSON.stringify(data));
  inputField.innerHTML = "";
}

chatBox.onmouseenter = ()=>{
  chatBox.classList.add("active");
}

chatBox.onmouseleave = ()=>{
  chatBox.classList.remove("active");
}

// Scroll to the bottom of the chat box
function scrollToBottom(){
  chatBox.scrollTop = chatBox.scrollHeight;
}

// Function to hide the emojiImages element
function hideEmojiImages(event) {
  var emojiImages = document.querySelector('.emoji-images');
  if (emojiImages && !event.target.classList.contains('smile')) {
    emojiImages.remove();
    document.removeEventListener('click', hideEmojiImages);
  }

  var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
  if(event.target.id === 'modeIntroImg' && computedStyle){
    document.querySelector(".wrapper").style.cursor = "auto";
    // Iterate through these img tags and set their border to none
    document.getElementById('modeEmoji').querySelectorAll('img').forEach(img => {
        img.style.border = 'none';
    });
    chooseEmoji = false;
  }
}

// add label emotion related events
function addSmileEvents(detailsElements){
  // Display emotion tagging icons when the mouse hovers over the message element
  detailsElements.forEach(details => {
    details.addEventListener('mouseover', () => {
      var smileElement = details.querySelector('.smile');
      var timeElement = details.querySelector('.time');
  
      // Make sure you find the corresponding element
      if (smileElement && timeElement) {
        //When the mouse is moved over the message, the smiley face and hidden time will be displayed
        details.querySelector('.smile').style.display = 'block';
        details.querySelector('.time').style.display = 'none';
        // If the emotion is already marked, change the background color of the smiley face to yellow
        var smileBackgroundColor = details.closest('.chat').querySelector("#affectLabel").textContent !== "" ? 'gold' : '';
        details.querySelector('.smile').style.backgroundColor = smileBackgroundColor;
      }

      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
      // Check if the required mouse style is included in the style
      if (computedStyle) {
        // Add a border when the mouse enters
        details.querySelector('p').classList.add('highlight-border');
      }     
    });

    details.addEventListener('mouseout', () => {
      var smileElement = details.querySelector('.smile');
      var timeElement = details.querySelector('.time');
  
      // Make sure you find the corresponding element
      if (smileElement && timeElement) {
        details.querySelector('.smile').style.display = 'none';
        details.querySelector('.time').style.display = 'block';
      }

      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');

      // Check if the required mouse style is included in the style
      if (computedStyle) {
        // Add a border when the mouse enters
        details.querySelector('p').classList.remove('highlight-border');
      }
    });
    var msgParent = details.closest('.chat');
    details.addEventListener('click', () => {

      var chooseEmojiImg = document.querySelector('img.highlight-border');
      var computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('picture/pens.ico');
      if(chooseEmojiImg === null || computedStyle === null){
        return;
      }      
      var source = (mode === 'OP') ? 'HumanO' : (mode === 'SP') ? 'HumanS' : undefined;

      if (computedStyle) {
        // websocket transmits tag data to the other party
        var data = {
          incomingUserId : incoming_id,
          outcomingUserId : outcoming_id,
          msg_id : msgParent.getAttribute('id'),
          emotion : chooseEmojiImg.alt,
          mode : mode,
          isPractice : isPractice,
          type: 'humanAffect'
        };
      
        conn.send(JSON.stringify(data));

        // Send XMLHttpRequest to insert emoji into database
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "php/insert-textEmo.php", true);
        xhr.onload = ()=>{
          if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
              let data = xhr.response;
              console.log(data);
            }
          } 
        }
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("emotion=" + chooseEmojiImg.alt + "&msg_id=" + msgParent.getAttribute('id') + "&user_id="+incoming_id + "&oldEmo=" + "" + "&mode=" + mode + "&source=" + source + "&labeler_user_id=" + outcoming_id);

        // Iterate through these img tags and set their border to none
        document.getElementById('modeEmoji').querySelectorAll('img').forEach(img => {
          img.classList.remove('highlight-border');
        });
        // mouse restore
        document.querySelector(".wrapper").style.cursor = "auto";
        chooseEmoji = false;
        details.querySelector('p').classList.add('affectDetailOutline');
        details.parentElement.querySelector('#affectLabelByHuman').textContent = emotionsEmojiDict[chooseEmojiImg.alt];
      }
    });
  });

  // Add a click event listener to each smile image element
  detailsElements.forEach(smile => {
    smile.addEventListener('click', () => {

      var emojiImagesElement = document.querySelector('.emoji-images');
      if (emojiImagesElement) {
        emojiImagesElement.remove();
      }

      document.removeEventListener('click', hideEmojiImages);
      
      // Create a div element to contain the emoji images
      var emojiImages = document.createElement('div');
      emojiImages.className = 'emoji-images';

      for (const emotion in emotionsEmojiDict) {
        const emoji = emotionsEmojiDict[emotion];

        const img = document.createElement('img');
        img.src = `https://fonts.gstatic.com/s/e/notoemoji/latest/${encodeURIComponent(emoji.codePointAt(0).toString(16))}/512.gif`;
        img.alt = emoji;
        img.width = 34; 
        img.height = 34; 
        emojiImages.appendChild(img);
        img.style.margin  = '8px';

        // Find the currently marked emotion and zoom in
        var msgParent = smile.closest('.chat');
        if(msgParent.querySelector("#affectLabel").textContent === emotionsEmojiDict[emotion]){
          img.width = 50; 
          img.height = 50; 
          img.style.margin  = '0px';
        }

        img.addEventListener('click', () => {
          console.log('click the emotion label:', emoji);
          var oldEmo = Object.entries(emotionsEmojiDict).find(([emotion, emoji]) => emoji === msgParent.querySelector("#affectLabel").textContent)?.[0];

          // Find the parent element and get the value of the data-msg-id attribute
          var affectLabel = msgParent.querySelector("#affectLabel");
          affectLabel.textContent = (oldEmo === emotion) ? "" : emoji;

          // Send XMLHttpRequest to insert emoji into database
          let xhr = new XMLHttpRequest();
          xhr.open("POST", "php/insert-textEmo.php", true);
          xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
              if(xhr.status === 200){
                let data = xhr.response;
                console.log(data);
              }
            } 
          }
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhr.send("emotion=" + emotion + "&msg_id=" + msgParent.getAttribute('id') + "&user_id="+outcoming_id + "&oldEmo=" + oldEmo);

          lastAffectTime = new Date();
        });

        // Add mouseenter event listener
        img.addEventListener('mouseenter', () => {
          var affectEmo = msgParent.querySelector("#affectLabel").textContent;
          if(affectEmo !== ""){
            var selectEmo = document.querySelectorAll('.emoji-images img[alt="' + affectEmo + '"]');
            selectEmo[0].style.width = "34px"; 
            selectEmo[0].style.height = "34px"; 
            selectEmo[0].style.margin = '8px'; 
          }

          img.style.width = '50px';
          img.style.height = '50px';
          img.style.margin  = '0px';

        });

        // Add mouseleave event listener
        img.addEventListener('mouseleave', () => {
          img.style.width = '34px';
          img.style.height = '34px';
          img.style.margin  = '8px';
          
          // If it is the selected emotion, zoom in
          var affectEmo = msgParent.querySelector("#affectLabel").textContent;
          if(affectEmo !== ""){
            var selectEmo = document.querySelectorAll('.emoji-images img[alt="' + affectEmo + '"]');
            selectEmo[0].style.width = "50px"; 
            selectEmo[0].style.height = "50px"; 
            selectEmo[0].style.margin = '0px'; 
          }
        });
      };

      var smilePosition = smile.getBoundingClientRect();
      // Set the initial position of the emojiImages element
      let emojiImagesTop = smilePosition.top - emojiImages.offsetHeight - 50;

      // Set the position of the emojiImages element
      emojiImages.style.position = 'absolute';
      emojiImages.style.top = `${emojiImagesTop}px`;

      // Append the emojiImages element to the body
      document.body.appendChild(emojiImages);

      // Hide the emojiImages element when clicking elsewhere
      document.addEventListener('click', hideEmojiImages);
    });
  });
}