import requests 
import json

BASE = "http://90e9-121-200-5-152.ngrok.io" 

response = requests.put(BASE, {'link' : 'C:\\Users\\pragya\\Downloads\\Iteration 2\\test\\new_holland_honeyeater_test_1.wav'})
print(response.json())
