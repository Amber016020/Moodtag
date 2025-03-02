import { ipAddress } from './config.js';

export async function startRecording(userid, mode) {
    try {
        // 檢查瀏覽器是否支援 getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('您的瀏覽器不支援攝影機訪問。');
        }

        // 取得使用者的媒體裝置（攝影機）
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });

        // 定義一個函數來創建 MediaRecorder 並開始錄影
        const createAndStartMediaRecorder = () => {
            const mediaRecorder = new MediaRecorder(stream);
            const chunks = [];

            // 當有新的錄影數據時，將其儲存到 chunks 陣列
            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    chunks.push(event.data);
                }
            };

            // 當錄影停止時，將錄影數據轉換成 blob 並發送到伺服器
            mediaRecorder.onstop = async () => {
                const blob = new Blob(chunks, { type: 'video/webm' });
                const formData = new FormData();
                const timestamp = generateTimestamp();
                formData.append('video', blob, `recording-${userid}-${mode}-${timestamp}.webm`); // 使用時間戳生成唯一的文件名

                try {
                    const response = await fetch(`http://${ipAddress}:3000/upload`, {
                        method: 'POST',
                        body: formData
                    });
                    if (response.ok) {
                        console.log('錄影檔案已成功上傳');
                    } else {
                        console.error('上傳失敗');
                    }
                } catch (error) {
                    console.error('上傳過程中出錯', error);
                }
            };

            mediaRecorder.start();
            return mediaRecorder;
        };

        // 生成當前時間的時間戳
        const generateTimestamp = () => {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0'); // 月份從0開始，+1
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `${year}${month}${day}-${hours}${minutes}${seconds}`;
        };

        // 每60秒重新創建一個 MediaRecorder 並開始新的錄影
        let mediaRecorder = createAndStartMediaRecorder();
        const interval = 60000; // 60秒

        const startNewRecording = () => {
            mediaRecorder.stop(); // 停止當前的錄影
            setTimeout(() => {
                mediaRecorder = createAndStartMediaRecorder(); // 創建並開始新的錄影
            }, 1000); // 1秒後重新開始
        };

        // 設置錄影間隔，每60秒執行一次
        const recordingInterval = setInterval(startNewRecording, interval);

        // 在需要的時候清除間隔，這裡假設錄影10分鐘
        setTimeout(() => {
            clearInterval(recordingInterval);
            mediaRecorder.stop();
        }, 600000); // 10分鐘後停止錄影

    } catch (error) {
        console.error('Error accessing media devices.', error);
    }
}
