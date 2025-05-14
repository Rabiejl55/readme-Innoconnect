import face_recognition
import sys
import pickle

# Charger les deux encodages
webcam_encoding_path = sys.argv[1]
stored_encoding_path = sys.argv[2]

with open(webcam_encoding_path, 'rb') as f:
    webcam_encoding = pickle.load(f)

with open(stored_encoding_path, 'rb') as f:
    stored_encoding = pickle.load(f)

# Comparer les encodages
result = face_recognition.compare_faces([stored_encoding], webcam_encoding, tolerance=0.3)
print(result[0])