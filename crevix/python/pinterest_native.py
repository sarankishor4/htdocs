import requests
import json
import sys
import re
import os

def emit(data):
    print("STREAM:" + json.dumps(data), flush=True)

def scrape_board_native(url, max_items=50):
    cookie_file = 'python/pinterest_cookies.txt'
    cookies = {}
    if os.path.exists(cookie_file):
        try:
            with open(cookie_file, 'r') as f:
                content = f.read().strip()
                if content.startswith('['): # JSON format
                    for c in json.loads(content):
                        cookies[c['name']] = c['value']
                else: # Netscape placeholder or format
                    # Basic parsing for netscape if needed, or just skip
                    pass
        except: pass

    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
    
    try:
        # Step 1: Try to get Board ID if not provided
        board_id = "882283520658189897" # Pre-discovered for Hottie
        
        # Step 2: Use Pinterest Resource API with cookies
        params = {
            'source_url': url,
            'data': json.dumps({
                'options': {
                    'board_id': board_id,
                    'page_size': 25,
                    'bookmarks': []
                },
                'context': {}
            })
        }
        
        api_url = 'https://www.pinterest.com/resource/BoardFeedResource/get/'
        # Harmonize cookies for www.pinterest.com
        www_cookies = {k: v for k, v in cookies.items()}
        
        resp = requests.get(api_url, params=params, headers=headers, cookies=www_cookies)
        
        if resp.status_code == 200 and 'application/json' in resp.headers.get('Content-Type', ''):
            data = resp.json()
            pins = data.get('resource_response', {}).get('data', [])
            count = 0
            for pin in pins:
                if count >= max_items: break
                p_id = pin.get('id')
                images = pin.get('images', {})
                img_url = images.get('orig', {}).get('url') or images.get('736x', {}).get('url', '')
                if p_id and img_url:
                    emit({
                        'type': 'post',
                        'shortcode': p_id,
                        'url': f'https://www.pinterest.com/pin/{p_id}/',
                        'title': pin.get('title') or pin.get('description') or f'Pin {p_id}',
                        'thumbnail': img_url,
                        'media_type': 'photo',
                        'uploader': 'Pinterest'
                    })
                    count += 1
            if count > 0:
                emit({'type': 'done', 'total': count})
                return

        # Fallback to Deep Harvest if API fails
        html = requests.get(url, headers=headers, cookies=cookies).text
        pins_data = re.findall(r'(https://i\.pinimg\.com/[^/]+/([^/]+)\.jpg)', html)
        count = 0
        seen = set()
        for img_url, p_id in pins_data:
            if img_url in seen: continue
            if count >= max_items: break
            seen.add(img_url)
            emit({
                'type': 'post',
                'shortcode': p_id,
                'url': f"https://www.pinterest.com/pin/{p_id}/",
                'title': f'Pin {p_id}',
                'thumbnail': img_url,
                'media_type': 'photo',
                'uploader': 'Pinterest'
            })
            count += 1
        
        if count > 0:
            emit({'type': 'done', 'total': count})
        else:
            emit({'type': 'error', 'message': 'No pins found. Board might be empty or session expired.'})

    except Exception as e:
        emit({'type': 'error', 'message': f'Native Scraper Error: {str(e)}'})

if __name__ == '__main__':
    url = sys.argv[1] if len(sys.argv) > 1 else ''
    max_items = int(sys.argv[2]) if len(sys.argv) > 2 else 50
    scrape_board_native(url, max_items)
