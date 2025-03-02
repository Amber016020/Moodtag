import os
from pypdf import PdfReader, PdfWriter

# 定義資料夾路徑
folder_path = r"E:\Research\簽名回傳"

# 取得資料夾下所有 PDF 檔案的完整路徑
pdf_files = [os.path.join(folder_path, file) for file in os.listdir(folder_path) if file.endswith('.pdf')]

# 初始化 PDF 寫入器
pdf_writer = PdfWriter()

# 用來儲存無法成功擷取第四頁的檔案
failed_files = []

# 遍歷每個 PDF 檔案
for pdf_file in pdf_files:
    # 讀取 PDF 檔案
    try:
        pdf_reader = PdfReader(pdf_file)
        print(f"檔案名 '{pdf_file}'")
        # 檢查該 PDF 的頁數
        num_pages = len(pdf_reader.pages)
        
        if num_pages >= 4:
            # 擷取第四頁（索引從 0 開始，所以第四頁為 pages[3]）
            fourth_page = pdf_reader.pages[3]
            pdf_writer.add_page(fourth_page)
            print(f"已擷取第四頁。")
        elif num_pages == 1:
            # 如果只有 1 頁，擷取該頁
            first_page = pdf_reader.pages[0]
            pdf_writer.add_page(first_page)
            print(f"檔案只有 1 頁，已擷取第一頁。")
        else:
            print(f"檔案 '{pdf_file}' 少於 4 頁且多於 1 頁，未處理。")
            failed_files.append(pdf_file)
    except Exception as e:
        print(f"讀取檔案 '{pdf_file}' 時發生錯誤: {e}")
        failed_files.append(pdf_file)

# 輸出合併後的 PDF 檔案
output_file_path = r"E:\Research\IRB_amber.pdf"
with open(output_file_path, 'wb') as output_pdf:
    pdf_writer.write(output_pdf)

print(f"完成，合併的 PDF 已儲存為 '{output_file_path}'")

# 列出無法成功處理的檔案
if failed_files:
    print("無法成功擷取頁面的檔案如下：")
    for failed_file in failed_files:
        print(failed_file)
else:
    print("所有 PDF 檔案均已成功處理。")
