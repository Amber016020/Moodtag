"""
This script compares two CSV files and identifies differences in their data.

Steps:
1. Load two CSV files: 'Amber_1.csv' and 'Yachun_1.csv'.
2. Verify that both files have the same column structure.
3. Compare values in each column (excluding the first column, assumed to be an index).
4. Record any differences, including the row identifier, column name, and differing values.
5. Save the differences into 'differences.csv' for further analysis.

Output:
- A CSV file ('differences.csv') containing mismatched values, or a message indicating no differences.
"""

import pandas as pd
import os

# 取得當前腳本所在的目錄
script_dir = os.path.dirname(os.path.abspath(__file__))

# 使用相對路徑
file1 = os.path.join(script_dir, "data", "Amber_1.csv")
file2 = os.path.join(script_dir, "data", "Yachun_1.csv")

# 讀取 CSV 檔案
df1 = pd.read_csv(file1)
df2 = pd.read_csv(file2)

# 確認兩份檔案具有相同的結構
if not df1.columns.equals(df2.columns):
    raise ValueError("兩份CSV檔案的欄位名稱不一致！")

# 初始化差異紀錄
differences = []

# 比對每個欄位的值
for col in df1.columns[1:]:  # 假設最左側欄位為索引欄位
    for i in range(len(df1)):
        if pd.isna(df1.at[i, col]) and pd.isna(df2.at[i, col]):
            continue  # 如果兩個都是 NaN，則跳過
        elif df1.at[i, col] != df2.at[i, col]:

            differences.append({
                "Row Key": df1.at[i, df1.columns[0]], 
                "Column": col,  
                "Amber": df1.at[i, col],  
                "Yachun": df2.at[i, col],  # 檔案2的值
            })
import os

print(os.getcwd())
# 將差異輸出成表格
if differences:
    diff_df = pd.DataFrame(differences)
    print(diff_df)

    # 設定 differences.csv 儲存位置（與 file1, file2 相同資料夾）
    diff_file = os.path.join(script_dir, "data", "differences.csv")
    # 存檔
    diff_df.to_csv(diff_file, index=False, encoding='utf-8-sig')

else:
    print("兩份 CSV 檔案完全相同！")
