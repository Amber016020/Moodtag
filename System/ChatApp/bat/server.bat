@echo off
REM 切換到指定目錄
cd /d D:\xampp\htdocs\ChatApp

REM 在新視窗中啟動 PHP 服務器
start cmd /k "php php\server.php"

REM 在新視窗中啟動 Node.js 應用
start cmd /k "node -r esm javascript/app.js"