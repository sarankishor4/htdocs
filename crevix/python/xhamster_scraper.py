import json
import sys
import subprocess
import os
import requests
import re

def emit(data):
    print("STREAM:" + json.dumps(data), flush=True)

def scan_xhamster(url, max_items=50):
    try:
        # Normalize URL to xhamster.com (mirrors like xhamster45.desi often block scrapers)
        url = url.replace('xhamster45.desi', 'xhamster.com')
        
        # Detect if it's a gallery or video/profile
        is_gallery = "/photos/gallery/" in url
        
        if is_gallery:
            # Try to get data via requests first (it's often faster and more reliable for galleries than yt-dlp)
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer': 'https://xhamster.com/'
            }
            try:
                resp = requests.get(url, headers=headers, timeout=15)
                if resp.status_code == 200:
                    html = resp.text
                    # Look for initialState JSON
                    match = re.search(r'window\.initialState\s*=\s*(\{.*?\});', html)
                    if match:
                        data = json.loads(match.group(1))
                        # In the new xHamster layout, photos are often in data['photos']['models'] or data['gallery']['photos']
                        photos = []
                        if 'photos' in data and 'models' in data['photos']:
                            photos = data['photos']['models']
                        elif 'gallery' in data and 'photos' in data['gallery']:
                            photos = data['gallery']['photos']
                        
                        if photos:
                            count = 0
                            for p in photos:
                                if count >= max_items: break
                                # Extract high res URL
                                # Often it's in p['imageURL'] or p['sources']['standard']
                                img_url = p.get('imageURL') or p.get('url')
                                if not img_url and 'sources' in p:
                                    img_url = p['sources'].get('standard') or p['sources'].get('huge')
                                
                                if img_url:
                                    emit({
                                        'type': 'post',
                                        'shortcode': str(p.get('id', '')),
                                        'url': url + '/' + str(p.get('id', '')),
                                        'parent_url': url,
                                        'title': p.get('title') or f"Photo {p.get('id', count+1)}",
                                        'thumbnail': img_url,
                                        'media_type': 'photo',
                                        'uploader': 'xHamster',
                                        'is_carousel': False
                                    })
                                    count += 1
                            emit({'type': 'done', 'total': count})
                            return
            except Exception as e:
                # If requests fails, fall back to yt-dlp
                pass

            # 1. Use yt-dlp to get all image pages from the gallery
            try:
                cmd = ['yt-dlp', '--flat-playlist', '--dump-single-json', url]
                result = subprocess.check_output(cmd).decode()
                data = json.loads(result)
                
                entries = data.get('entries', [])
                gallery_title = data.get('title', 'xHamster Gallery')
                uploader = data.get('uploader', 'xHamster')
                
                count = 0
                for entry in entries:
                    if count >= max_items: break
                    
                    photo_page_url = entry.get('url')
                    if not photo_page_url: continue
                    
                    # Get the direct high-res image link
                    try:
                        # -g gets the direct URL
                        img_link = subprocess.check_output(['yt-dlp', '-g', photo_page_url]).decode().strip()
                        if img_link:
                            emit({
                                'type': 'post',
                                'shortcode': entry.get('id', ''),
                                'url': photo_page_url,
                                'parent_url': url, # The gallery URL
                                'title': f"{gallery_title} - Item {count+1}",
                                'thumbnail': img_link,
                                'media_type': 'photo',
                                'uploader': uploader,
                                'is_carousel': False
                            })
                            count += 1
                    except:
                        continue
                emit({'type': 'done', 'total': count})
                return
            except Exception as e:
                emit({'type': 'error', 'message': f'xHamster Gallery Error: {str(e)}'})
                return
        else:
            # Standard video/profile scan
            cmd = [
                "yt-dlp",
                "--dump-json",
                "--flat-playlist",
                "--playlist-end", str(max_items),
                "--no-warnings",
                url
            ]
            
            process = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
            
            count = 0
            for line in process.stdout:
                try:
                    entry = json.loads(line)
                    if not entry: continue
                    
                    shortcode = entry.get('id', '')
                    title = entry.get('title', 'xHamster Video')
                    thumb = entry.get('thumbnail', '')
                    uploader = entry.get('uploader', 'xHamster')
                    duration = entry.get('duration_string', '0:00')
                    
                    emit({
                        'type': 'post',
                        'shortcode': shortcode,
                        'url': entry.get('webpage_url', url),
                        'title': title,
                        'thumbnail': thumb,
                        'media_type': 'video',
                        'uploader': uploader,
                        'duration': duration,
                        'is_carousel': False
                    })
                    count += 1
                except:
                    continue
                
        emit({'type': 'done', 'total': count})

    except Exception as e:
        emit({'type': 'error', 'message': f'xHamster Scanner Error: {str(e)}'})

if __name__ == '__main__':
    if len(sys.argv) < 2:
        emit({'type': 'error', 'message': 'No xHamster URL provided'})
        sys.exit(1)
        
    url = sys.argv[1]
    max_items = int(sys.argv[2]) if len(sys.argv) > 2 else 20
    scan_xhamster(url, max_items)
