import sys
from PIL import Image

def remove_white_bg(input_path, output_path, tolerance=240):
    try:
        img = Image.open(input_path).convert("RGBA")
        datas = img.getdata()

        newData = []
        for item in datas:
            # If the pixel is mostly white
            if item[0] >= tolerance and item[1] >= tolerance and item[2] >= tolerance:
                # Replace with a transparent pixel
                newData.append((255, 255, 255, 0))
            else:
                newData.append(item)

        img.putdata(newData)
        img.save(output_path, "PNG")
        print(f"Background removed successfully and saved to {output_path}")
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)

input_img = "public/images/hero-character.png"
output_img = "public/images/hero-character-nobg.png"
remove_white_bg(input_img, output_img, tolerance=230)
