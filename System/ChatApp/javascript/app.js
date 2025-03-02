const express = require('express');
const multer = require('multer');
const cors = require('cors');
const path = require('path');
const fs = require('fs'); // 引入 fs 模塊

const app = express();
const port = 3000;
import { ipAddress } from './config.js';

// 使用 cors 中間件
app.use(cors());

// 設置 multer 用於處理文件上傳
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const uploadDir = path.join(__dirname, '../movie'); // 使用 path.join 確保跨平台兼容
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true }); // 創建目錄（如果不存在）
        }
        cb(null, uploadDir);
    },
    filename: (req, file, cb) => {
        cb(null, file.originalname); // 保持文件原名稱
    }
});

const upload = multer({ storage: storage });

app.use(express.static(path.join(__dirname, 'public'))); // 提供靜態文件

app.post('/upload', upload.single('video'), (req, res) => {
    res.sendStatus(200); // 上傳成功
});

// 錯誤處理中間件
app.use((err, req, res, next) => {
    console.error(err.stack); // 打印錯誤堆疊
    res.status(500).send('伺服器出錯');
});

app.listen(port, () => {
    console.log(`伺服器運行在 http://${ipAddress}:${port}`);
});
