import pandas as pd
from transformers import GPT2Tokenizer, GPT2LMHeadModel, TrainingArguments, Trainer
from datasets import Dataset
import torch

# 讀取Excel文件
df = pd.read_excel('d:/xampp/htdocs/ChatApp/python/chatLabel.xlsx')


# 檢查數據
print(df.head())

# 將DataFrame轉換為Hugging Face的Dataset格式
dataset = Dataset.from_pandas(df)

# 加載預訓練的GPT-2模型和tokenizer
tokenizer = GPT2Tokenizer.from_pretrained('gpt2')

# 設置pad_token
tokenizer.pad_token = tokenizer.eos_token

model = GPT2LMHeadModel.from_pretrained('gpt2')

# 數據預處理
def preprocess_function(examples):
    inputs = tokenizer(examples['text'], padding='max_length', truncation=True, return_tensors='pt')
    inputs['labels'] = inputs.input_ids.clone()  # 將input_ids作為labels
    return inputs

tokenized_dataset = dataset.map(preprocess_function, batched=True)

# 訓練參數設置
training_args = TrainingArguments(
    output_dir='./results',
    num_train_epochs=3,
    per_device_train_batch_size=4,
    per_device_eval_batch_size=4,
    warmup_steps=500,
    weight_decay=0.01,
    logging_dir='./logs',
)

# 自定義Trainer類以覆蓋compute_loss方法
class CustomTrainer(Trainer):
    def compute_loss(self, model, inputs, return_outputs=False):
        labels = inputs.pop("labels")
        outputs = model(**inputs)
        logits = outputs.logits
        loss_fct = torch.nn.CrossEntropyLoss()
        loss = loss_fct(logits.view(-1, model.config.vocab_size), labels.view(-1))
        return (loss, outputs) if return_outputs else loss

# 訓練
trainer = CustomTrainer(
    model=model,
    args=training_args,
    train_dataset=tokenized_dataset,
    eval_dataset=tokenized_dataset  # 這裡應該分割一部分數據作為驗證集
)

trainer.train()

# 保存模型和tokenizer
model.save_pretrained('./trained_model')
tokenizer.save_pretrained('./trained_model')