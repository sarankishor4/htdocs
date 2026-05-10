import sys
import requests
import os
import time

def download_file(url, target_path, referer, cookies_str=""):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Referer': referer,
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
        'Connection': 'keep-alive'
    }
    
    if cookies_str:
        headers['Cookie'] = cookies_str
    
    try:
        print(f"Python: Attempting to download {url}")
        with requests.get(url, headers=headers, stream=True, verify=False, timeout=60) as r:
            r.raise_for_status()
            with open(target_path, 'wb') as f:
                for chunk in r.iter_content(chunk_size=8192):
                    f.write(chunk)
        print(f"Python: Successfully downloaded to {target_path}")
        return True
    except Exception as e:
        print(f"Python Error: {str(e)}")
        return False

if __name__ == "__main__":
    # Usage: python processor.py <url> <target_path> <referer> [cookies]
    if len(sys.argv) > 3:
        url = sys.argv[1]
        target = sys.argv[2]
        referer = sys.argv[3]
        cookies = sys.argv[4] if len(sys.argv) > 4 else ""
        download_file(url, target, referer, cookies)
    else:
        print("Python: Not enough arguments for download.")
