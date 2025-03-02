const fs = require('fs');
const path = require('path');

const imageDirectory = path.join(__dirname, '..', 'picture', 'emotionImage', 'normal');

// 讀取目錄中的所有文件
fs.readdir(imageDirectory, (err, files) => {
    if (err) {
        return console.error('Unable to scan directory: ' + err);
    }

    // 過濾出圖片文件（假設圖片文件有常見的擴展名，如 .jpg, .png, .gif 等）
    const imageFiles = files.filter(file => {
        return ['.bmp','.jpg', '.jpeg', '.png', '.gif'].includes(path.extname(file).toLowerCase());
    });

    // 打亂圖片文件的順序
    const shuffledImages = shuffleArray(imageFiles);

    // 將結果保存為JSON文件
    fs.writeFile('shuffledImages.json', JSON.stringify(shuffledImages), (err) => {
        if (err) throw err;
        console.log('Shuffled images saved to shuffledImages.json');
    });

    console.log(shuffledImages);
});

// 打亂數組順序的函數
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}
