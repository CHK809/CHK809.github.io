import glob
import re

html_files = glob.glob("/Users/sean/Desktop/期中/*.html")

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Drinks images
    content = re.sub(r'image/(0[1-9]|1[0-3]|27|28|31|32|33)\.jpg', 'image/drink.jpg', content)
    
    # Donuts (Cakes) images
    content = re.sub(r'image/000[1-7]\.jpg', 'image/cake.jpg', content)
    
    # Fruits (Breads) images
    content = re.sub(r'image/S[1-6]\.jpg', 'image/bread.jpg', content)
    content = re.sub(r'image/unnamed(?:\s\(\d+\))?\.jpg', 'image/bread.jpg', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("HTML files updated with new image paths.")
