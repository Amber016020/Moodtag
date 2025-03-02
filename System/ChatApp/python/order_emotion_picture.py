import pandas as pd
import json  # 確保導入 json 模塊
import math

# 讀取txt檔案到DataFrame
df = pd.read_csv('E:\MeetingRecord\情緒刺激圖片\Emotion6\ground_truth.txt', delimiter='\t')

folder_map = {0: "SM", 1: "SP", 2: "OM", 3: "OP", 4: "C"}

folderIndex = 0

# 根據該情緒的概率來篩選數據並按喚醒程度排序
filtered_df = df.sort_values(by='[arousal]', ascending=False)
filtered_df['folder'] = None
filtered_df['order'] = None
# 設置valence與arousal的條件
filtered_df = filtered_df[~(filtered_df['[valence]'] > 3.5)]
filtered_df = filtered_df[~(filtered_df['[arousal]'] < 6)]

for idx, row in filtered_df.iterrows():
    filtered_df.at[idx, 'folder'] = folder_map.get(folderIndex % 5, None)
    filtered_df.at[idx, 'order'] = math.floor(folderIndex / 5)
    folderIndex+=1


# Save the filtered DataFrame as a CSV file
filtered_df.to_csv('D:\\xampp\\htdocs\\ChatApp\\picture\\emotionImage\\filtered_data.csv', index=False)

# 提取image_filename和folder列
data_to_export = filtered_df[['[image_filename]', 'folder']]

# 將DataFrame轉換為字典列表
data_dict = data_to_export.to_dict(orient='records')

# 將字典列表轉換為JSON格式
json_data = json.dumps(data_dict, indent=4)

# 將JSON數據寫入文件
with open('D:\\xampp\\htdocs\\ChatApp\\picture\\emotionImage\\emotion_data.json', 'w') as json_file:
    json_file.write(json_data)

print(filtered_df)