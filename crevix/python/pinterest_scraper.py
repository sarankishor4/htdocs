import json
import sys
import subprocess
import os

def emit(data):
    print("STREAM:" + json.dumps(data), flush=True)

def scan_pinterest(url, max_items=50):
    try:
        # Using yt-dlp to extract flat-playlist metadata from Pinterest
        # Adding --ignore-errors to handle 'No video formats' on photo pins
        cmd = [
            "yt-dlp",
            "--dump-json",
            "--flat-playlist",
            "--ignore-errors",
            "--playlist-end", str(max_items),
            url
        ]
        
        process = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        
        count = 0
        for line in process.stdout:
            try:
                entry = json.loads(line)
                if not entry: continue
                
                shortcode = entry.get('id', '')
                title = entry.get('title', 'Pinterest Pin')
                
                # Get the highest quality thumbnail as the original image
                thumb = entry.get('thumbnails', [{}])[-1].get('url', '')
                if not thumb: thumb = entry.get('url', '')
                
                emit({
                    'type': 'post',
                    'shortcode': shortcode,
                    'url': f"https://www.pinterest.com/pin/{shortcode}/",
                    'title': title,
                    'thumbnail': thumb,
                    'media_type': 'photo',
                    'is_carousel': False,
                    'uploader': 'Pinterest'
                })
                count += 1
            except:
                continue
                
        emit({'type': 'done', 'total': count})

    except Exception as e:
        emit({'type': 'error', 'message': f'Pinterest Scanner Error: {str(e)}'})

if __name__ == '__main__':
    url = sys.argv[1] if len(sys.argv) > 1 else ''
    max_items = int(sys.argv[2]) if len(sys.argv) > 2 else 50
    
    if not url:
        emit({'type': 'error', 'message': 'No Pinterest URL provided'})
    else:
        scan_pinterest(url, max_items)
