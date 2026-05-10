import json
import sys
import os
import http.cookiejar
import requests

cookie_file = sys.argv[1] if len(sys.argv) > 1 else 'python/instagram_cookies.txt'
username = 'sofia9__official'

session = requests.Session()
session.headers.update({
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'X-IG-App-ID': '936619743392459',
    'X-Requested-With': 'XMLHttpRequest',
    'Referer': 'https://www.instagram.com/',
})

cj = http.cookiejar.MozillaCookieJar()
cj.load(cookie_file, ignore_discard=True, ignore_expires=True)
for c in cj:
    session.cookies.set(c.name, c.value, domain='.instagram.com')
    if c.name == 'csrftoken':
        session.headers['X-CSRFToken'] = c.value

resp = session.get(f'https://www.instagram.com/api/v1/users/web_profile_info/?username={username}')
data = resp.json()

user = data.get('data', {}).get('user', {})
timeline = user.get('edge_owner_to_timeline_media', {})

print(f"Status: {resp.status_code}")
print(f"Username: {user.get('username')}")
print(f"Timeline edge count: {timeline.get('count', 'N/A')}")
print(f"Edges found: {len(timeline.get('edges', []))}")

# Check what keys are in user
media_keys = [k for k in user.keys() if 'media' in k.lower() or 'edge' in k.lower() or 'reel' in k.lower()]
print(f"Media-related keys: {media_keys}")

# Print first edge if exists
edges = timeline.get('edges', [])
if edges:
    print(f"First edge keys: {list(edges[0].get('node', {}).keys())}")
else:
    print("No edges in timeline. Checking full keys:")
    for k in sorted(user.keys()):
        v = user[k]
        if isinstance(v, dict) and 'edges' in v:
            print(f"  {k} -> {len(v.get('edges', []))} edges")
        elif isinstance(v, dict) and 'count' in v:
            print(f"  {k} -> count={v.get('count')}")
