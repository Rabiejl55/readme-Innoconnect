import face_recognition
import numpy as np
import sys
import os

image_path = sys.argv[1]
user_id = sys.argv[2]
output_path = f"C:/ProjetInnoconnect/face_encodings/user_{user_id}.npy"

print(f"Processing image: {image_path}")
if not os.path.exists(image_path):
    print(f"Error: Image file not found at {image_path}")
    sys.exit(1)

try:
    image = face_recognition.load_image_file(image_path)
    print("Image loaded successfully")
    face_encodings = face_recognition.face_encodings(image)
    print(f"Found {len(face_encodings)} face(s) in the image")
    if len(face_encodings) == 0:
        print("Aucun visage détecté dans l'image")
        sys.exit(1)
    elif len(face_encodings) > 1:
        print("Plusieurs visages détectés, veuillez utiliser une photo avec un seul visage")
        sys.exit(1)
    else:
        encoding = face_encodings[0]
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        np.save(output_path, encoding)
        print("Encoding generated and saved successfully")
except Exception as e:
    print(f"Erreur lors de la génération de l'encodage : {str(e)}")
    sys.exit(1)