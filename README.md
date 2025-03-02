# Moodtag

> **題目：** MoodTag: Enhancing Emotion Regulation in Dyadic Instant Messaging Conversations Using AI-Assisted Affect Labeling  
> **作者：** 吳佳融, 指導教授：程芙茵

## Table of Contents
- [System](#System)
- [Measures](#Measures)
- [Data Analysis](#Data-Analysis)

## System
### 架設網站
- **描述：** 本研究設計並實現了一個支持 AI 助力情緒標記的文字聊天系統。系統核心功能包括：
  1. 即時情緒辨識（透過 ChatGPT API）；
  2. 使用者手動標記情緒；
  3. 聊天數據自動儲存與分析。
- **操作說明：** 請參考 [System/系統建構.pptx](System/系統建構.pptx) 文件，內含詳細的步驟說明。

## Measures

### Alexithymia
- **描述：** 評估受測者述情障礙的程度
- **測量工具：** 請參考 [Measures/Alexithymia/使用說明.md](Measures/Alexithymia/使用說明.md) 文件，內含所使用測驗的介紹和完整題目。
- **文獻來源：** Preece, D., Becerra, R., Robinson, K., Dandy, J., & Allan, A. (2018). The psychometric assessment of alexithymia: Development and validation of the Perth Alexithymia Questionnaire. Personality and Individual Differences, 132, 32-44.
### Affect
- **描述：** 評估受測者在當下情緒的狀態
- **測量工具：** 請參考 [Measures/Affect/使用說明.md](Measures/Affect/使用說明.md) 文件，內含所使用測驗的介紹和完整題目。
- **文獻來源：** Watson, D., Clark, L. A., & Tellegen, A. (1988). Development and validation of brief measures of positive and negative affect: the PANAS scales. Journal of personality and social psychology, 54(6), 1063.
### Trust
- **描述：** 評估受測者對AI的信任程度
- **測量工具：** 請參考 [Measures/Trust/使用說明.md](Measures/Trust/使用說明.md) 文件，內含所使用測驗的介紹和完整題目。
- **文獻來源：** 
    - Scharowski, N., Perrig, S. A., Aeschbach, L. F., von Felten, N., Opwis, K., Wintersberger, P., & Brühlmann, F. (2024). To Trust or Distrust Trust Measures: Validating Questionnaires for Trust in AI. arXiv preprint arXiv:2403.00582.
    - Jian, J. Y., Bisantz, A. M., & Drury, C. G. (2000). Foundations for an empirically determined scale of trust in automated systems. International journal of cognitive ergonomics, 4(1), 53-71.
### Acceptance
- **描述：** 評估受測者對通訊軟體的接受程度
- **測量工具：** 請參考 [Measures/Acceptance/使用說明.md](Measures/Acceptance/使用說明.md) 文件，內含所使用測驗的介紹和完整題目。
- **文獻來源：** Yarosh, S., Markopoulos, P., & Abowd, G. D. (2014, February). Towards a questionnaire for measuring affective benefits and costs of communication technologies. In Proceedings of the 17th ACM conference on Computer supported cooperative work & social computing (pp. 84-96).

## Data Analysis

### Raw Data
- **描述：** 本研究蒐集的數據包括：
  1. 聊天記錄（CSV 格式）：存儲實驗過程中所有訊息的時間戳與情緒標記；
  2. 問卷結果（CSV 格式）：包含受測者在 PANAS 、 PAQ 、 TPA 、 ABCCT 的回答。
- **檔案說明：** 
  - [chat_logs.csv](Data_Analysis/Raw_Data/chat_logs.csv): 聊天訊息記錄
  - [survey_responses.csv](Data_Analysis/Raw_Data/survey_responses.csv): 問卷原始數據
  - [emotion_changes_over_time.csv](Data_Analysis/Processed_Data/emotion_changes_over_time.csv): 時間序列情緒變化

### R Code
- **描述：** 資料分析所使用的 R 程式碼。
- **檔案說明：** 請參考 [Data_Analysis/R_Code/分析程式碼.md](Data_Analysis/R_Code/分析程式碼.md) 文件，內含各檔案所對應的研究問題。

### video
### Video
- **描述：** 本實驗錄影主要用於：
  1. 分析參與者面部情緒變化；
  2. 比對聊天內容與行為反應。
- **隱私保障：** 錄影僅供研究使用。
- **檔案說明：** 所有檔案保存在白色公用電腦的 "D:\Moodtag\Videos" 資料夾中，檔名格式為 `Participant_<編號>.webm`。