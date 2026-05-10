import json
import sys
import os
import http.cookiejar
import requests
import urllib.parse

def load_cookies(cookie_file):
    cookies = {}
    try:
        with open(cookie_file, 'r') as f:
            content = f.read().strip()
            if content.startswith('['):
                # JSON Format Detection
                data = json.loads(content)
                for c in data:
                    cookies[c['name']] = c['value']
            else:
                # Netscape Format Fallback
                cj = http.cookiejar.MozillaCookieJar()
                cj.load(cookie_file, ignore_discard=True, ignore_expires=True)
                for c in cj:
                    cookies[c.name] = c.value
    except Exception as e:
        pass
    return cookies

def emit(data):
    print("STREAM:" + json.dumps(data), flush=True)

import socket
import urllib3.util.connection as urllib3_cn

def allowed_gai_family():
    return socket.AF_INET # Force IPv4

urllib3_cn.allowed_gai_family = allowed_gai_family

def get_profile(username, cookie_file=None, max_posts=30, proxy=None):
    session = requests.Session()
    adapter = requests.adapters.HTTPAdapter(max_retries=3)
    session.mount('https://', adapter)
    
    if proxy:
        session.proxies = {'http': proxy, 'https': proxy}

    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': '*/*',
        'Accept-Language': 'en-US,en;q=0.9',
        'X-IG-App-ID': '936619743392459',
        'X-ASBD-ID': '129477',
        'X-IG-WWW-Claim': '0',
        'X-Requested-With': 'XMLHttpRequest',
        'Referer': 'https://www.instagram.com/',
        'Origin': 'https://www.instagram.com',
    })

    if cookie_file and os.path.exists(cookie_file):
        cookies = load_cookies(cookie_file)
        for name, val in cookies.items():
            session.cookies.set(name, val, domain='.instagram.com')
        if 'csrftoken' in cookies:
            session.headers['X-CSRFToken'] = cookies['csrftoken']

    try:
        # Step 1: Get user info
        resp = session.get(f'https://www.instagram.com/api/v1/users/web_profile_info/?username={username}')
        if resp.status_code != 200:
            emit({'type': 'error', 'message': f'Instagram returned {resp.status_code}. Cookies may be expired.'})
            return

        data = resp.json()
        user = data.get('data', {}).get('user', {})
        if not user:
            emit({'type': 'error', 'message': 'Profile not found.'})
            return

        user_id = user['id']

        emit({
            'type': 'profile',
            'username': user.get('username', username),
            'full_name': user.get('full_name', ''),
            'profile_pic': user.get('profile_pic_url_hd', user.get('profile_pic_url', '')),
            'followers': user.get('edge_followed_by', {}).get('count', 0),
            'is_private': user.get('is_private', False)
        })

        if user.get('is_private', False):
            emit({'type': 'error', 'message': 'This profile is private.'})
            return

        # Step 2: Fetch posts via Instagram v1 API (feed endpoint)
        count = 0
        end_cursor = None
        has_next = True

        while has_next and count < max_posts:
            # INCREASED BATCH SIZE: 50 instead of 12 for 4x faster scanning
            feed_url = f'https://www.instagram.com/api/v1/feed/user/{user_id}/?count=20'
            if end_cursor:
                feed_url += f'&max_id={end_cursor}'

            r = session.get(feed_url, timeout=15)
            if r.status_code != 200:
                break

            feed = r.json()
            items = feed.get('items', [])
            has_next = feed.get('more_available', False)
            end_cursor = feed.get('next_max_id', None)

            if not items:
                break

            for item in items:
                if count >= max_posts:
                    break

                shortcode = item.get('code', '')
                caption_obj = item.get('caption', {})
                caption = ''
                if caption_obj:
                    caption = (caption_obj.get('text', '') or '')[:100]
                caption = caption.replace('\n', ' ').replace('"', "'") if caption else f'Post by @{username}'

                media_type = item.get('media_type', 1)
                # 1=photo, 2=video, 8=carousel

                if media_type == 8:
                    # Carousel - keep as ONE post with all items inside
                    carousel = item.get('carousel_media', [])
                    carousel_items = []
                    first_thumb = ''
                    for child in carousel:
                        child_type = child.get('media_type', 1)
                        thumb = ''
                        if child.get('image_versions2', {}).get('candidates'):
                            thumb = child['image_versions2']['candidates'][0].get('url', '')
                        if not first_thumb:
                            first_thumb = thumb

                        vid_url = ''
                        if child_type == 2 and child.get('video_versions'):
                            vid_url = child['video_versions'][0].get('url', '')

                        carousel_items.append({
                            'thumbnail': thumb,
                            'media_type': 'video' if child_type == 2 else 'photo',
                            'video_url': vid_url
                        })

                    emit({
                        'type': 'post',
                        'shortcode': shortcode,
                        'url': f'https://www.instagram.com/p/{shortcode}/',
                        'title': caption,
                        'thumbnail': first_thumb,
                        'media_type': 'carousel',
                        'video_url': '',
                        'duration': 0,
                        'likes': item.get('like_count', 0),
                        'is_carousel': True,
                        'carousel_count': len(carousel_items),
                        'carousel_items': carousel_items,
                        'uploader': username
                    })
                    count += 1
                else:
                    # Single photo or video
                    thumb = ''
                    if item.get('image_versions2', {}).get('candidates'):
                        thumb = item['image_versions2']['candidates'][0].get('url', '')

                    vid_url = ''
                    duration = 0
                    if media_type == 2 and item.get('video_versions'):
                        vid_url = item['video_versions'][0].get('url', '')
                        duration = int(item.get('video_duration', 0))

                    emit({
                        'type': 'post',
                        'shortcode': shortcode,
                        'url': f'https://www.instagram.com/p/{shortcode}/',
                        'direct_url': vid_url or thumb,
                        'title': caption,
                        'thumbnail': thumb,
                        'media_type': 'video' if media_type == 2 else 'photo',
                        'duration': duration,
                        'likes': item.get('like_count', 0),
                        'is_carousel': False,
                        'uploader': username
                    })
                    count += 1

        emit({'type': 'done', 'total': count})

    except requests.exceptions.ConnectionError:
        emit({'type': 'error', 'message': 'DNS / Connection Error: Could not resolve Instagram. Please check your internet or proxy.'})
    except Exception as e:
        emit({'type': 'error', 'message': f'Scanner Error: {str(e)}'})

if __name__ == '__main__':
    username = sys.argv[1] if len(sys.argv) > 1 else ''
    cookie_file = sys.argv[2] if len(sys.argv) > 2 else None
    max_posts = int(sys.argv[3]) if len(sys.argv) > 3 else 5000
    proxy = sys.argv[4] if len(sys.argv) > 4 and sys.argv[4] != '""' else None

    if not username:
        emit({'type': 'error', 'message': 'No username provided'})
    else:
        get_profile(username, cookie_file, max_posts, proxy)
