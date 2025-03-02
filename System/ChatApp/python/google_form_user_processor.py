import pandas as pd

# 讀取原始CSV檔
input_file = 'E:\Research\part\data\\base.csv'
# df = pd.read_csv(input_file)

# 嘗試使用不同的編碼來讀取CSV檔
encodings = ['utf-8', 'gbk', 'big5']

for encoding in encodings:
    try:
        df = pd.read_csv(input_file, encoding=encoding)
        print(f'Successfully read the file with encoding: {encoding}')
        break
    except UnicodeDecodeError as e:
        print(f'Failed to read the file with encoding {encoding}: {e}')

# 創建一個空的DataFrame來儲存轉換後的數據
new_df = pd.DataFrame(columns=['時間戳記', '姓名', '您的朋友姓名', '聯絡方式(E-mail)', '年齡', '生理性別', '問題編號', '回答'])

# 將每一行中的回答分成多行
rows_list = []
for index, row in df.iterrows():
    basic_info = row[:6].tolist()  # 提取基本信息列
    for col_index, col_value in enumerate(row[6:], start=1):  # 忽略前六列基本信息
        new_row = basic_info + [f'問題{col_index}', col_value]
        rows_list.append(new_row)

# 將列表轉換為新的DataFrame
new_df = pd.DataFrame(rows_list, columns=['時間戳記', '姓名', '您的朋友姓名', '聯絡方式(E-mail)', '年齡', '生理性別', '問題編號', '回答'])


# 將轉換後的數據寫入新的CSV檔
output_file = 'E:\Research\part\data\\transformed_data.csv'
new_df.to_csv(output_file, index=False, encoding='utf-8-sig')
print(f'Transformed data saved to {output_file}')
