/* eslint-disable */

// === Imports ===
import { startRecording } from './record.js';
import { ipAddress } from './config.js';

// === DOM refs / globals ===
const form = document.querySelector(".typing-area");
const incoming_id = form.querySelector("#incoming_id").value; // 收訊者(對方) userId
const outcoming_id = form.querySelector("#outcoming_id").value; // 發訊者(自己) userId
const isPractice = form.querySelector("#practice").value;
const mode = form.querySelector("#mode").value;
const sendBtn = form.querySelector("button");
const chatBox = document.querySelector(".chat-box");
const taskDescription = document.getElementById('taskDescription');
let inputField = document.querySelector('.emojionearea-editor'); // 會在 emojioneArea init 後才存在

const affectList = document.getElementById("affectList");
const affectPanel = document.getElementById("affectPanel");

// === State ===
let eventListenerAdded = false;
let chooseEmoji = false;
let isRecording = false;
let lastAffectTime = new Date();
let isSendingDateTime = 0;
let timerInterval;
let totalSeconds;
const systemName = 'MoodTag';
const selfAffect = 'Manual';
let isComposing = false;
let conn;
let user1SurveyCompleted = false;
let user2SurveyCompleted = false;

const emotionsEmojiDict = {
  anger: "😡",
  disgust: "🤮",
  fear: "😨",
  sad: "😢",
  surprise: "😮"
};

// === Wake Lock（保持螢幕常亮）===
let wakeLock = null;
async function requestWakeLock() {
  try {
    wakeLock = await navigator.wakeLock.request('screen');
    wakeLock.addEventListener('release', () => {
      wakeLock = null;
      // 可選：自動再申請（有些瀏覽器不建議頻繁申請，依需求）
      // requestWakeLock();
    });
  } catch (_) {}
}
if ('wakeLock' in navigator) {
  document.addEventListener('DOMContentLoaded', requestWakeLock);
}

// === 工具 ===
function formatDate(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  const H = String(date.getHours()).padStart(2, '0');
  const M = String(date.getMinutes()).padStart(2, '0');
  const S = String(date.getSeconds()).padStart(2, '0');
  return `${y}-${m}-${d} ${H}:${M}:${S}`;
}

function scrollToBottom() {
  chatBox.scrollTop = chatBox.scrollHeight;
}

// === Emoji 圖片（選單）輔助 ===
function emojiToHexCodePoint(emojiChar) {
  const codePoints = [];
  for (const c of emojiChar) codePoints.push(c.codePointAt(0).toString(16));
  return codePoints[0];
}

// === 問卷顯示/隱藏 ===
function toggleSurveyElementsVisibility(allSurveysCompleted, scaleName) {
  const surveyElements = document.querySelectorAll('.survey');
  surveyElements.forEach(survey => {
    if (allSurveysCompleted) {
      survey.style.display = 'none';
    } else {
      const links = survey.getElementsByTagName('a');
      Array.from(links).forEach(link => {
        if (link.href.includes(scaleName)) link.style.display = 'none';
      });
    }
  });
}

function onSurveyCompleted(user, testOrder) {
  if (user === incoming_id) {
    user1SurveyCompleted = true;
  } else if (user === outcoming_id) {
    user2SurveyCompleted = true;
    const waitMsg = document.getElementById('waitMessage');
    if (waitMsg) waitMsg.style.display = '';
  }
  const bothDone = user1SurveyCompleted && user2SurveyCompleted;
  const waitMsg2 = document.getElementById('waitMessage');
  if (waitMsg2) waitMsg2.style.display = bothDone ? 'none' : '';
  const confirmBtn = document.getElementById('confirmButton');
  if (!confirmBtn) return;
  if (bothDone) {
    if (testOrder == 'pre') confirmBtn.style.display = '';
  } else {
    confirmBtn.style.display = 'none';
  }
}

// === 倒數 ===
function updateTimer() {
  let minutes = Math.floor(totalSeconds / 60);
  let seconds = totalSeconds % 60;

  minutes = minutes < 10 ? '0' + minutes : String(minutes);
  seconds = seconds < 10 ? '0' + seconds : String(seconds);

  taskDescription.textContent = `${minutes}:${seconds}`;
  totalSeconds--;
  if (totalSeconds < 0) {
    clearInterval(timerInterval);
    taskDescription.textContent = "<h5>請點擊進入下一模式</h5>";
    taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
    const nextLink = document.getElementById('nextModeLink');
    if (nextLink) nextLink.style.display = '';
  }
}

function nextModeLink(scale) {
  const nextModeLinkEl = document.getElementById('nextModeLink');
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "get-nextLink.php", true);
  xhr.onload = () => {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      const data = xhr.response;
      if (nextModeLinkEl) nextModeLinkEl.href = data;
    }
  };
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send('mode=' + encodeURIComponent(mode) + "&practice=" + encodeURIComponent(isPractice));

  const modalHeader = document.querySelector('.modal-header');
  if (modalHeader) modalHeader.textContent = '';
  const overlay = document.getElementById('overlay');
  if (overlay) overlay.style.display = '';
  const perSurvey = document.getElementById('perSurvey');
  if (perSurvey) {
    perSurvey.innerHTML = `
      <div class="survey" style="text-align: center;">${scale}</div>
    `;
  }

  const labelGif = document.getElementById('labelGif');
  if (labelGif) labelGif.style.display = 'none';
  const confirmButton = document.getElementById('confirmButton');
  if (confirmButton) confirmButton.style.display = 'none';
}

function displayNextLinkText() {
  const practiceTexts = {
    'Manual': '<h1>進入下一練習測試模式</h1>',
    'MoodTag': '<h1>練習結束，進入正式實驗</h1>'
  };
  const modeTexts = {
    'neutral': '<h1>進入下一模式</h1>',
    'Control': '<h1>該模式已結束，進入下一模式</h1>',
    'Manual': '<h1>該模式已結束，進入下一模式</h1>',
    'MoodTag': '<h1>該模式已結束，進入下一模式</h1>'
  };
  if (isPractice === "1") return practiceTexts[mode] || '';
  return modeTexts[mode] || '';
}

// === OpenAI Proxy ===
async function sendRequest(data, chatHistory, incoming_id, outcoming_id, mode, isPractice) {
  const prompt = data.msg;
  const formattedChatHistory = chatHistory.map(item =>
    `${item.sender}: ${item.message} (時間: ${item.time})`
  ).join('\n');

  try {
    const response = await fetch('../php/openai-proxy.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'gpt-3.5-turbo',
        messages: [
          {
            role: 'system',
            content: `請根據以下好友之間的對話內容來標記傳送訊息者的情緒（僅限於 "anger"、"disgust"、"fear"、"sad"、"surprise"），若沒有明顯情緒變化則標記為 "null"。

重要背景：這是兩個好友之間的日常對話，他們可能會開玩笑、使用幽默或口語化表達。在進行情緒判斷時，請特別注意他們的互動模式和上下文。根據對話的流暢度及情感轉變來做出判斷。

情緒標籤要求：
- 請細心理解對話中的上下文，並考慮之前的對話來推斷訊息的情緒變化，即使情緒表現不強烈，也應根據細微的情緒暗示進行標記。
- 若對話中包含台灣常見的口語化表達或髒話（如 "三小"、"靠北"），請確保根據上下文來決定是否標記負面情緒，而非僅依賴關鍵字。
- **只有在驚訝情緒伴隨著負面情感或不安情境時，才應標記為 "surprise"**。若驚訝情緒屬於日常對話中的中性反應，則不應標記為驚訝。
- 朋友間的幽默或非負面表達（如 "笑死"）不應標記為負面情緒。
- 對於附和性回應或不帶明顯情緒的詢問（如「怎麼了」「對啊」「沒錯」），若無明顯情緒波動，請標記為 "null"。

回傳格式：僅 "anger"、"disgust"、"fear"、"sad"、"surprise" 或 "null"。`
          },
          {
            role: 'system',
            content: `**對話歷史：**
以下是雙方的對話歷史，user 代表發送訊息者，friend 代表收訊息者：
${formattedChatHistory}

請根據對話歷史和當下訊息，提供當前的情緒標記。`
          },
          { role: 'user', content: prompt }
        ]
      })
    });

    if (!response.ok) {
      const errText = await response.text().catch(() => '');
      throw new Error(`Proxy error ${response.status}: ${errText}`);
    }

    const dataGPT = await response.json();
    const choices = dataGPT.choices;

    if (choices && choices.length > 0) {
      const result = (choices[0].message.content || '').trim();
      const gptData = {
        incomingUserId: incoming_id,
        outcomingUserId: outcoming_id,
        type: 'GPT_emo',
        msg: data.msg,
        msg_id: data.msg_id,
        emotion: result,
        mode,
        isPractice,
        isMoodTag: 1
      };
      if (gptData.emotion) conn.send(JSON.stringify(gptData));
    }
  } catch (error) {
    console.error('Error:', error?.message || error);
  }
}

// === 初始載入 chat HTML ===
function load(xhr) {
  xhr.onload = () => {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      const data = xhr.response;
      chatBox.innerHTML = data;
      scrollToBottom();
      bindInit();                // 綁事件
      rebuildAffectListFromDOM(); // 鏡射右欄
    }
  };
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("incoming_id=" + encodeURIComponent(incoming_id) + "&mode=" + encodeURIComponent(mode) + "&isPractice=" + encodeURIComponent(isPractice));
}

// === 初始化事件/行為 ===
function bindInit() {
  if (mode !== 'Control' && mode !== 'neutral') {
    addSmileEvents(document.querySelectorAll('.chat-box .details'));
  }
  const modeEmoji = document.getElementById('modeEmoji');
  if (modeEmoji) {
    modeEmoji.querySelectorAll('img').forEach(details => {
      details.addEventListener('mouseover', () => {
        const computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('images/pens.ico');
        if (computedStyle && !chooseEmoji) details.classList.add('highlight-border');
      });
      details.addEventListener('mouseout', () => {
        const computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('images/pens.ico');
        if (computedStyle && !chooseEmoji) details.classList.remove('highlight-border');
      });
      details.addEventListener('click', () => {
        const computedStyle = window.getComputedStyle(document.querySelector('.wrapper')).cursor.includes('images/pens.ico');
        if (computedStyle) chooseEmoji = true;
      });
    });
  }
}

// === 右欄 Mirror + 絕對定位對齊 + 同步捲動 + 高亮 ===

// 右側 hover → 高亮左側對應訊息
function setPairHighlight(id, on) {
  const msg = chatBox.querySelector(`[data-msg-id="${id}"]`) || document.getElementById(id);
  const li  = affectList.querySelector(`.affect-item[data-msg-id="${id}"]`);
  if (msg) msg.classList.toggle('highlight-pair', !!on);
  if (li)  li.classList.toggle('is-hover', !!on);
}

affectList.addEventListener('mouseover', (e) => {
  const li = e.target.closest('.affect-item');
  if (!li) return;
  setPairHighlight(li.dataset.msgId, true);
});
affectList.addEventListener('mouseout', (e) => {
  const li = e.target.closest('.affect-item');
  if (!li) return;
  setPairHighlight(li.dataset.msgId, false);
});
affectList.addEventListener('focusin', (e) => {
  const li = e.target.closest('.affect-item');
  if (!li) return;
  setPairHighlight(li.dataset.msgId, true);
});
affectList.addEventListener('focusout', (e) => {
  const li = e.target.closest('.affect-item');
  if (!li) return;
  setPairHighlight(li.dataset.msgId, false);
});

// 絕對定位鏡射：建立 li 並加入一層 inner（視覺）
function mirrorOneMessageToRight(msgEl) {
  const id = msgEl.dataset.msgId || msgEl.id;
  if (!id) return;

  let li = affectList.querySelector(`.affect-item[data-msg-id="${id}"]`);
  if (!li) {
    const manualLabel  = msgEl.querySelector('.affect-label.manual')?.textContent.trim()  || '';
    const moodtagLabel = msgEl.querySelector('.affect-label.moodtag')?.textContent.trim() || '';

    li = document.createElement('li');
    li.className = 'affect-item';
    li.dataset.msgId = id;
    li.tabIndex = 0;
    li.innerHTML = `
      <div class="affect-item__inner">
        <div class="affect-item__col affect-item__col--manual">
          <span class="dot dot--self"></span>
          <span class="affect-emoji affect-emoji--manual">${manualLabel}</span>
        </div>
        <div class="affect-item__divider"></div>
        <div class="affect-item__col affect-item__col--moodtag">
          <span class="dot dot--peer"></span>
          <span class="affect-emoji affect-emoji--moodtag">${moodtagLabel}</span>
        </div>
      </div>`;
    affectList.appendChild(li);

    new ResizeObserver(() => rafLayout()).observe(msgEl);
    watchImages(msgEl);
  }
  rafLayout();
}

// 右欄絕對定位：貼齊左欄訊息的 offsetTop/height
function layoutAffectList() {
  // 右欄視窗高度跟左欄一致
  affectList.style.height = chatBox.clientHeight + 'px';

  // 右欄內容高度 = 左欄捲動範圍 + 右欄視窗高度
  const leftScrollRange = Math.max(0, chatBox.scrollHeight - chatBox.clientHeight);
  const rightViewport   = affectList.clientHeight; // 上面剛設定
  let spacer = affectList.querySelector('.affect-list__spacer');
  if (!spacer) {
    spacer = document.createElement('div');
    spacer.className = 'affect-list__spacer';
    affectList.insertBefore(spacer, affectList.firstChild);
  }
  spacer.style.height = (leftScrollRange + rightViewport) + 'px';

  // 絕對定位每條對齊左側訊息
  chatBox.querySelectorAll('.chat[data-msg-id], .chat[id]').forEach(msgEl => {
    const id = msgEl.dataset.msgId || msgEl.id;
    const li = affectList.querySelector(`.affect-item[data-msg-id="${id}"]`);
    if (!li) return;
    const top = msgEl.offsetTop;
    const height = msgEl.getBoundingClientRect().height;
    li.style.position = 'absolute';
    li.style.left = '0';
    li.style.right = '0';
    li.style.top = top + 'px';
    li.style.height = height + 'px';
  });
}

// rAF 節流
let _raf = 0;
function rafLayout() {
  if (_raf) return;
  _raf = requestAnimationFrame(() => {
    _raf = 0;
    layoutAffectList();
  });
}

// 監看 chatBox 尺寸與 DOM 變化
const roChat = new ResizeObserver(rafLayout);
roChat.observe(chatBox);
const moChat = new MutationObserver(rafLayout);
moChat.observe(chatBox, { childList: true, subtree: true, characterData: true });
window.addEventListener('resize', rafLayout);

// 圖片載入後也重算
function watchImages(el){
  el.querySelectorAll('img').forEach(img=>{
    if (!img.complete) img.addEventListener('load', rafLayout, {once:true});
  });
}
watchImages(chatBox);

// 重新建立右欄（初始載入）
function rebuildAffectListFromDOM() {
  affectList.innerHTML = '';
  chatBox.querySelectorAll('.chat').forEach(mirrorOneMessageToRight);
  rafLayout();
}

// 左右同步捲動（以內容比例）
const leftScroller  = chatBox;
const rightScroller = affectList;
let syncing = 0; // 0=無、1=左帶右、2=右帶左
function syncByRatio(src, dst) {
  const sh = Math.max(1, src.scrollHeight - src.clientHeight);
  const dh = Math.max(1, dst.scrollHeight - dst.clientHeight);
  const r  = src.scrollTop / sh;
  dst.scrollTop = r * dh;
}
leftScroller.addEventListener("scroll", () => {
  if (syncing === 2) return;
  syncing = 1;
  syncByRatio(leftScroller, rightScroller);
  requestAnimationFrame(() => syncing = 0);
}, { passive: true });
rightScroller.addEventListener("scroll", () => {
  if (syncing === 1) return;
  syncing = 2;
  syncByRatio(rightScroller, leftScroller);
  requestAnimationFrame(() => syncing = 0);
}, { passive: true });

// === 問卷/警示/輸入框 週期性處理 ===
setInterval(() => {
  if (!inputField) {
    inputField = document.querySelector('.emojionearea-editor');
    if (!eventListenerAdded && inputField) {
      inputField.addEventListener("keydown", function (event) {
        if (event.key === "Enter" &&
            (inputField.textContent.trim() !== "" || inputField.querySelector('img') !== null) &&
            !isComposing) {
          event.preventDefault();
          sendBtn.click();
        }
      });
      eventListenerAdded = true;
      const editor = inputField;
      const handleInput = () => {
        if (isSendingDateTime === 0) isSendingDateTime = formatDate(new Date());
      };
      editor.addEventListener('keydown', handleInput);
      inputField.addEventListener('compositionstart', () => { isComposing = true; });
      inputField.addEventListener('compositionend', () => { isComposing = false; });
    }
  } else {
    if (inputField.textContent.trim() !== '' || inputField.querySelector('img') !== null) {
      sendBtn.classList.add("active");
    } else {
      sendBtn.classList.remove("active");
    }
  }

  if (mode === selfAffect && (new Date() - lastAffectTime) > 60000) {
    const alertMessage = document.getElementById('alertMessage');
    if (alertMessage) {
      alertMessage.style.display = '';
      setTimeout(() => {
        alertMessage.style.display = 'none';
        lastAffectTime = new Date();
      }, 10000);
    }
  }

  if ((!user1SurveyCompleted || !user2SurveyCompleted) && isPractice === '0') {
    const xhr = new XMLHttpRequest();
    const surveyLinks = document.querySelectorAll('a[href*="survey/"]');
    const surveys = [];
    surveyLinks.forEach(link => {
      const url = new URL(link.href);
      const path = url.pathname;
      const match = path.match(/\/survey\/(.+?)\.html/);
      const scaleName = match ? match[1] : "PANAS";
      const testOrder = url.searchParams.get('testOrder');
      surveys.push({ scaleName, testOrder });
    });

    const requestData = {
      user_ids: [incoming_id, outcoming_id],
      mode: mode,
      isPractice: isPractice,
      surveys: surveys
    };

    xhr.open("POST", "../php/get-preSurvey.php", true);
    xhr.onload = () => {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        for (const user_id in response) {
          const userSurveys = response[user_id].surveys;
          const allSurveysCompleted = userSurveys.every(s => s.status === 'Data available');
          if (allSurveysCompleted) onSurveyCompleted(user_id, userSurveys[0].testOrder);
          userSurveys.forEach(survey => {
            if (survey.status === 'Data available') {
              if (outcoming_id === user_id) {
                toggleSurveyElementsVisibility(allSurveysCompleted, survey.scaleName);
              }
            }
          });
        }
      }
    };
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(`data=${encodeURIComponent(JSON.stringify(requestData))}`);
  }
}, 500);

// === WebSocket ===
function connectWebSocket() {
  conn = new WebSocket(`ws://${ipAddress}:8119`);

  conn.onopen = () => console.log("Connection established!");
  conn.onclose = () => {
    console.log("Connection closed, attempting to reconnect...");
    setTimeout(connectWebSocket, 1000);
  };
  conn.onerror = (error) => console.error("WebSocket error:", error);

  conn.onmessage = function (e) {
    const data = JSON.parse(e.data);

    if (data.type === 'scale') {
      user1SurveyCompleted = false;
      user2SurveyCompleted = false;
      totalSeconds = 6 * 60;
      taskDescription.style.fontSize = '50px';
      taskDescription.style.fontWeight = 'bold';
      taskDescription.style.textAlign = 'center';
      taskDescription.textContent = '06:00';
      clearInterval(timerInterval);
      timerInterval = setInterval(updateTimer, 1000);
      taskDescription.insertAdjacentHTML('afterend', `
        <div id="buttonContainer" style="position: absolute; bottom: 10px; right: 10px;">
          <button id="skipBreakButton" style="font-weight: bold; font-size: 10px;">跳過休息</button>
        </div>
      `);
      document.getElementById('skipBreakButton').addEventListener('click', function () {
        totalSeconds = 0;
        document.getElementById('skipBreakButton').style.display = 'none';
      });
      nextModeLink(data.msg.replace(/replaceUserId/g, outcoming_id));
      const nextLink = document.getElementById('nextModeLink');
      if (nextLink) nextLink.style.display = 'none';
      scrollToBottom();
      if (data.testOrder === 'final') {
        taskDescription.textContent = "";
        const skipBtn = document.getElementById('skipBreakButton');
        if (skipBtn) skipBtn.style.display = 'none';
        clearInterval(timerInterval);
      }
      return;
    }

    if (data.type === 'nextPage') {
      const nextLink = document.getElementById('nextModeLink');
      if (nextLink) nextLink.style.display = '';
      nextModeLink(data.msg.replace(/replaceUserId/g, outcoming_id));
      taskDescription.textContent = displayNextLinkText();
      taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
      return;
    }

    // GPT 文字情緒結果
    if (data.type === 'GPT_emo') {
      const isTargetPair =
        (data.incomingUserId === outcoming_id && data.outcomingUserId === incoming_id) ||
        (data.incomingUserId === incoming_id && data.outcomingUserId === outcoming_id);

      if (isTargetPair) {
        const msgEl = document.getElementById(data.msg_id);
        if (msgEl) {
          
          const emo = emotionsEmojiDict[data.emotion] || '';
          const leftEmoji = affectList.querySelector(
            `.affect-item[data-msg-id="${data.msg_id}"] .affect-emoji--manual`
          );
          const rightEmoji = affectList.querySelector(
            `.affect-item[data-msg-id="${data.msg_id}"] .affect-emoji--moodtag`
          );
          if (leftEmoji) leftEmoji.textContent = emo || '';
          if (rightEmoji) rightEmoji.textContent = emo || '';
          
        }
      }
      return;
    }

    // 蒐集 chat history（給 LLM 判斷使用）
    const chatHistory = [];
    document.querySelectorAll('.chat-box .chat').forEach(chat => {
      const isOutgoing = chat.classList.contains('outgoing');
      const sender = isOutgoing ? 'user' : 'friend';
      const messageContent = chat.querySelector('.details p')?.innerText || '';
      const time = chat.querySelector('.details .time')?.innerText || '';
      chatHistory.push({ sender, message: messageContent, time });
    });

    // 自己送出 → 觸發 GPT
    if (data.type === 'GPT' && data.from === 'Me') {
      sendRequest(data, chatHistory, incoming_id, outcoming_id, mode, isPractice);
      
    }

    // 時間
    const dateTime = new Date(data.time);
    const formattedTime = `${dateTime.getHours().toString().padStart(2, '0')}:${dateTime.getMinutes().toString().padStart(2, '0')}`;

    const hasAffectLabel = data.textEmotion !== null && data.textEmotion !== '';
    const ferEmotion = hasAffectLabel
      ? (emotionsEmojiDict[data.textEmotion] !== undefined ? emotionsEmojiDict[data.textEmotion] : '')
      : '';

    // 建立訊息 HTML
    let chat_html = "";

    if (data.from === 'Me' && data.incomingUserId === incoming_id && data.outcomingUserId === outcoming_id) {
      localStorage.setItem('userSentFirstMessage', 'true');
      chat_html = `
      <div id="${data.msg_id}" class="chat outgoing" data-msg-id="${data.msg_id}">
        <div class="details">
          <span class="time">${formattedTime}</span>
          ${hasAffectLabel && mode === systemName ? `<div class="emoTag" style="font-size:25px;">${ferEmotion}</div>` : ``}
          <img class="smile" src="images/smile.png" alt="Smile">
          <p>${data.msg}</p>
        </div>
        <div class="affect labels" data-manual="" data-moodtag="" style="display:none">
          <span class="affect-label manual"></span>
          <span class="affect-label moodtag"></span>
        </div>
      </div>`;
    }
    else if (data.from === 'NotMe' && data.incomingUserId === outcoming_id && data.outcomingUserId === incoming_id) {
      localStorage.setItem('partnerSentFirstMessage', 'true');
      chat_html = `
        <div id="${data.msg_id}" class="chat incoming" data-msg-id="${data.msg_id}">
          <div class="details">
            <img class="profile" src="images/${data.img}" alt="">
            <p>${data.msg}</p>
            ${hasAffectLabel && mode === systemName ? `<div class="emoTag" style="font-size:25px;">${ferEmotion}</div>` : ``}
            <div class="read-status">
              <span class="read">已讀</span>
              <span class="time">${formattedTime}</span>
              <img class="smile" src="images/smile.png" alt="Smile">
            </div>
          </div>
          <div class="affect labels" data-manual="" data-moodtag="" style="display:none">
            <span class="affect-label manual"></span>
            <span class="affect-label moodtag"></span>
          </div>
        </div>`;
    } else {
      return; // 非本聊天室
    }

    const noMsg = document.getElementById('noMessages');
    if (noMsg) noMsg.style.display = 'none';

    // 加到左欄
    $('.chat-box').append(chat_html);

    // 右欄鏡射 + 對齊
    const newly = document.getElementById(data.msg_id);
    if (newly) { mirrorOneMessageToRight(newly); watchImages(newly); }

    // 綁定該則訊息的情緒互動
    if (mode !== 'Control' && mode !== 'neutral') {
      addSmileEvents([newly?.querySelector('.details')].filter(Boolean));
    }

    // 捲到底
    scrollToBottom();

    // 初次發話重載（你的原始邏輯）
    if (mode === 'MoodTag' && !localStorage.getItem('userSentFirstMessage') && !localStorage.getItem('partnerSentFirstMessage')) {
      localStorage.setItem('userSentFirstMessage', 'true');
      localStorage.setItem('partnerSentFirstMessage', 'true');
      location.reload();
    }
  };
}

// === Document Ready ===
$(document).ready(function () {
  updateTextContent(mode);

  if (mode === 'neutral') {
    localStorage.removeItem('hasReloaded');
    localStorage.removeItem('userSentFirstMessage');
    localStorage.removeItem('partnerSentFirstMessage');
  }

  const confirmBtn = document.getElementById('confirmButton');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      if (mode === 'MoodTag' && !localStorage.getItem('hasReloaded')) {
        localStorage.setItem('hasReloaded', 'true');
        location.reload();
      } else {
        handleConfirmAction();
      }
    });
  }

  if (mode === 'MoodTag' && localStorage.getItem('hasReloaded')) {
    handleConfirmAction();
    const confirmBtn2 = document.getElementById('confirmButton');
    if (confirmBtn2) confirmBtn2.click();
  }

  function handleConfirmAction() {
    const overlay = document.getElementById('overlay');
    if (overlay) overlay.style.display = 'none';
    scrollToBottom();
    const isGetUserMediaSupported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    if (isPractice === "0" && isRecording) {
      if (isGetUserMediaSupported) {
        startRecording(outcoming_id, mode);
      } else {
        alert('無法訪問您的攝影機。請檢查您的瀏覽器設置或使用支援的瀏覽器。');
      }
    }
  }

  $("#myTextarea").emojioneArea({ pickerPosition: "bottom" });

  const xhr = new XMLHttpRequest();
  if (mode === systemName) {
    xhr.open("POST", "../php/get-chatMoodTag.php", true);
    load(xhr);
  } else if (mode === selfAffect) {
    xhr.open("POST", "../php/get-chatSelfAffect.php", true);
    load(xhr);
  } else {
    xhr.open("POST", "../php/get-chat.php", true);
    load(xhr);
  }

  connectWebSocket();
});

// === 表單送出 ===
form.onsubmit = (e) => e.preventDefault();
sendBtn.onclick = () => {
  inputField = document.querySelector('.emojionearea-editor');
  const html = inputField ? inputField.innerHTML : '';

  const data = {
    incomingUserId: incoming_id,
    outcomingUserId: outcoming_id,
    msg: html,
    mode: mode,
    isPractice: isPractice,
    isMoodTag: 0,
    type: mode === systemName ? 'GPT' : 'Control',
    typeStartTime: isSendingDateTime
  };
  isSendingDateTime = 0;

  conn?.send(JSON.stringify(data));
  if (inputField) inputField.innerHTML = "";
};

// === hover/標記 選單 ===
function hideEmojiImages(event) {
  const emojiImages = document.querySelector('.emoji-images');
  if (emojiImages && !event.target.classList.contains('smile')) {
    emojiImages.remove();
    document.removeEventListener('click', hideEmojiImages);
  }
  const wrapper = document.querySelector('.wrapper');
  if (!wrapper) return;
  const computedStyle = window.getComputedStyle(wrapper).cursor.includes('images/pens.ico');
  if (event.target.id === 'modeIntroImg' && computedStyle) {
    wrapper.style.cursor = "auto";
    const modeEmoji = document.getElementById('modeEmoji');
    if (modeEmoji) {
      modeEmoji.querySelectorAll('img').forEach(img => { img.style.border = 'none'; });
    }
    chooseEmoji = false;
  }
}

function addSmileEvents(detailsElements) {
  detailsElements.forEach(details => {
    details.addEventListener('mouseover', () => {
      const smileElement = details.querySelector('.smile');
      const timeElement = details.querySelector('.time');
      if (smileElement && timeElement) {
        smileElement.style.display = 'block';
        timeElement.style.display = 'none';
        const hasLabel = (details.closest('.chat')?.querySelector(".affect-label")?.textContent || "") !== "";
        smileElement.style.backgroundColor = hasLabel ? 'gold' : '';
      }
      const wrapper = document.querySelector('.wrapper');
      const computedStyle = wrapper && window.getComputedStyle(wrapper).cursor.includes('images/pens.ico');
      if (computedStyle) details.querySelector('p')?.classList.add('highlight-border');
    });

    details.addEventListener('mouseout', () => {
      const smileElement = details.querySelector('.smile');
      const timeElement = details.querySelector('.time');
      if (smileElement && timeElement) {
        smileElement.style.display = 'none';
        timeElement.style.display = 'block';
      }
      const wrapper = document.querySelector('.wrapper');
      const computedStyle = wrapper && window.getComputedStyle(wrapper).cursor.includes('images/pens.ico');
      if (computedStyle) details.querySelector('p')?.classList.remove('highlight-border');
    });

    // 點擊聊天內容（非笑臉）→ 人工標記（右邊 5 個 GIF 那條）
    const msgParent = details.closest('.chat');
    details.addEventListener('click', () => {
      const chooseEmojiImg = document.querySelector('img.highlight-border');
      const wrapper = document.querySelector('.wrapper');
      const computedStyle = wrapper && window.getComputedStyle(wrapper).cursor.includes('images/pens.ico');
      if (!chooseEmojiImg || !computedStyle) return;

      const data = {
        incomingUserId: incoming_id,
        outcomingUserId: outcoming_id,
        msg_id: msgParent.getAttribute('id'),
        emotion: chooseEmojiImg.alt,
        mode: mode,
        isPractice: isPractice,
        type: 'humanAffect'
      };
      conn?.send(JSON.stringify(data));

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "../php/insert-textEmo.php", true);
      xhr.onload = () => {};
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.send(
        "emotion=" + encodeURIComponent(chooseEmojiImg.alt) +
        "&msg_id=" + encodeURIComponent(msgParent.getAttribute('id')) +
        "&user_id=" + encodeURIComponent(incoming_id) +
        "&oldEmo=" + "" +
        "&mode=" + encodeURIComponent(mode) +
        "&source=" + "" +
        "&labeler_user_id=" + encodeURIComponent(outcoming_id)
      );

      const modeEmoji = document.getElementById('modeEmoji');
      if (modeEmoji) modeEmoji.querySelectorAll('img').forEach(img => img.classList.remove('highlight-border'));
      if (wrapper) wrapper.style.cursor = "auto";
      chooseEmoji = false;

      const labelEl = details.parentElement.querySelector('.affect-label');
      labelEl.textContent = emotionsEmojiDict[chooseEmojiImg.alt] || '';

      const li2 = affectList.querySelector(`.affect-item[data-msg-id="${msgParent.id}"] .affect-emoji`);
      if (li2) li2.textContent = emotionsEmojiDict[chooseEmojiImg.alt] || '';
    });
  });

  detailsElements.forEach(details => {
    const smile = details.querySelector('.smile');
    if (!smile) return;

    smile.addEventListener('click', () => {
      const exists = document.querySelector('.emoji-images');
      if (exists) exists.remove();
      document.removeEventListener('click', hideEmojiImages);

      const emojiImages = document.createElement('div');
      emojiImages.className = 'emoji-images';

      const msgParent = smile.closest('.chat');
      const currentEmoji = msgParent.querySelector(".affect-label")?.textContent || "";

      for (const emotion in emotionsEmojiDict) {
        const emoji = emotionsEmojiDict[emotion];
        const hex = emojiToHexCodePoint(emoji);
        const img = document.createElement('img');
        img.src = `https://fonts.gstatic.com/s/e/notoemoji/latest/${hex}/512.gif`;
        img.alt = emoji;
        img.width = 34; img.height = 34;
        img.style.margin = '8px';

        if (currentEmoji === emoji) {
          img.width = 50; img.height = 50; img.style.margin = '0px';
        }

        img.addEventListener('click', () => {
          const oldEmo = Object.entries(emotionsEmojiDict).find(([, e]) => e === currentEmoji)?.[0];
          const affectLabel = msgParent.querySelector(".affect-label");
          const newEmoji = (oldEmo === emotion) ? "" : emoji;
          affectLabel.textContent = newEmoji;

          const xhr = new XMLHttpRequest();
          xhr.open("POST", "../php/insert-textEmo.php", true);
          xhr.onload = () => {};
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhr.send(
            "emotion=" + encodeURIComponent(emotion) +
            "&msg_id=" + encodeURIComponent(msgParent.getAttribute('id')) +
            "&user_id=" + encodeURIComponent(outcoming_id) +
            "&oldEmo=" + encodeURIComponent(oldEmo || "")
          );

          const rightEmoji = affectList.querySelector(`.affect-item[data-msg-id="${msgParent.id}"] .affect-emoji`);
          if (rightEmoji) rightEmoji.textContent = newEmoji || '';

          lastAffectTime = new Date();
        });

        img.addEventListener('mouseenter', () => {
          if (currentEmoji) {
            const selected = emojiImages.querySelector(`img[alt="${currentEmoji}"]`);
            if (selected) {
              selected.style.width = "34px"; selected.style.height = "34px"; selected.style.margin = "8px";
            }
          }
          img.style.width = '50px'; img.style.height = '50px'; img.style.margin = '0px';
        });

        img.addEventListener('mouseleave', () => {
          img.style.width = '34px'; img.style.height = '34px'; img.style.margin = '8px';
          if (currentEmoji) {
            const selected = emojiImages.querySelector(`img[alt="${currentEmoji}"]`);
            if (selected) {
              selected.style.width = "50px"; selected.style.height = "50px"; selected.style.margin = "0px";
            }
          }
        });

        emojiImages.appendChild(img);
      }

      const rect = smile.getBoundingClientRect();
      emojiImages.style.position = 'absolute';
      emojiImages.style.left = `${rect.left}px`;
      emojiImages.style.top = `${rect.top - 60}px`;
      emojiImages.style.zIndex = 9999;
      document.body.appendChild(emojiImages);
      document.addEventListener('click', hideEmojiImages);
    });
  });
}

// === Mode Intro / Survey 區塊 ===
function updateTextContent(mode) {
  const modeIntroText = document.getElementById('modeIntroText');
  const modeIntroImg = document.getElementById('modeIntroImg');
  const taskTitle = document.getElementById('taskTitle');
  const labelGif = document.getElementById('labelGif');
  let topic = "";

  const modeEmoji = document.getElementById('modeEmoji');
  if (modeEmoji) modeEmoji.style.display = 'none';
  if (modeIntroImg) {
    modeIntroImg.setAttribute('src', mode === 'MoodTag' ? "images/robot.svg" : "images/pens.png");
    modeIntroImg.style.display = 'none';
  }
  taskDescription.style.fontSize = '20px';
  const confirmButton = document.getElementById('confirmButton');
  if (confirmButton) confirmButton.style.display = '';

  if (isPractice === '0' && mode !== 'neutral') {
    const surveyURL = `http://${ipAddress}/ChatApp/survey/PANAS.html?user=${outcoming_id}&mode=${mode}&testOrder=pre`;
    const perSurvey = document.getElementById('perSurvey');
    if (perSurvey) {
      perSurvey.innerHTML = `
        <div class="survey" style="text-align: center; margin-top: 40px;">
          <h3>請先填寫此問卷，點擊下方連結⬇️⬇️⬇️</h3>
          <a href="${surveyURL}" target="_blank" style="font-size: 26px;">PANAS 正面和負面情緒量表</a></br></br>
        </div>`;
    }
    if (confirmButton) confirmButton.style.display = 'none';
  }

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "../php/get-Topic.php", true);
  xhr.onload = () => {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      topic = xhr.response;
      switch (mode) {
        case 'MoodTag':
          if (isPractice === '1') {
            const modalHeader = document.querySelector('.modal-header');
            if (modalHeader) modalHeader.innerHTML = '<h1>如何使用 MoodTag 標記情緒：</h1>';
            taskDescription.textContent =
`<b><h3>接下來的任務：</h3>隨意與對方傳送訊息，並試著修改或加上情緒標籤。</b>

<b style="color: #ff6600;"><h3>重點：</h3>Step 1：MoodTag 會自動辨識訊息的情緒，如果你不認同 MoodTag 的標記，你可以修改或刪除標記
    Step 2：對於 MoodTag 沒有辨識到的訊息，你也可以手動加上情緒標籤 </b>

<b style="text-align: center; display: block; color: #FF0000;">更改情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            if (labelGif) {
              labelGif.style = "border: 3px solid #FF0000";
              labelGif.src = "images/demo/labelChange.gif";
            }
            break;
          }
          if (modeIntroText) {
            modeIntroText.textContent = "MoodTag 會即時分析你的文字，並為你標示出潛在的情緒，幫助你更好地理解自己的感受。如果有問題請隨時告訴實驗人員！";
            modeIntroText.classList.add('mode-intro-text-M');
          }
          if (modeIntroImg) modeIntroImg.classList.add('mode-intro-img-M');
          if (taskTitle) taskTitle.innerText = 'MoodTag標記';
          if (labelGif) labelGif.src = "images/demo/labelChange.gif";
          if (modeIntroImg) modeIntroImg.style.display = 'block';
          taskDescription.textContent =
`<b>接下來的任務：需與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
<h3 style="color: #001eff;">➡️話題：${topic}⬅️</h3>
<b style="color: #ff6600;"><h3>重點：</h3>Step 1：MoodTag 會自動辨識訊息的情緒，如果你不認同 MoodTag 的標記，你可以修改或刪除標記
    Step 2：對於 MoodTag 沒有辨識到的訊息，你也可以手動加上情緒標籤 </b>
<b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束。</b>
<b style="text-align: center; display: block; color: #FF0000;">更改情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
          taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
          if (labelGif) labelGif.style = "border: 3px solid #FF0000";
          break;

        case 'Manual':
          if (isPractice === '1') {
            const modalHeader2 = document.querySelector('.modal-header');
            if (modalHeader2) modalHeader2.innerHTML = '<h1>如何自己標記情緒：</h1>';
            taskDescription.textContent =
`<b>接下來的任務：隨意與對方傳送訊息，並試著在訊息加上情緒標籤</b>
<b style="color: #ff6600;">重點：
   Step 1：自己傳訊息時所，若感受到負面情緒，將感受到的情緒標記下來
   Step 2：對方傳來的訊息若激發到自己的情緒也標記下來
</b>
<b style="text-align: center; display: block; color: #FF0000;">情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
            taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
            if (labelGif) {
              labelGif.style = "border: 3px solid #FF0000";
              labelGif.src = "images/demo/labelDone.gif";
            }
            break;
          }
          if (modeIntroText) {
            modeIntroText.textContent = "請自己透過標記當下情緒來調節情緒。如果有問題請隨時告訴實驗人員！";
            modeIntroText.classList.add('mode-intro-text-P');
          }
          if (modeIntroImg) modeIntroImg.classList.add('mode-intro-img-P');
          if (taskTitle) taskTitle.innerText = '自我標記';
          if (labelGif) labelGif.src = "images/demo/labelDone.gif";
          if (modeIntroImg) modeIntroImg.style.display = 'block';
          taskDescription.textContent =
`<b>接下來的任務：與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
<h3 style="color: #001eff;">➡️話題：${topic}⬅️</h3>
<b style="color: #ff6600;"><h3>重點</h3>
  Step 1：自己傳訊息時所，若感受到負面情緒，將感受到的情緒標記下來
  Step 2：對方傳來的訊息若激發到自己的情緒也標記下來
</b>
<b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束。</b>
<b style="text-align: center; display: block; color: #FF0000;">情緒標籤操作方式如下⬇️⬇️⬇️</b>`;
          taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
          if (labelGif) labelGif.style = "border: 3px solid #FF0000";
          break;

        case 'Control':
          if (taskTitle) taskTitle.innerText = '一般模式';
          taskDescription.textContent =
`<b>接下來的任務：在該階段中，需與對方進行文字聊天，針對你們選擇的話題進行抒發，讓心情回到當下狀態。</b>
<h3 style="color: #001eff;">➡️話題：${topic}⬅️</h3>
<b>過程中除非特殊問題，否則不會中斷實驗，10 分鐘後實驗人員會提醒你們對話結束</b>`;
          taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
          if (labelGif) labelGif.src = "";
          break;

        case 'neutral':
          if (taskTitle) taskTitle.innerText = '平靜模式';
          taskDescription.textContent =
`<b><h3>接下來的任務：聊聊實驗前在做甚麼，以恢復到平靜情緒</h3></b>
<b>聊天時間3分鐘，時間到會由實驗人員提醒並填答問卷</b>`;
          taskDescription.innerHTML = taskDescription.textContent.replace(/\n/g, '<br>');
          if (labelGif) labelGif.src = "";
          break;
      }
    }
  };
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("user_id=" + encodeURIComponent(incoming_id) + "&mode=" + encodeURIComponent(mode));
}

// === Chatbox active 樣式 ===
chatBox.onmouseenter = () => chatBox.classList.add("active");
chatBox.onmouseleave = () => chatBox.classList.remove("active");
