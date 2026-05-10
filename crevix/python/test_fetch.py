import requests
import re
import json

url = "https://xhamster45.desi/photos/gallery/collaboration-16132067"
headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
}

try:
    response = requests.get(url, headers=headers)
    html = response.text
    print(f"HTML Length: {len(html)}")
    print(html[:500])
    
    # Look for any links containing /photos/gallery/
    photo_links = re.findall(r'href=["\']([^"\']*?/photos/gallery/\d+/\d+[^"\']*?)["\']', html)
    print(f"Found {len(set(photo_links))} unique photo links via broader regex")
    for link in list(set(photo_links))[:5]:
        print(link)

except Exception as e:
    print(f"Error: {e}")
