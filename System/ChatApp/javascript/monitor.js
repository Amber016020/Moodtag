var leftChatBox = document.querySelector("#left-chat-box");
var rightChatBox = document.querySelector("#right-chat-box");
var survey = document.querySelector("#survey");
var leftUserSelect;
var rightUserSelect;
var modeSelect = document.querySelector("#modeSelect").value = 'self-affect';
var isPractice = document.getElementById('practiceCheckbox').checked = true;
var conn;
var reflesh = false;
var parts;
var testsequence;
var newModeSelect = modeSelect;
var newIsPractice = isPractice;

var totalSeconds;
var time = document.getElementById('time');
var currentState = document.getElementById('currentState');
var timerInterval;

import { ipAddress } from './config.js';

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

function start(){
  modeSelect = document.querySelector("#modeSelect").value = newModeSelect;
  isPractice = document.getElementById('practiceCheckbox').checked = newIsPractice;

  if(testsequence[2].pattern === modeSelect && !isPractice){
    document.getElementById('postSurvey').style.display = 'none';
  }
}

function updateState(){
  return new Promise((resolve, reject) => {
    if(isPractice && modeSelect === 'Moodtag'){
      document.getElementById('nextPractice').style.display = 'none';
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/get-nextLink.php", true);
    xhr.onload = () => {
      if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          var params = new URLSearchParams(xhr.response.split('?')[1]);
          newModeSelect = params.get('mode');
          newIsPractice = params.get('practice') === 'true';

          resolve(); 
        } else {
          reject('Error: ' + xhr.status);
        }
      }
    }
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send('mode=' + modeSelect + "&practice=" + isPractice + "&outgoing_id=" + parts[0]);
  });
}

document.getElementById('queryBtn').addEventListener('click', function() {
   parts = document.querySelector("#userGroupSelect").value.split(',');

  if (parts.length !== 2) {
      console.log('String format is incorrect');
      return;
  }

  [leftUserSelect, rightUserSelect] = parts;

  // 定義 AJAX 請求的共用函數
  var sendRequest = (url, data, callback) => {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = () => {
          if (xhr.status === 200) callback(xhr.responseText);
      };
      xhr.send(data);
  };

  // 取得使用者資料
  sendRequest("php/get-usersData.php", `userIds=${JSON.stringify(parts)}`, (response) => {
      var userDatas = JSON.parse(response);
      document.querySelectorAll(".chat-area").forEach((chatArea, index) => {
          var { lname, fname, img } = userDatas[index];
          chatArea.querySelector("span").textContent = lname + fname;
          chatArea.querySelector("img").src = `php/images/${img}`;
      });
  });

  // 取得測試順序
  sendRequest("php/get-testsequence.php", `user_id=${leftUserSelect}`, (response) => {
      testsequence = JSON.parse(response);
  });

  modeSelect = document.querySelector("#modeSelect").value;
  isPractice = document.getElementById('practiceCheckbox').checked === true ? 1 : 0;
  reflesh = true;
})

document.getElementById('skipBreakButton').addEventListener('click', function() {
  totalSeconds = 0;
});

document.getElementById('timerButton').addEventListener('click', function() {
  var time = document.getElementById('time');
      time.style.fontSize = '50px';
      time.style.fontWeight = 'bold';
      time.style.textAlign = 'center';
      time.textContent = '10:00';
  totalSeconds = 10 * 60;
  clearInterval(timerInterval);
  timerInterval = setInterval(updateTimer, 1000);
});

// 處理量表的conn data
function sendNextPage() {
  var data = {
    incomingUserId: leftUserSelect,
    outcomingUserId: rightUserSelect,
    type: 'nextPage',
    testOrder: '',
    mode: modeSelect,
    isPractice: isPractice,
    isMoodTag: 0,
    msg: ''
  };

  conn.send(JSON.stringify(data));
}

// 處理量表的conn data
function sendSurvey(surveyData, testOrder) {
  var surveyHTML = `
  <div class="survey" style="text-align: center; margin-top: 40px;">
    <h3>此模式完成~請填寫以下${surveyData.length}份問卷</h3></br>
  `;

  for (let i = 0; i < surveyData.length; i++) {
    var survey = surveyData[i];
    var surveyURL = `http://${ipAddress}/ChatApp/survey/${survey.page}.html?user=replaceUserId&mode=${modeSelect}&testOrder=${survey.testOrder}`;
    surveyHTML += `
      <a href="${surveyURL}" target="_blank" style="font-size: 26px;">${survey.name}</a></br>
    `;
  }

  surveyHTML += `
      <br>
      <b>點擊上方連結填寫問卷⬆️⬆️⬆️</b>
    </div> `;

  var data = {
    incomingUserId: leftUserSelect,
    outcomingUserId: rightUserSelect,
    type: 'scale',
    testOrder: testOrder,
    mode: modeSelect,
    isPractice: isPractice,
    isMoodTag: 0,
    msg: surveyHTML
  };

  conn.send(JSON.stringify(data));
}

setInterval(() => {
  if (reflesh){
    chatLoad();

    let xhr2 = new XMLHttpRequest();
    xhr2.open("POST", "php/get-survey.php", true);
    xhr2.onload = ()=>{
      if(xhr2.readyState === XMLHttpRequest.DONE){
        if(xhr2.status === 200){
          let data = xhr2.response;
          // Update the chat box with the received data
          survey.innerHTML = data;
          // Scroll to the bottom if the mouse is not inside the chat room
          if(!rightChatBox.classList.contains("active")){
            scrollToBottom();
          }
        }
      }
    }
    xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr2.send("user_id="+leftUserSelect + "&mode=" + modeSelect + "&isPractice=" + isPractice);

    let xhr3 = new XMLHttpRequest();
    xhr3.open("POST", "php/get-survey.php", true);
    xhr3.onload = ()=>{
      if(xhr3.readyState === XMLHttpRequest.DONE){
        if(xhr3.status === 200){
          let data = xhr3.response;
          // Update the chat box with the received data
          document.querySelector("#survey1").innerHTML = data;
          // Scroll to the bottom if the mouse is not inside the chat room
          if(!rightChatBox.classList.contains("active")){
            scrollToBottom();
          }
        }
      }
    }
    xhr3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr3.send("user_id="+rightUserSelect + "&mode=" + modeSelect + "&isPractice=" + isPractice);
  }

}, 500);

// 取的監看選擇對象
function getPartnerAndUser(){
  let xhrUserList = new XMLHttpRequest();
  xhrUserList.open("POST", "php/get-usersList.php", true);
  xhrUserList.onload = ()=>{
    if(xhrUserList.readyState === XMLHttpRequest.DONE){
      if(xhrUserList.status === 200){
        let data = xhrUserList.response;
        document.querySelector("#userGroupSelect").innerHTML = data;
      }
    }
  }
  xhrUserList.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhrUserList.send();
}

// 更新user的名字
function updateUserInfo(uniqueId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `php/get-chatArea.php?unique_id=${uniqueId}`, true);
  xhr.onload = function() {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      const data = JSON.parse(xhr.responseText);
      if (data) {
        document.getElementById('user-img').src = `php/images/${data.img}`;
        document.getElementById('user-name').textContent = `${data.fname} ${data.lname}`;
      }
    }
  };
  xhr.send();
}

// 通用的聊天室内容加载函数
function loadChatContent(url, chatBox, incomingId, outgoingId) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.onload = () => {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        let data = xhr.response;
        // 更新聊天框内容
        chatBox.innerHTML = data;
        // 如果鼠标不在聊天室内，滚动到底部
        if (!chatBox.classList.contains("active")) {
          scrollToBottom(chatBox);
        }
      }
    }
  };
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("incoming_id=" + incomingId + "&outgoing_id=" + outgoingId + "&mode=" + modeSelect + "&isPractice=" + isPractice);
}

// 调用通用函数加载左方的聊天室内容
function chatLoad() {
  let url = "php/get-chatMoodTagMonitor.php";
  loadChatContent(url, leftChatBox, rightUserSelect, leftUserSelect);
  loadChatContent(url, rightChatBox, leftUserSelect, rightUserSelect);
}

// Establish a WebSocket connection
function connectWebSocket() {

  // Establish a WebSocket connection
  // todo：根據當前主機位置修改?
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
      leftChatBox.append(data.msg);
      rightChatBox.append(data.msg);
      scrollToBottom();
      return;
    }
  };
}

// 定義一個函數來更新顯示的時間
function updateTimer() {
  // 計算分鐘和秒
  let minutes = Math.floor(totalSeconds / 60);
  let seconds = totalSeconds % 60;

  // 格式化為兩位數
  minutes = minutes < 10 ? '0' + minutes : minutes;
  seconds = seconds < 10 ? '0' + seconds : seconds;

  // 更新顯示的文本內容
  time.textContent = `${minutes}:${seconds}`;

  // 每秒減少一秒
  totalSeconds--;
  // 當倒數到零時停止倒數
  if (totalSeconds < 0) {
      clearInterval(timerInterval);
      currentState.textContent = "開始下一模式";
      start();
  }
}

$(document).ready(function(){
  getPartnerAndUser();

  document.getElementById('nextPractice').addEventListener('click', function() {
    sendNextPage();
    updateState().then(start);
  });

  document.getElementById('baseSurvey').addEventListener('click', function() {
    const surveyData = [
      { name: "PANAS 正面和負面情緒量表", page: "PANAS" , testOrder: "base"},
    ];
    sendSurvey(surveyData, 'post');
    updateState();
    var time = document.getElementById('time');
    time.style.fontSize = '50px';
    time.style.fontWeight = 'bold';
    time.style.textAlign = 'center';
    time.textContent = '06:00';
    totalSeconds = 6 * 60;
    clearInterval(timerInterval);
    timerInterval = setInterval(updateTimer, 1000);
    document.getElementById('currentState').textContent = '休息時間倒數：';
    document.getElementById('baseSurvey').style.display = 'none';
  });

  // Send post-survey
  document.getElementById('postSurvey').addEventListener('click', function() {
    const surveyData = [
      { name: "PANAS 正面和負面情緒量表", page: "PANAS" , testOrder: "post"},
      { name: "PAQ 述情障礙量表", page: "PAQ", testOrder: "post" },
    ];

    sendSurvey(surveyData, 'post');
    updateState();
    var time = document.getElementById('time');
        time.style.fontSize = '50px';
        time.style.fontWeight = 'bold';
        time.style.textAlign = 'center';
        time.textContent = '06:00';
    totalSeconds = 6 * 60;
    clearInterval(timerInterval);
    timerInterval = setInterval(updateTimer, 1000);
    document.getElementById('currentState').textContent = '休息時間倒數：';
  });

  // Send final survey
  document.getElementById('finalSurvey').addEventListener('click', function() {
    const surveyData = [
      { name: "PANAS 正面和負面情緒量表", page: "PANAS" , testOrder: "post"},
      { name: "PAQ 述情障礙量表", page: "PAQ", testOrder: "post" },
      { name: "TPA 人機信任測量量表", page: "TPA", testOrder: "post" },
      { name: "ABCCT 評估溝通技術對情感影響量表", page: "ABCCT", testOrder: "post" },
      { name: "聊天模式使用偏好調查", page: "Preference", testOrder: "post" },
    ];

    sendSurvey(surveyData, 'final');
  });

  // Initial connection
  connectWebSocket();
});

// Scroll to the bottom of the chat box
function scrollToBottom(){
  leftChatBox.scrollTop = leftChatBox.scrollHeight;
  rightChatBox.scrollTop = rightChatBox.scrollHeight;
}
