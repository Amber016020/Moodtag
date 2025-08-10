#!/usr/bin/env bash
set -euo pipefail

# === 可調參數 ===
XAMPP_VER="8.2.12-0"
XAMPP_RUN="xampp-linux-x64-${XAMPP_VER}-installer.run"
XAMPP_URL="https://sourceforge.net/projects/xampp/files/XAMPP%20Linux/8.2.12/${XAMPP_RUN}/download"
REPO_URL="https://github.com/Amber016020/Moodtag.git"
SITE_DIR="/opt/lampp/htdocs/Moodtag"
SQL_FILE="chatapp20241004_1.sql"      # 你的 .sql 檔名，放在 repo 裡或改路徑
DB_NAME="chatapp"
OPENAI_KEY="sk-proj-REPLACE_ME"       # 之後改

log() { echo -e "\n\033[1;32m==> $*\033[0m"; }

# 1) 基本套件
log "更新 apt 並安裝基本工具..."
sudo apt-get update -y
sudo apt-get install -y git curl nano

# 2) 安裝 XAMPP
if [ ! -x "/opt/lampp/lampp" ]; then
  log "下載 XAMPP 安裝程式..."
  curl -L "${XAMPP_URL}" -o "/tmp/${XAMPP_RUN}"
  chmod +x "/tmp/${XAMPP_RUN}"
  log "安裝 XAMPP..."
  sudo "/tmp/${XAMPP_RUN}" --mode unattended
else
  log "XAMPP 已安裝，略過。"
fi

# 3) 調整 phpMyAdmin 權限
XAMPP_CONF="/opt/lampp/etc/extra/httpd-xampp.conf"
if ! grep -q 'Require all granted' "$XAMPP_CONF"; then
  log "修改 phpMyAdmin 設定，允許外部存取（僅測試用，請注意安全）..."
  sudo sed -i 's#<Directory "/opt/lampp/phpmyadmin">#<Directory "/opt/lampp/phpmyadmin">\n    Require all granted#g' "$XAMPP_CONF"
fi

# 4) 啟動 XAMPP
log "啟動 XAMPP..."
sudo /opt/lampp/lampp start

# 5) 佈署專案
if [ ! -d "$SITE_DIR" ]; then
  log "Clone 專案到 ${SITE_DIR}..."
  sudo git clone "$REPO_URL" "$SITE_DIR"
else
  log "專案已存在，拉取最新..."
  cd "$SITE_DIR"
  sudo git pull --rebase || true
fi

# 6) 建 .env（後端讀用）
if [ ! -f "${SITE_DIR}/System/ChatApp/php/.env" ]; then
  log "建立 .env..."
  sudo bash -c "cat > ${SITE_DIR}/System/ChatApp/php/.env" <<EOF
OPENAI_API_KEY=${OPENAI_KEY}
EOF
fi

# 7) 建立資料庫 & 匯入
log "建立資料庫 ${DB_NAME}（若不存在）..."
/opt/lampp/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ -f "${SITE_DIR}/System/ChatApp/sql/${SQL_FILE}" ]; then
  log "匯入資料 ${SQL_FILE}..."
  /opt/lampp/bin/mysql -u root "${DB_NAME}" < "${SITE_DIR}/System/ChatApp/sql/${SQL_FILE}"
else
  log "找不到 SQL 檔：${SITE_DIR}/System/ChatApp/sql/${SQL_FILE}，略過匯入。"
fi

log "全部完成！你可以打開： http://<你的IP>/Moodtag/System/ChatApp/login.php"
