import sys
import os
import json
import requests
import http.cookiejar

def load_cookies(cookie_file):
    cookies = {}
    try:
        cj = http.cookiejar.MozillaCookieJar()
        cj.load(cookie_file, ignore_discard=True, ignore_expires=True)
        for c in cj:
            cookies[c.name] = c.value
    except:
        pass
    return cookies

def download(url, target_dir, cookie_file=None, proxy=None):
    session = requests.Session()
    if proxy:
        session.proxies = {'http': proxy, 'https': proxy}
        
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.instagram.com/',
    })

    if cookie_file and os.path.exists(cookie_file):
        cookies = load_cookies(cookie_file)
        for name, val in cookies.items():
            session.cookies.set(name, val, domain='.instagram.com')

    try:
        # Resolve absolute path for target_dir
        target_dir = os.path.abspath(target_dir)
        if not os.path.exists(target_dir):
            os.makedirs(target_dir, exist_ok=True)

        # Generate filename
        ext = 'mp4' if 'mp4' in url or 'video' in url.lower() else 'jpg'
        if '?' in url:
            clean_url = url.split('?')[0]
            if clean_url.endswith('.jpg') or clean_url.endswith('.webp'): ext = 'jpg'
            if clean_url.endswith('.mp4'): ext = 'mp4'
            
        filename = f"insta_{int(os.path.getmtime(cookie_file) if os.path.exists(cookie_file) else 0)}_{os.urandom(4).hex()}.{ext}"
        save_path = os.path.join(target_dir, filename)

        with session.get(url, stream=True, timeout=30) as r:
            r.raise_for_status()
            with open(save_path, 'wb') as f:
                for chunk in r.iter_content(chunk_size=262144): # 256KB Chunks for speed
                    f.write(chunk)
        
        print(f"DOWNLOADED_FILE:{save_path}")
    except Exception as e:
        print(f"ERROR:{str(e)}")

if __name__ == "__main__":
    if len(sys.argv) > 2:
        url = sys.argv[1]
        target_dir = sys.argv[2]
        proxy = sys.argv[3] if len(sys.argv) > 3 and sys.argv[3] != '""' else None
        cookie_file = sys.argv[4] if len(sys.argv) > 4 and sys.argv[4] != '""' else None
        download(url, target_dir, cookie_file, proxy)
