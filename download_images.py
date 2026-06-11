import urllib.request
import os
import glob
import re

img_dir = "/Users/sean/Desktop/期中/image"
os.makedirs(img_dir, exist_ok=True)

# Unsplash URLs
drink_url = "https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80"
donut_url = "https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=400&q=80"
fruit_url = "https://images.unsplash.com/photo-1481391319762-47dcb729566d?auto=format&fit=crop&w=400&q=80"

print("Downloading images...")
req = urllib.request.Request(drink_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as response, open(os.path.join(img_dir, "new_drink.jpg"), 'wb') as out_file:
    out_file.write(response.read())

req = urllib.request.Request(donut_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as response, open(os.path.join(img_dir, "new_donut.jpg"), 'wb') as out_file:
    out_file.write(response.read())

req = urllib.request.Request(fruit_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as response, open(os.path.join(img_dir, "new_fruit.jpg"), 'wb') as out_file:
    out_file.write(response.read())

print("Images downloaded.")

html_files = glob.glob("/Users/sean/Desktop/期中/*.html")

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Drinks images
    content = re.sub(r'image/(0[1-9]|1[0-3]|27|28|31|32|33)\.jpg', 'image/new_drink.jpg', content)
    
    # Donuts images
    content = re.sub(r'image/000[1-7]\.jpg', 'image/new_donut.jpg', content)
    
    # Fruits images
    content = re.sub(r'image/S[1-6]\.jpg', 'image/new_fruit.jpg', content)
    content = re.sub(r'image/unnamed(?:\s\(\d+\))?\.jpg', 'image/new_fruit.jpg', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("HTML files updated with new image paths.")
