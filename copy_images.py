import os
import shutil

src_dir = "/Users/sean/.gemini/antigravity/brain/50103afa-df7f-4ffb-9466-251b30515662"
dest_dir = "/Users/sean/Desktop/期中/image"

# Find generated images
drink_img = None
cake_img = None
bread_img = None

for f in os.listdir(src_dir):
    if f.startswith("drink_image"): drink_img = os.path.join(src_dir, f)
    elif f.startswith("cake_image"): cake_img = os.path.join(src_dir, f)
    elif f.startswith("bread_image"): bread_img = os.path.join(src_dir, f)

if drink_img: shutil.copyfile(drink_img, os.path.join(dest_dir, "new_drink.png"))
if cake_img: shutil.copyfile(cake_img, os.path.join(dest_dir, "new_cake.png"))
if bread_img: shutil.copyfile(bread_img, os.path.join(dest_dir, "new_bread.png"))

print("Copy completed")
