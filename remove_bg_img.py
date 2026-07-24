import sys
from rembg import remove
from PIL import Image

input_path = "public/images/hero-character.png"
output_path = "public/images/hero-character-rembg.png"

try:
    img = Image.open(input_path)
    output_img = remove(img)
    output_img.save(output_path)
    print("Background removed successfully.")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
