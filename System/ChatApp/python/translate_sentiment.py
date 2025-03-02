import sys
import nltk
from googletrans import Translator
from nltk.sentiment import SentimentIntensityAnalyzer

nltk.download('vader_lexicon', quiet=True)

sia = SentimentIntensityAnalyzer()
translator = Translator()

# 獲取命令行參數
param1 = sys.argv[1] if len(sys.argv) > 1 else "default_value1"
param2 = sys.argv[2] if len(sys.argv) > 2 else "default_value2"

def emoapi(text_chinese):
    text = translator.translate(text_chinese, dest='en').text
    score = sia.polarity_scores(text)

    if score['compound'] > 0:
        return "Positive Sentiment"
    elif score['compound'] < 0:
        return "Negative Sentiment"
    else:
        return "Neutral Sentiment"

result = emoapi(param1)
print(result)
