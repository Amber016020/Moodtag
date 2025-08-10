# MoodTag

> **題目：** MoodTag: Enhancing Emotion Regulation in Dyadic Instant Messaging Conversations Using AI-Assisted Affect Labeling  
> **作者：** 吳佳融, 指導教授：程芙茵

## Table of Contents
- [System](#System)
- [Measures](#Measures)
- [Data Analysis](#Data-Analysis)

## System
### 網站描述： 本研究設計並實現了一個支持 AI 助力情緒標記的文字聊天系統。系統核心功能包括：
  1. 即時情緒辨識（透過 ChatGPT API）；
  2. 使用者手動標記情緒；
  3. 聊天數據自動儲存與分析。


### 架設說明：
系統建置流程分為兩個主要步驟：

#### GCP 環境建置
請參考 [Installation/gcp-setup.pdf](Installation/gcp-setup.pdf) 說明，完成 Google Cloud Platform 環境的設定，包含虛擬機建立、網路與防火牆設定等。

#### 系統部屬
完成 GCP 環境後，請參考 [Installation/system-deployment.pdf](Installation/system-deployment.pdf) 進行系統部屬，包括應用程式安裝、資料庫設定與服務啟動。

完成以上步驟後，即可啟動系統，並使用 AI 輔助情緒標記、手動標記，以及自動化的聊天資料記錄與分析功能。

## Measures
請參考 [Measures/問卷使用說明.md](Measures/問卷使用說明.md) 文件，內含所使用量表的介紹和完整題目。

### Alexithymia
- **描述：** 評估受測者述情障礙的程度
測量工具： 請參考 Measures/Creative_Production/使用說明.md 文件，內含所使用的評分指標和評分方式。
- **文獻來源：** Preece, D., Becerra, R., Robinson, K., Dandy, J., & Allan, A. (2018). The psychometric assessment of alexithymia: Development and validation of the Perth Alexithymia Questionnaire. Personality and Individual Differences, 132, 32-44.
### Affect
- **描述：** 評估受測者在當下情緒的狀態
- **文獻來源：** Watson, D., Clark, L. A., & Tellegen, A. (1988). Development and validation of brief measures of positive and negative affect: the PANAS scales. Journal of personality and social psychology, 54(6), 1063.
### Trust
- **描述：** 評估受測者對AI的信任程度
- **文獻來源：** 
    - Scharowski, N., Perrig, S. A., Aeschbach, L. F., von Felten, N., Opwis, K., Wintersberger, P., & Brühlmann, F. (2024). To Trust or Distrust Trust Measures: Validating Questionnaires for Trust in AI. arXiv preprint arXiv:2403.00582.
    - Jian, J. Y., Bisantz, A. M., & Drury, C. G. (2000). Foundations for an empirically determined scale of trust in automated systems. International journal of cognitive ergonomics, 4(1), 53-71.
### Acceptance
- **描述：** 評估受測者對通訊軟體的接受程度
- **文獻來源：** Yarosh, S., Markopoulos, P., & Abowd, G. D. (2014, February). Towards a questionnaire for measuring affective benefits and costs of communication technologies. In Proceedings of the 17th ACM conference on Computer supported cooperative work & social computing (pp. 84-96).

## Data Analysis
### R Data
- **描述：** 本研究所蒐集的各項原始數據。
- **檔案說明：** 請參考 [Data_Analysis/Raw_Data/原始數據.md](Data_Analysis/Raw_Data/原始數據.md) 文件，內含各檔案所對應的測量變數。

### R Code
- **描述：** 資料分析所使用的 R 程式碼。
- **檔案說明：** 請參考 [Data_Analysis/R_Code/分析程式碼.md](Data_Analysis/R_Code/分析程式碼.md) 文件，內含各檔案所對應的研究問題。
