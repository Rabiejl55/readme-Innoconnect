from PIL import Image
import sys
import numpy as np

image_path = sys.argv[1]

# Charger l'image
image = Image.open(image_path).convert('L')  # Convertir en niveaux de gris
image_array = np.array(image)

# Calculer la luminosité moyenne
brightness = np.mean(image_array)

# Vérifier les reflets (zones très lumineuses)
highlight_threshold = 230  # Seuil pour détecter les zones très lumineuses (proche de blanc)
highlight_percentage = np.sum(image_array > highlight_threshold) / image_array.size * 100

if brightness < 80:
    print("Image trop sombre")
elif highlight_percentage > 5:  # Si plus de 5% de l'image est très lumineuse, probablement des reflets
    print("Trop de reflets")
else:
    print("Image OK")