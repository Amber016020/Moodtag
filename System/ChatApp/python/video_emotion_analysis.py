import cv2
from keras.models import model_from_json
import numpy as np
import matplotlib.pyplot as plt
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

# 開啟影片檔案
video = cv2.VideoCapture("movie/recording-1575832032-normal-20240721-211825.webm")

# 定義情緒標籤
labels = {0: 'angry', 1: 'disgust', 2: 'fear', 3: 'happy', 4: 'neutral', 5: 'sad', 6: 'surprise'}

# 儲存情緒結果
emotion_results = []
timestamps = []

while video.isOpened():
    ret, frame = video.read()
    if not ret:
        break
    # 取得當前時間戳
    timestamp = video.get(cv2.CAP_PROP_POS_MSEC) / 1000.0
    # 將畫面轉換為灰度
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    # 檢測人臉
    faces = face_cascade.detectMultiScale(gray, 1.3, 5)
    try:
        for (p, q, r, s) in faces:
            # 提取人臉區域
            image = gray[q:q + s, p:p + r]
            cv2.rectangle(frame, (p, q), (p + r, q + s), (255, 0, 0), 2)
            image = cv2.resize(image, (48, 48))
            img = extract_features(image)
            pred = model.predict(img)
            prediction_label = labels[pred.argmax()]
            cv2.putText(frame, prediction_label, (p - 10, q - 10), cv2.FONT_HERSHEY_COMPLEX_SMALL, 2, (0, 0, 255))

            # 儲存情緒結果和時間戳
            emotion_results.append(prediction_label)
            timestamps.append(timestamp)

    except cv2.error:
        pass
    
    cv2.imshow("Output", frame)
    if cv2.waitKey(1) == 27:
        break

# 釋放資源
video.release()
cv2.destroyAllWindows()

# 繪製情緒變化圖表
plt.figure(figsize=(10, 5))
plt.plot(timestamps, emotion_results, marker='o', linestyle='-')
plt.xlabel('Time (s)')
plt.ylabel('Emotion')
plt.title('Emotion Changes Over Time')
plt.xticks(rotation=45)
plt.grid(True)
plt.show()
