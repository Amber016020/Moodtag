"""
This script processes survey data collected from two Google Forms, 
filters relevant responses, and integrates the results into a structured CSV file.

Steps:
1. Load survey responses from two CSV files ('survey.csv' and 'transformed_data.csv').
2. Filter the transformed data to include only users present in the survey data.
3. Update survey responses based on question numbers:
   - PAQ (Questions 1-25) are directly mapped.
   - ABCCT (Questions 25, 28-52) are adjusted for correct indexing.
4. Save the processed results into 'survey_finish.csv'.

Output:
- A cleaned and structured CSV file ('survey_finish.csv') for further analysis.
"""

import pandas as pd

# 讀取 CSV 檔案
survey_df = pd.read_csv('E:/Research/part/data/survey.csv')
transformed_df = pd.read_csv('E:/Research/part/data/transformed_data.csv')

# 篩選 transformed_df，只保留 'userName' 存在於 survey_df 的資料
filtered_transformed_df = transformed_df[transformed_df['姓名'].isin(survey_df['userName'])]

# 檢查篩選結果
print(f"篩選後的 transformed_data：\n{filtered_transformed_df}")

# 根據 userName 和 問題編號篩選對應的行，並更新 response 欄位
for index, row in filtered_transformed_df.iterrows():
    user_name = row['姓名']
    question_number = int(row['問題編號'].replace('問題', ''))
    # 根據 userName 和 問題編號進行更新
    # PAQ 
    if(question_number <= 25):
        response = int(float(row['回答']))
        print(f'Updating PAQ for {user_name}, Question: {question_number}, Response: {response}')
        survey_df.loc[
            (survey_df['scaleName'] == 'PAQ') &
            (survey_df['userName'] == user_name) &
            (survey_df['question_number'] == question_number),
            'neutral'
        ] = response
        # 打印確認
        print(survey_df[
            (survey_df['scaleName'] == 'PAQ') & 
            (survey_df['userName'] == user_name) & 
            (survey_df['question_number'] == question_number)
        ])
    # ABCCT
    if question_number == 25:
        # 对于 question_number = 25，减去 24
        adjusted_question_number = question_number - 24
    elif 28 <= question_number <= 52:
        # 对于 question_number 在 28 到 52 之间，减去 27
        adjusted_question_number = question_number - 26
    # ABCCT
    if question_number == 25 or (28 <= question_number <= 52):
        response = int(float(row['回答']))
        print(f'Updating ABCCT for {user_name}, Question: {adjusted_question_number}, Response: {response}')
        survey_df.loc[
            (survey_df['scaleName'] == 'ABCCT') &
            (survey_df['userName'] == user_name) &
            (survey_df['question_number'] == adjusted_question_number),
            'neutral'
        ] = response
        # 打印確認
        print(survey_df[
            (survey_df['scaleName'] == 'ABCCT') & 
            (survey_df['userName'] == user_name) & 
            (survey_df['question_number'] == adjusted_question_number)
        ])
    
# 將修改後的 DataFrame 保存為新的 CSV 檔案
survey_df.to_csv('E:/Research/part/data/survey_finish.csv', index=False, encoding='utf-8-sig')
