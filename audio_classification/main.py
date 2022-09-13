import flask
from flask import Flask
from flask_restful import Api, Resource, reqparse
import librosa
import numpy as np
import pickle
import pandas.util.testing as tm
# import tensorflow as tf
# import keras
# from keras.models import load_model
from tensorflow.python.keras.backend import set_session


app = Flask(__name__)
api = Api(app)


link_args = reqparse.RequestParser()
link_args.add_argument("link", type=str, help = "Link address")

# graph = tf.get_default_graph()
# model = load_model("my_model")
# model = pickle.load(open("model_saved.sav",'rb'))



@app.route('/', methods = ['PUT'])
def specie_pred():
    
    # input_data = link_args.json()
    # input_dictionary = json.loads(input_data)
    args = link_args.parse_args()
    filename = args['link']
    print(filename)
    model = pickle.load(open("model_saved.sav",'rb'))

    audio, sample_rate = librosa.load(filename, res_type='kaiser_fast') 
    mfccs_features = librosa.feature.mfcc(y=audio, sr=sample_rate, n_mfcc=40)
    mfccs_scaled_features = np.mean(mfccs_features.T,axis=0)

    mfccs_scaled_features=mfccs_scaled_features.reshape(1,-1)
        
    predicted_label=model.predict(mfccs_scaled_features)
    classes_x=np.argmax(predicted_label,axis=1)
    if classes_x == [0]:
        pred = 'Brown Headed'
    elif classes_x == [1]:
        pred = 'New Holland'
    else:
        pred = 'White Napped'
            
    print(pred)
            
    # data = toDict(pred)
        
    return {'PRINT': pred}


if __name__ == "__main__":
    app.run(debug = True)