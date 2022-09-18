import requests 
import json

BASE = "http://c477-121-200-5-152.ngrok.io/pred" 
# BASE = "http://52.63.68.159:5000"
# BASE = "https://audio-prediction.herokuapp.com/"

response = requests.put(BASE)
print(response.json())
