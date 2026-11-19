import glob, re

for f in glob.glob('*.php'):
    with open(f, 'r', encoding='utf-8') as file:
        text = file.read()
        v = re.findall(r"['\"]visual_heading['\"]\s*=>\s*['\"]([^'\"]+)['\"]", text)
        c = re.findall(r"['\"]category_heading['\"]\s*=>\s*['\"]([^'\"]+)['\"]", text)
        if v or c:
            print(f"{f}: visual={v}, category={c}")
