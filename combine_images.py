from PIL import Image

# Open the two images
# custom: has the cups and background elements, but holes in the character
custom = Image.open("public/images/hero-character-custom.png").convert("RGBA")

# rembg: perfectly extracted character and phone, but missing the cups
rembg = Image.open("public/images/hero-character-rembg.png").convert("RGBA")

# We want rembg ON TOP of custom. 
# This way, the perfect character from rembg covers the character with holes in custom.
# The transparent areas of rembg will let the cups from custom show through!
combined = Image.alpha_composite(custom, rembg)

combined.save("public/images/hero-character-dark.png")
print("Saved combined image for dark mode to public/images/hero-character-dark.png")
