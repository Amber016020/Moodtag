import os
import pandas as pd
import matplotlib.pyplot as plt
from matplotlib.font_manager import FontProperties
from ckiptagger import WS, POS, NER
import numpy as np
import re

# 定義清理訊息的函數
def clean_message(text):
    text = re.sub(r'[^\w\s]', '', text)  # 移除標點符號
    text = re.sub(r'\d+', '', text)  # 移除數字（根據需求，也可以保留）
    return text.strip()  # 移除首尾的空格

# 將含有 '上午' 和 '下午' 的時間轉換成 24 小時制
def convert_to_24h_format(time_str):
    # 檢查是否是下午並進行相應轉換
    if '下午' in time_str:
        time_str = time_str.replace('下午', '').strip()
        # 將時間轉換成 24 小時制
        hour, minute, second = map(int, time_str.split(':'))
        if hour != 12:
            hour += 12
        return f"{hour:02}:{minute:02}:{second:02}"
    elif '上午' in time_str:
        time_str = time_str.replace('上午', '').strip()
        # 12:xx:xx AM 應該轉換為 00:xx:xx
        hour, minute, second = map(int, time_str.split(':'))
        if hour == 12:
            hour = 0
        return f"{hour:02}:{minute:02}:{second:02}"
    return time_str

# 打開並讀取正負面詞彙檔案
with open('python/data/ntusd-negative.txt', 'r', encoding='utf-8') as file:
    negative_word_list = [line.strip() for line in file]

with open('python/data/ntusd-positive.txt', 'r', encoding='utf-8') as file:
    positive_word_list = [line.strip() for line in file]

# 設定 GPU
os.environ["CUDA_VISIBLE_DEVICES"] = "0"

# 加載模型並設定 GPU
ws = WS("python/data", disable_cuda=True)

# 讀取 CSV 文件
df = pd.read_csv('E:/Research/part/data/messages.csv', encoding='utf-8')

# 應用到 msg_time 列
df['msg_time'] = df['msg_time'].apply(convert_to_24h_format)

# 再次將 msg_time 列轉換成 datetime
df['msg_time'] = pd.to_datetime(df['msg_time'], format='%H:%M:%S')

# 對每條訊息進行清洗
df['cleaned_msg'] = df['msg'].apply(clean_message)

# 移除重複空格
df['cleaned_msg'] = df['cleaned_msg'].apply(lambda x: ' '.join(x.split()))

# 假設有一個停用詞列表
stop_words = set(['的', '是', '了', '在', '和', '我', '有', '也', '就', '不', '人', '都', '很'])

# 定義去除停用詞的函數
def remove_stopwords(sentence):
    return ' '.join([word for word in sentence.split() if word not in stop_words])

# 去除停用詞
df['cleaned_msg'] = df['cleaned_msg'].apply(remove_stopwords)

# 設定中文字體
font = FontProperties(fname='C:/Windows/Fonts/msjh.ttc')  # 使用微軟正黑體，請確保路徑正確

# 新增列以儲存切割結果、正面詞彙、負面詞彙
df['positive_sentiment_score'] = 0
df['negative_sentiment_score'] = 0
df['time_deltas'] = 0

# 按 pattern 分組
pattern_groups = df.groupby('pattern')

# 對每個 pattern 單獨處理
for pattern, pattern_df in pattern_groups:
    # 按 userID 和 userName 分組
    grouped = pattern_df.groupby(['userID', 'userName'])

    for (user_id, user_name), group in grouped:
        sentence_list = group['cleaned_msg'].tolist()  # 取得當前用戶的所有消息

        # 進行斷詞、詞性標註與命名實體識別
        word_sentence_list = ws(sentence_list)

        # 計算該用戶每句話的情緒分數
        for idx, (msg_time, words) in enumerate(zip(group['msg_time'], word_sentence_list)):
            # 計算正面和負面情緒分數
            pos_score = sum(1 for word in words if word in positive_word_list)
            neg_score = sum(1 for word in words if word in negative_word_list)

            # 獲取單詞數量
            total_words = len(words)  # 所有單詞的總數

            # 計算平均情緒分數（防止除以零的情況）
            avg_pos_score = pos_score / total_words if total_words > 0 else 0
            avg_neg_score = neg_score / total_words if total_words > 0 else 0


            # 計算時間差，結果是 timedelta 類型
            time_delta = msg_time - group['msg_time'].min()

            # 取得時間差的總秒數
            time_difference_in_seconds = time_delta.total_seconds()

            # 使用 str() 將 time_difference_in_seconds 轉換為字符串
            print(str(msg_time) + ' - ' + str(group['msg_time'].min()) + ' = ' + str(time_difference_in_seconds) + ' seconds')

            # 更新 DataFrame 中對應行的情緒分數和時間差
            df.loc[group.index[idx], 'positive_sentiment_score'] = pos_score
            df.loc[group.index[idx], 'negative_sentiment_score'] = neg_score
            df.loc[group.index[idx], 'time_deltas'] = time_difference_in_seconds


# 將結果存儲到新的 CSV 檔案，使用 `utf-8-sig` 來避免中文亂碼
df.to_csv('E:/Research/part/data/messagesSA.csv', index=False, encoding='utf-8-sig')
