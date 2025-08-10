## 實驗說明
因為我們將受試者們的回答分成兩類：正常照片、誤導性照片，所以分別先對這兩類進行 Cohen's kappa value，最後再對兩邊都有提到的 
Common Visual Features 他們的 Cohen's kappa value 取平均值。


## 檔案說明

`資料夾`

  - **[Thematic Analysis](./Thematic%20Analysis)**  
  我們將受試者們的回答分成兩類：正常照片、誤導性照片，分別對這兩類進行 Thematic Analysis，另外還有附上 Thematic Analysis的教學步驟，可供參考。

`csv`

  - **[MisleadingPhoto.csv](./MisleadingPhoto.csv)**  
  對誤導性照片進行 Thematic Analysis 得出的所有 Codes 的 0,1 編碼。

  - **[NormalPhoto.csv](./NormalPhoto.csv)**  
  對正常照片進行的 Thematic Analysis 得出的所有 Codes 的 0,1 編碼。

`Rmd`

  - **[Cohen_Misleading.Rmd](./Cohen_Misleading.Rmd)**  
  使用 [MisleadingPhoto.csv](./MisleadingPhoto.csv) 來取得誤導性照片中提到的 Visual Features 的 Cohen's kappa value。
  
  - **[Cohen_Normal.Rmd](./Cohen_Normal.Rmd)**  
  使用 [NormalPhoto.csv](./NormalPhoto.csv) 來取得正常照片中提到的 Visual Features 的 Cohen's kappa value。

  - **[Average_Cohen.Rmd](./Average_Cohen.Rmd)**
  將正常照片、誤導性照片都有提到的 Common Visual Features 列出來，並且取得兩類平均的 Cohen's kappa value。  
[註]一定要先執行 [Cohen_Misleading.Rmd](./Cohen_Misleading.Rmd), [Cohen_Normal.Rmd](./Cohen_Normal.Rmd) 才能執行此 `Rmd` file。


 
