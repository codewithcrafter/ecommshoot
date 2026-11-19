import urllib.request
from bs4 import BeautifulSoup

pages = [
    '',
    'about',
    'services',
    'product-photography',
    'ecommerce-product-photography',
    'lifestyle-product-photography',
    'fashion-product-photography',
    'creative-product-photography',
    'white-background-product-photography',
    'jewellery-product-photography',
    'product-photo-editing-retouching',
    'blog',
    'blog/amazon-product-photography-guidelines-2024',
    'contact'
]

for p in pages:
    url = f'http://127.0.0.1:8000/{p}'
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req) as resp:
            html = resp.read().decode('utf-8', errors='ignore')
            soup = BeautifulSoup(html, 'html.parser')
            btns = soup.find_all('a', class_=lambda c: c and 'btn' in c)
            btn_info = [(b.get_text(strip=True)[:35], ' '.join(b.get('class', []))) for b in btns]
            print(f'{url} -> {len(btns)} buttons')
            for text, cls in btn_info:
                if any(w in cls for w in ['secondary', 'outline', 'white']) or 'Services' in text or 'Shoot' in text:
                    print(f'   [{text}] -> class="{cls}"')
    except Exception as e:
        print(f'{url} -> ERROR: {e}')
