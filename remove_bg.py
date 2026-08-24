import sys
from rembg import remove
from PIL import Image

input_path = "public/images/hero-character.png"
output_path = "public/images/hero-character.png"

try:
    with open(input_path, 'rb') as i:
        with open(output_path, 'wb') as o:
            input_data = i.read()
            output_data = remove(input_data)
            o.write(output_data)
    print("Background removed successfully.")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
