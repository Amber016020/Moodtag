import pandas as pd
import numpy as np
import glob
import os

# 將 frame 時間正規化到 0-600 秒（假設 10 分鐘，每秒一個 frame）
def resample_user_data(df, num_frames=600):
    df['normalized_time'] = np.linspace(0, 600, num=len(df))  # 正規化到 600 秒
    return df

# 設定 CSV 檔案的資料夾路徑
folder_path = r'E:\\Research\\part\\data\\group3'

# 使用 glob 來匹配資料夾下所有 CSV 檔案
csv_files = glob.glob(os.path.join(folder_path, '*-control*.csv')) + \
            glob.glob(os.path.join(folder_path, '*-moodtag*.csv')) + \
            glob.glob(os.path.join(folder_path, '*-self-affect*.csv'))

# 根據檔名中的某部分進行排序
csv_files.sort(key=lambda x: os.path.basename(x))  # 根據檔名的第一部分排序

# 建立一個空的 list 來儲存每個 CSV 的 DataFrame
dfs = []

# 讀取每個 CSV 檔案並添加到 dfs 列表中
for file in csv_files:
    print(os.path.basename(file))
    # 提取 user_id 和 mode
    filename = os.path.basename(file)
    user_id = filename.split('-')[1]  # 假設檔名的第一部分為 user_id
    mode = filename.split('-')[2]  # 假設檔名的第二部分為模式 (control/moodtag/self-affect)
    
    # 讀取 CSV
    df = pd.read_csv(file)
    
    # 新增 user_id 和 mode 欄位
    df['user_id'] = user_id
    df['mode'] = mode
    
    # 添加到列表
    dfs.append(df)

# 合併所有的 DataFrame
combined_df = pd.concat(dfs, ignore_index=True)

# 根據情緒欄位去重（假設情緒欄位為 Happiness, Sadness, Surprise, Fear, Disgust, Anger）
emotion_columns = ['Neutral', 'Happiness', 'Sadness', 'Surprise', 'Fear', 'Disgust', 'Anger']

# 確保去重只針對相同 user_id 和 mode 的連續重複情緒
combined_df = combined_df.drop_duplicates(subset=['user_id', 'mode'] + emotion_columns)

# 對每個 user_id 和 mode 進行重抽樣
combined_df = combined_df.groupby(['user_id', 'mode']).apply(resample_user_data).reset_index(drop=True)

# 根據 user_id 和 mode 重新計算 frame
combined_df['frame'] = combined_df.groupby(['user_id', 'mode']).cumcount() + 1

# 設定儲存 CSV 檔案的路徑
output_folder_path = r'E:\\Research\\part\\data\\face'
output_file_path = os.path.join(output_folder_path, 'face_data1.csv')

# 確保儲存資料夾存在
os.makedirs(output_folder_path, exist_ok=True)

# 將整理好的 DataFrame 儲存成 CSV
combined_df.to_csv(output_file_path, index=False)  # index=False 確保不存儲索引欄位
