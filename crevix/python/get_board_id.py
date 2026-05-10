import requests
import json
import re
import os

def find_board_id():
    cookie_file = 'python/pinterest_cookies.txt'
    cookies = {}
    if os.path.exists(cookie_file):
        with open(cookie_file, 'r') as f:
            data = json.load(f)
            for c in data:
                cookies[c['name']] = c['value']
    
    headers = {'User-Agent': 'Mozilla/5.0'}
    r = requests.get('https://in.pinterest.com/kishorbabal/hottie/', headers=headers, cookies=cookies)
    
    # Try to find board_id
    match = re.search(r'"board_id":"(\d+)"', r.text)
    if match:
        print(f"FOUND_ID:{match.group(1)}")
    else:
        print("NOT_FOUND")
        # Log a snippet of the HTML for debugging
        with open('python/pinterest_debug.html', 'w', encoding='utf-8') as f:
            f.write(r.text)

if __name__ == '__main__':
    find_board_id()
