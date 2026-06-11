import urllib.request
import os

img_dir = "/Users/sean/Desktop/期中/image"
os.makedirs(img_dir, exist_ok=True)

print("Downloading images from picsum...")
urllib.request.urlretrieve("https://picsum.photos/id/431/400/400", os.path.join(img_dir, "new_drink.jpg"))
urllib.request.urlretrieve("https://picsum.photos/id/106/400/400", os.path.join(img_dir, "new_cake.jpg"))
urllib.request.urlretrieve("https://picsum.photos/id/1080/400/400", os.path.join(img_dir, "new_bread.jpg"))
print("Images downloaded.")
