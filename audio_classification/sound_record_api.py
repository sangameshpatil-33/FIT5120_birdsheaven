## REFERENCE: https://www.geeksforgeeks.org/how-to-automate-the-storage-using-dropbox-api-in-python/
## REFERENCE: https://villoro.com/post/dropbox_python

# import required libraries
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

# define app name for the api, call FLASK function
app = Flask(__name__)
# add cors to the add to connect to any platform without error
CORS(app)

# define dropbox api token
TOKEN = '****'

# set route to the record function
@app.route('/record', methods = ['GET'])
def specie_pred():
    
    # Establish connection to dropbox
    def connect_to_dropbox():

        try:
            # connect to dropbox
            dbx = dropbox.Dropbox(TOKEN)
            
        # if error occured,
        except Exception as e:
            # print the error
            print(str(e))
            
        # return dropbox connection
        return dbx
    
    # explicit function to list files
    def list_files_in_folder():

        # here dbx is an object which is obtained
        # by connecting to dropbox via token
        dbx = connect_to_dropbox()

        try:
            # create emmpty list to store filenames
            filenames = []
            # define folder path where files are stored in dropbox
            folder_path = "/Audio Submit Form"
            # using the dropbox xonnection, list all the files in the folder
            files = dbx.files_list_folder(folder_path).entries

            # iterate through each file in the folder
            for file in files:
                # fetch the names of the file and store it in the list
                filenames.append(file.name)
            # return list of all file names
            return filenames
        
        # if any error occurs,
        except Exception as e:
            # print error
            print(str(e))
            
    # define audio recording function
    def record_audio(filename):
        # set filename to save the audio data
        filename = filename + ' -user.wav'
        # Record in chunks of 1024 samples
        chunk = 1024  
        # 16 bits per sample
        sample_format = pyaudio.paInt16  
        channels = 2
        # Record at 44100 samples per second
        fs = 44100  
        # time limit set for the recording
        seconds = 20
        
        # Create an interface to PortAudio
        p = pyaudio.PyAudio()  
        
#         print('Recording')

        # listen to audio data with the set paramters
        stream = p.open(format=sample_format,
                        channels=channels,
                        rate=fs,
                        frames_per_buffer=chunk,
                        input=True)
        
         # Initialize array to store frames
        frames = [] 

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
    
    # call dropbox connection function build a connection
    dbx = connect_to_dropbox()
    # call the list files function to fetch all filenames from the dropbox folder
    filenames = list_files_in_folder()
    # from the fetched filenames, find all files in "wav" format
    wav_files = [i for i in filenames if i.endswith('.wav')]
    # to find the latest file submitted to the dropbox,
    # extract only the starting numerical bits of each filename and convert it into an integer format
    file_num = [int(i.split(" ")[0]) for i in wav_files]
    # find the max value from the list to state the latest file and add 1 to set it as the latest file being added
    filename = max(file_num) + 1
    # call record function 
    record_audio(str(filename))
    # call the send audio function to send the recorded data to dropbox
    send_audio(str(filename))
    
    # return a message for the user in json format
    return {'RESULT': 'Your audio has been recorded. Proceed to Predict Button to know your backyard bird.'}

# Trial function is built to check if the server connection is built.
# it is one of the debugging options. In case the record function does not work, 
# we can call trial function to check if the connection wsa successfull
# set route to the trial function
@app.route('/', methods = ['GET'])
def trial():
    # return json value home ok
    return {"home": "ok"}

# call the main class
if __name__ == "__main__":
    # set configuration to convert the local machine as the dedicated server
    pyngrok_config = conf.get_default()
    if not os.path.exists(pyngrok_config.ngrok_path):
        myssl = ssl.create_default_context();
        myssl.check_hostname=False
        myssl.verify_mode=ssl.CERT_NONE
        installer.install_ngrok(pyngrok_config.ngrok_path, context=myssl)

    # build a connection to port 8000
    ngrok_tunnel = ngrok.connect(8000)
    # create public url 
    print("PUBLIC URL:", ngrok_tunnel.public_url)
    nest_asyncio.apply()
    # run app on port 8000 and with the parameter debug as True
    app.run(debug = True, port = 8000)