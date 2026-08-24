import sys
import os
from rembg import remove
from PIL import Image

input_path = "public/images/hero-character.png"
temp_output_path = "public/images/hero-character.png.tmp"

try:
    with open(input_path, 'rb') as i:
        input_data = i.read()
        output_data = remove(input_data)
        
    with open(temp_output_path, 'wb') as o:
        o.write(output_data)
        
    os.replace(temp_output_path, input_path)
    print("Background removed successfully.")
except Exception as e:
    if os.path.exists(temp_output_path):
        os.remove(temp_output_path)
    print(f"Error: {e}")
    sys.exit(1)
