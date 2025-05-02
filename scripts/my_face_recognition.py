import face_recognition
import pickle
import os
import sys
import json

captured_image_path = sys.argv[1]

captured_image = face_recognition.load_image_file(captured_image_path)
captured_encodings = face_recognition.face_encodings(captured_image)

if len(captured_encodings) == 0:
    print(json.dumps({'success': False, 'error': 'No face detected in the captured image'}))
    sys.exit()

captured_encoding = captured_encodings[0]

encoding_dir = "C:/ProjetInnoconnect/face_encodings/"

best_match_id = None
best_match_distance = float('inf')

for filename in os.listdir(encoding_dir):
    if filename.endswith('.npy'):
        user_id = filename.split('_')[1].split('.')[0]
        with open(os.path.join(encoding_dir, filename), 'rb') as f:
            stored_encoding = pickle.load(f)

        distance = face_recognition.face_distance([stored_encoding], captured_encoding)[0]
        if distance < best_match_distance and distance < 0.6:
            best_match_distance = distance
            best_match_id = user_id

if best_match_id:
    print(json.dumps({'success': True, 'user_id': best_match_id}))
else:
    print(json.dumps({'success': False, 'error': 'No matching face found'}))