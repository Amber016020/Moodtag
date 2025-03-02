# Utilities
import joblib

import sys

from googletrans import Translator

import warnings

# 忽略特定警告
warnings.filterwarnings("ignore", category=UserWarning, module="sklearn")


# 獲取命令行參數
param1 = sys.argv[1] if len(sys.argv) > 1 else "default_value1"

# Load pre-trained emotion classifier pipeline
pipe_lr = joblib.load(open("D:/xampp/htdocs/ChatApp/models/emotion_classifier_pipe_lr_03_june_2021.pkl", "rb"))

# Function to translate Chinese text to English
def translate_to_english(chinese_text):
    translator = Translator()
    english_text = translator.translate(chinese_text, src='zh-cn', dest='en').text
    return english_text

# Function to predict emotions for a given document
def predict_emotions(docx):
    results = pipe_lr.predict([docx])
    return results[0]

# Function to get prediction probabilities for each emotion
def get_prediction_proba(docx):
    results = pipe_lr.predict_proba([docx])
    return results

# Emoji mapping for emotions
emotions_emoji_dict = {"anger": "😠", "disgust": "🤮", "fear": "😨😱", "happy": "🤗", "joy": "😂", "neutral": "😐",
                       "sad": "😔", "sadness": "😔", "shame": "😳", "surprise": "😮"}

# Main function
def main():
    #raw_chinese_text = input("Enter a text: ")

    # Translate Chinese text to English
    raw_text_english = translate_to_english(param1)


    # Perform emotion prediction and get probability
    prediction = predict_emotions(raw_text_english)
    probability = get_prediction_proba(raw_text_english)
    #print(emotions_emoji_dict[prediction])
    print(prediction)
    #print(probability)
    return prediction;

# Run the Streamlit app if the script is executed directly
main()
