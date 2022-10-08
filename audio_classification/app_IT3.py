## REFERENCE: https://www.geeksforgeeks.org/how-to-automate-the-storage-using-dropbox-api-in-python/
## REFERENCE: https://villoro.com/post/dropbox_python

# import all required files
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
from tensorflow.python.keras.backend import set_session
from flask_cors import CORS
import tensorflow as tf
import tensorflow.keras.models

# define app name for the api, call FLASK function
app = Flask(__name__)
# add cors to the add to connect to any platform without error
CORS(app)

# define dropbox api token
TOKEN = '****'

# set route to the prediction function
@app.route('/pred', methods = ['GET'])
# define prediction function
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
    
    # define the function to extract features
    def features_extractor(file_name):
        # load and read the data and sample rate of the audio file
        audio, sample_rate = librosa.load(file_name, res_type='kaiser_fast') 
        # using the audio data and its sample rate, extract features
        mfccs_features = librosa.feature.mfcc(y=audio, sr=sample_rate, n_mfcc=40)
        # transpose the features extracted matrix and find its mean
        mfccs_scaled_features = np.mean(mfccs_features.T,axis=0)
        
        # return features extracted for the file name provided
        return mfccs_scaled_features
    
    # define function to fetch file from the dropbox
    def read_extract_from_file():
    
        # here dbx is an object which is obtained
        # by connecting to dropbox via token
        dbx = connect_to_dropbox()

        try:
            # download the required audio file
            _, res = dbx.files_download("/Audio Submit Form/"+ user_file[0])
            # iterate through the content of the audio file
            with io.BytesIO(res.content) as stream:
                # call feature extraction function for the data loaded
                data=features_extractor(stream)
            # return the extracted feature data
            return data
        # if error occurs,
        except Exception as e:
            # print errors
            print(str(e))
    
    # call dropbox connection function build a connection
    dbx = connect_to_dropbox()
    # call the list files function to fetch all filenames from the dropbox folder
    filenames = list_files_in_folder()
    # from the fetched filenames, find all files in "wav" format
    wav_files = [i for i in filenames if i.endswith('.wav')]
    # to find the latest file submitted to the dropbox,
    # extract only the starting numerical bits of each filename and convert it into an integer format
    file_num = [int(i.split(" ")[0]) for i in filenames if i.endswith('.wav')]
    # find the max value from the list to state the latest file
    curr_file = max(file_num)
    # check the substring of the latest file in the list containing names of all wav files
    user_file = [i for i in wav_files if str(curr_file) in i]
    # call the extract file function to read the data and extract features
    mfccs_scaled_features = read_extract_from_file()
    # reshape the features in the required format
    mfccs_scaled_features = mfccs_scaled_features.reshape(1,-1)
    # load the best saved model
    model = tf.keras.models.load_model('saved_models/weights.best.sequential.hdf5')
    # using the loaded trained model, predict label for the new audio file based on features
    predicted_label=model.predict(mfccs_scaled_features)
    # find the class with maximum probability 
    classes_x=np.argmax(predicted_label,axis=1)
    
    # as per the label encoder in the 3_model_training_save_IT3 file, transform the encoded label to its original format
    
    # check if the class is 0
    if classes_x == [0]:
        # if true, set the category 
        pred = 'Brown Headed Honeyeater'
    # check if the class is 1
    elif classes_x == [1]:
        # if true, set the category 
        pred = 'Melbourne Skyline Bird'
    # check if the class is 2
    elif classes_x == [2]:
        # if true, set the category 
        pred = 'New Holland Honeyeater'
    # check if the class is 3
    elif classes_x == [3]:
        # if true, set the category 
        pred = 'Not Bird'
    # check if the class is 4
    elif classes_x == [4]:
        # if true, set the category 
        pred = 'White Napped Honeyeater'
    else:
        pass
    
    # return the predicted category in json format
    return {'SPECIE': pred}

# Trial function is built to check if the server connection is built.
# it is one of the debugging options. In case the prediction function does not work, 
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