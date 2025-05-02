import face_recognition
import sys
import pickle

image_path = sys.argv[1]
user_id = sys.argv[2]

image = face_recognition.load_image_file(image_path)
face_encodings = face_recognition.face_encodings(image)

if len(face_encodings) > 0:
    encoding = face_encodings[0]
    with open(f"C:/xampp/htdocs/ProjetInnoconnect/face_encodings/user_{user_id}.npy", 'wb') as f:
        pickle.dump(encoding, f)
    print("Encoding généré avec succès")
else:
    print("Aucun visage détecté dans l'image")