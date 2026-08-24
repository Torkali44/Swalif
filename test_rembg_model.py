import sys
from rembg import remove, new_session
from PIL import Image

input_path = "public/images/hero-character.png"
output_path = "public/images/hero-character-isnet.png"

try:
    img = Image.open(input_path)
    session = new_session("isnet-general-use")
    output_img = remove(img, session=session)
    output_img.save(output_path)
    print("Background removed with isnet-general-use.")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
