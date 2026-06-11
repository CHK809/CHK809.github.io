import os
import glob
import re

directory = "/Users/sean/Desktop/期中"
html_files = glob.glob(os.path.join(directory, "*.html"))

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Navbar background to light khaki
    content = re.sub(r'\.navbar\s*{\s*background:\s*#ffffff\s*;', '.navbar { background: #F5EEDC;', content)
    content = re.sub(r'background:\s*#ffffff\s*!important;', 'background: #F5EEDC !important;', content)
    
    # Menu sections background to khaki
    content = re.sub(r'background:\s*#f4f6f8;', 'background: #F0E6D2;', content)
    content = re.sub(r'background:\s*#fff8f8;', 'background: #E8D8C8;', content)
    content = re.sub(r'background:\s*#f4fff8;', 'background: #D2C4A7;', content)

    # Footer background to coffee brown
    content = re.sub(r'background:\s*linear-gradient\(135deg,\s*#f2f6f4\s*0%,\s*#e6ece9\s*100%\)', 'background: linear-gradient(135deg, #4A3623 0%, #3C2A1E 100%)', content)
    
    # Footer headings to light khaki
    content = re.sub(r'\.footer-section\s*h3\s*{\s*color:\s*#006241', '.footer-section h3 { color: #F5EEDC', content)
    
    # Footer text to khaki
    content = re.sub(r'color:\s*#505965;', 'color: #D2C4A7;', content)
    
    # Social links hover background to coffee/khaki
    content = re.sub(r'background:\s*linear-gradient\(135deg,\s*#00704A\s*0%,\s*#004d33\s*100%\)', 'background: linear-gradient(135deg, #6F4E37 0%, #4A3623 100%)', content)
    
    # Price text to coffee brown
    content = re.sub(r'\.menu-item-price\s*{\s*font-size:\s*1\.05rem;\s*font-weight:\s*500;\s*color:\s*#00704A', '.menu-item-price { font-size: 1.05rem; font-weight: 500; color: #6F4E37', content)
    
    # Login/Signup backgrounds to khaki/coffee
    content = re.sub(r'background:\s*linear-gradient\(135deg,\s*#e6f0eb\s*0%,\s*#00704A\s*100%\)', 'background: linear-gradient(135deg, #F0E6D2 0%, #C3B091 100%)', content)
    
    # Login/Signup buttons to coffee
    content = re.sub(r'background:\s*linear-gradient\(135deg,\s*#00704A\s*0%,\s*#00704A\s*100%\)', 'background: linear-gradient(135deg, #6F4E37 0%, #4A3623 100%)', content)

    # Some border colors in menu title
    content = re.sub(r'border-bottom:\s*3px\s*solid\s*#00704A;', 'border-bottom: 3px solid #6F4E37;', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("Colors updated successfully.")
