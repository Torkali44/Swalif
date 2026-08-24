import sys
from PIL import Image
import collections

def remove_bg_advanced(input_path, output_path):
    img = Image.open(input_path).convert("RGBA")
    width, height = img.size
    pixels = img.load()
    
    bg_mask = [[False]*height for _ in range(width)]
    queue = collections.deque([(0,0), (width-1,0), (0,height-1), (width-1,height-1)])
    
    # 1. Flood fill for main background (tolerance is wider, > 230)
    def is_bg(r, g, b):
        return r > 230 and g > 230 and b > 230
        
    while queue:
        x, y = queue.popleft()
        if bg_mask[x][y]:
            continue
            
        r, g, b, a = pixels[x, y]
        if is_bg(r, g, b):
            bg_mask[x][y] = True
            pixels[x, y] = (255, 255, 255, 0)
            for dx, dy in [(-1,0), (1,0), (0,-1), (0,1)]:
                nx, ny = x + dx, y + dy
                if 0 <= nx < width and 0 <= ny < height and not bg_mask[nx][ny]:
                    queue.append((nx, ny))

    # 2. Check for trapped ultra-white pixels and edge anti-aliasing
    for x in range(width):
        for y in range(height):
            if not bg_mask[x][y]:
                r, g, b, a = pixels[x, y]
                
                # If there are any trapped pure white pixels, make them transparent
                if r > 250 and g > 250 and b > 250:
                    pixels[x, y] = (255, 255, 255, 0)
                    bg_mask[x][y] = True
                    continue

                # Anti-aliasing for edges
                is_edge = False
                for dx, dy in [(-1,0), (1,0), (0,-1), (0,1)]:
                    nx, ny = x + dx, y + dy
                    if 0 <= nx < width and 0 <= ny < height:
                        if bg_mask[nx][ny]:
                            is_edge = True
                            break
                
                if is_edge:
                    lightness = max(r, g, b)
                    if lightness > 180:
                        new_alpha = int(255 - (lightness - 180) * (255.0 / 75.0))
                        new_alpha = max(0, min(255, new_alpha))
                        pixels[x, y] = (r, g, b, min(a, new_alpha))
                        
    img.save(output_path, "PNG")
    print(f"Saved custom transparent image to {output_path}")

remove_bg_advanced("public/images/hero-character.png", "public/images/hero-character-custom.png")
