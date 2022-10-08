## REFERENCE: https://www.geeksforgeeks.org/how-to-automate-the-storage-using-dropbox-api-in-python/
## REFERENCE: https://villoro.com/post/dropbox_python

import io
import pathlib
import pandas as pd
import dropbox
from dropbox.exceptions import AuthError
import librosa
import numpy as np
from flask import Flask
from flask_restful import Api, Resource, reqparse
import pickle
from pyngrok import ngrok
from pyngrok import ngrok, conf, installer
import os
import nest_asyncio
from flask_cors import CORS
import pyaudio
import wave


app = Flask(__name__)
CORS(app)
TOKEN = '****'


@app.route('/record', methods = ['GET'])
def specie_pred():
    
    # Establish connection
    def connect_to_dropbox():

        try:
            dbx = dropbox.Dropbox(TOKEN)
            print('Connected to Dropbox successfully')

        except Exception as e:
            print(str(e))

        return dbx
    
    # explicit function to list files
    def list_files_in_folder():

        # here dbx is an object which is obtained
        # by connecting to dropbox via token
        dbx = connect_to_dropbox()

        try:
            filenames = []
            folder_path = "/Audio Submit Form"
            print(folder_path)
            # dbx object contains all functions that 
            # are required to perform actions with dropbox
            files = dbx.files_list_folder(folder_path).entries

            for file in files:
                
                # listing
                filenames.append(file.name)
            return filenames

        except Exception as e:
            print(str(e))
            
    def record_audio(filename):
        filename = filename + ' -user.wav'
        chunk = 1024  # Record in chunks of 1024 samples
        sample_format = pyaudio.paInt16  # 16 bits per sample
        channels = 2
        fs = 44100  # Record at 44100 samples per second
        seconds = 5

        p = pyaudio.PyAudio()  # Create an interface to PortAudio

        print('Recording')

        stream = p.open(format=sample_format,
                        channels=channels,
                        rate=fs,
                        frames_per_buffer=chunk,
                        input=True)

        frames = []  # Initialize array to store frames

        # Store data in chunks for 3 seconds
        for i in range(0, int(fs / chunk * seconds)):
            data = stream.read(chunk)
            frames.append(data)

        # Stop and close the stream 
        stream.stop_stream()
        stream.close()
        # Terminate the PortAudio interface
        p.terminate()

        print('Finished recording')

        # Save the recorded data as a WAV file
        wf = wave.open(filename, 'wb')
        wf.setnchannels(channels)
        wf.setsampwidth(p.get_sample_size(sample_format))
        wf.setframerate(fs)
        wf.writeframes(b''.join(frames))
        wf.close()
        print('Finished writing')
        
    def send_audio(filename):
        file_from = filename + ' -user.wav'
        file_to = '/Audio Submit Form/' + file_from 
        
        with open(file_from, 'rb') as f:
            dbx.files_upload(f.read(), file_to)
    
    dbx = connect_to_dropbox()
    filenames = list_files_in_folder()
    wav_files = [i for i in filenames if i.endswith('.wav')]
    file_num = [int(i.split(" ")[0]) for i in wav_files]
    filename = max(file_num) + 1
    print(filename)
    record_audio(str(filename))
    send_audio(str(filename))
    
    
    
    return {'RESULT': 'Your audio has been recorded. Proceed to Predict Button to know your backyard bird.'}

@app.route('/', methods = ['GET'])
def trial():
    return {"home": "ok"}


if __name__ == "__main__":
    pyngrok_config = conf.get_default()
    if not os.path.exists(pyngrok_config.ngrok_path):
        myssl = ssl.create_default_context();
        myssl.check_hostname=False
        myssl.verify_mode=ssl.CERT_NONE
        installer.install_ngrok(pyngrok_config.ngrok_path, context=myssl)

    ngrok_tunnel = ngrok.connect(8000)
    print("PUBLIC URL:", ngrok_tunnel.public_url)
    nest_asyncio.apply()
    app.run(debug = True, port = 8000)
