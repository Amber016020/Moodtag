# 📂 系統架構
本專案為 ChatApp，採用多語言與多層次架構，整合前端、後端、資料庫及 AI 模型模組。
以下為主要目錄與檔案說明：

ChatApp/
├── bat/                     # 批次腳本（Batch scripts）
├── css/                     # 前端樣式表（CSS）
├── font/                    # 字型檔案
├── javascript/              # 前端 JavaScript 程式碼
├── lang/                    # 語言檔（多語系支援）
├── models/                  # 機器學習或 AI 模型檔案
├── php/                     # PHP 後端功能模組
├── python/                  # Python 腳本與服務
├── sql/                     # 資料庫 SQL 腳本
├── src/                     # 核心原始碼（可能為主要應用邏輯）
├── survey/                  # 問卷或使用者調查相關程式
├── vendor/                  # Composer 相依套件
├── .gitignore               # Git 忽略規則
├── composer.json            # Composer 套件設定
├── composer.lock            # Composer 套件版本鎖定檔
├── emotion_data.json        # 情緒資料 JSON
├── login.php                # 使用者登入頁面
├── package.json             # Node.js 套件設定
├── package-lock.json        # Node.js 套件版本鎖定檔
├── shuffledImages.json      # 亂序圖片資料 JSON
├── signup.php               # 使用者註冊頁面
└── users.php                # 使用者資料管理

🔹 前端 (Frontend)
css/、font/、javascript/：負責 UI 與互動功能。

lang/：多語言文字檔，支援國際化。

🔹 後端 (Backend)
php/、login.php、signup.php、users.php：處理使用者帳號、資料存取與系統邏輯。

vendor/：使用 Composer 管理的 PHP 套件。

🔹 AI 與資料處理
models/：儲存 AI/ML 模型。

python/：AI 推論、資料分析等 Python 腳本。

emotion_data.json：情緒相關資料。

🔹 資料庫
sql/：資料庫結構與初始化腳本。

🔹 其他
bat/：自動化批次指令。

survey/：問卷與研究數據收集。

shuffledImages.json：隨機圖片資料設定。

