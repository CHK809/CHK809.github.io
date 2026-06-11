import urllib.request
import os
import glob
import re

img_dir = "/Users/sean/Desktop/期中/image"
os.makedirs(img_dir, exist_ok=True)

drink_url = "https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG"
donut_url = "https://upload.wikimedia.org/wikipedia/commons/a/a5/Glazed-Donut.jpg"
fruit_url = "https://upload.wikimedia.org/wikipedia/commons/e/e0/Sliced_bread.jpg"

print("Downloading images...")
urllib.request.urlretrieve(drink_url, os.path.join(img_dir, "new_drink.jpg"))
urllib.request.urlretrieve(donut_url, os.path.join(img_dir, "new_cake.jpg"))
urllib.request.urlretrieve(fruit_url, os.path.join(img_dir, "new_bread.jpg"))
print("Images downloaded.")

html_files = glob.glob("/Users/sean/Desktop/期中/*.html")

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Drinks images
    content = re.sub(r'image/(0[1-9]|1[0-3]|27|28|31|32|33)\.jpg', 'image/new_drink.jpg', content)
    
    # Donuts (Cakes) images
    content = re.sub(r'image/000[1-7]\.jpg', 'image/new_cake.jpg', content)
    
    # Fruits (Breads) images
    content = re.sub(r'image/S[1-6]\.jpg', 'image/new_bread.jpg', content)
    content = re.sub(r'image/unnamed(?:\s\(\d+\))?\.jpg', 'image/new_bread.jpg', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("HTML files updated with new image paths.")
