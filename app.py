## REFERENCE: https://www.geeksforgeeks.org/how-to-automate-the-storage-using-dropbox-api-in-python/
## REFERENCE: https://villoro.com/post/dropbox_python
import io
import pathlib
import pandas as pd
import dropbox
from dropbox.exceptions import AuthError
import librosa
import numpy as np
import flask
from flask import Flask
from flask_restful import Api, Resource, reqparse
import librosa
import numpy as np
import pickle
import pandas.util.testing as tm
from pyngrok import ngrok
from pyngrok import ngrok, conf, installer
import os
import nest_asyncio
from tensorflow.python.keras.backend import set_session


app = Flask(__name__)
api = Api(app)

TOKEN = '****'


@app.route('/', methods = ['PUT'])
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

            # dbx object contains all functions that 
            # are required to perform actions with dropbox
            files = dbx.files_list_folder(folder_path).entries

            for file in files:

                # listing
                filenames.append(file.name)
            return filenames

        except Exception as e:
            print(str(e))
    
    def features_extractor(file_name):
        audio, sample_rate = librosa.load(file_name, res_type='kaiser_fast') 
        mfccs_features = librosa.feature.mfcc(y=audio, sr=sample_rate, n_mfcc=40)
        mfccs_scaled_features = np.mean(mfccs_features.T,axis=0)
    
        return mfccs_scaled_features
    
    def read_extract_from_file():
    
        # here dbx is an object which is obtained
        # by connecting to dropbox via token
        dbx = connect_to_dropbox()

        try:
            _, res = dbx.files_download("/Audio Submit Form/"+ user_file[0])

            with io.BytesIO(res.content) as stream:
                data=features_extractor(stream)
            return data

        except Exception as e:
            print(str(e))
    
    dbx = connect_to_dropbox()
    filenames = list_files_in_folder()

    wav_files = [i for i in filenames if i.endswith('.wav')]
    file_num = [int(i.split(" ")[0]) for i in filenames if i.endswith('.wav')]
    curr_file = max(file_num)
    user_file = [i for i in wav_files if str(curr_file) in i]
    mfccs_scaled_features = read_extract_from_file()
    mfccs_scaled_features = mfccs_scaled_features.reshape(1,-1)
    model = pickle.load(open("model_saved.sav",'rb'))
    predicted_label=model.predict(mfccs_scaled_features)
    classes_x=np.argmax(predicted_label,axis=1)
    if classes_x == [0]:
        pred = 'Brown Headed'
    elif classes_x == [1]:
        pred = 'New Holland'
    elif classes_x == [2]:
        pred = 'Not Specified'
    else:
        pred = 'White Napped'

    return {'class': pred}


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


