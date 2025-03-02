import cv2
from keras.models import model_from_json
import numpy as np
import socket

import warnings

# 忽略特定警告
warnings.filterwarnings("ignore", category=UserWarning, module="sklearn")


# 讀取已經訓練好的模型的 JSON 檔案
json_file = open("models/facialemotionmodel.json", "r")
model_json = json_file.read()
json_file.close()
# 載入模型結構
model = model_from_json(model_json)

# 載入模型權重
model.load_weights("models/facialemotionmodel.h5")

# 載入 OpenCV 的預訓練人臉檢測器
haar_file = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
face_cascade = cv2.CascadeClassifier(haar_file)

# 定義一個函數來提取圖像的特徵
def extract_features(image):
    feature = np.array(image)
    feature = feature.reshape(1, 48, 48, 1)
    return feature / 255.0

# 開啟網路攝影機
webcam = cv2.VideoCapture(0)

# 定義情緒標籤
labels = {0: 'angry', 1: 'disgust', 2: 'fear', 3: 'happy', 4: 'neutral', 5: 'sad', 6: 'surprise'}

while True:
    # 讀取攝影機捕獲的畫面
    i, im = webcam.read()
    # 將畫面轉換為灰度
    gray = cv2.cvtColor(im, cv2.COLOR_BGR2GRAY)
    # 檢測人臉
    faces = face_cascade.detectMultiScale(im, 1.3, 5)
    try:
        # 遍歷每個偵測到的人臉
        for (p, q, r, s) in faces:
            # 提取人臉區域
            image = gray[q:q + s, p:p + r]
            # 在原始畫面上繪製矩形框
            cv2.rectangle(im, (p, q), (p + r, q + s), (255, 0, 0), 2)
            # 調整人臉區域大小
            image = cv2.resize(image, (48, 48))
            # 提取特徵
            img = extract_features(image)
            # 使用模型進行情緒預測
            pred = model.predict(img)
            # 獲取預測結果的標籤
            prediction_label = labels[pred.argmax()]
            # 在畫面上添加情緒標籤
            cv2.putText(im, '% s' % (prediction_label), (p - 10, q - 10), cv2.FONT_HERSHEY_COMPLEX_SMALL, 2, (0, 0, 255))

            # 输出情绪标签到控制台
            print(prediction_label)
        # 顯示結果
        cv2.imshow("Output", im)
        # 按下 ESC 鍵退出程式
        if cv2.waitKey(1) == 27:
            break
    except cv2.error:
        pass
# 釋放攝影機資源
webcam.release()
# 關閉所有 OpenCV 視窗
cv2.destroyAllWindows()
