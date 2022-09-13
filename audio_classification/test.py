import requests 
import json

BASE = " http://127.0.0.1:5000/" 

response = requests.put(BASE, {'link' : 'C:\\Users\\pragya\\Downloads\\Iteration 2\\test\\new_holland_honeyeater_test_1.wav'})
print(response.json())
