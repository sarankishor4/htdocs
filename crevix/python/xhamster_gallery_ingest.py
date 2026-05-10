import requests
import json
import re
import subprocess
import mysql.connector

def fetch_xhamster_gallery(gallery_url):
    print(f"--- STARTING XHAMSTER GALLERY FETCH: {gallery_url} ---")
    
    try:
        # 1. Use yt-dlp to get all image links from the gallery
        # We use --flat-playlist and --dump-single-json
        cmd = ['yt-dlp', '--flat-playlist', '--dump-single-json', gallery_url]
        result = subprocess.check_output(cmd).decode()
        data = json.loads(result)
        
        # In xHamster galleries, entries often contain the individual photo pages
        entries = data.get('entries', [])
        print(f"Found {len(entries)} potential photo pages.")
        
        image_urls = []
        for entry in entries:
            photo_page_url = entry.get('url')
            if photo_page_url:
                # Extract the direct high-res image link from each photo page
                # We can use yt-dlp -g for each page
                try:
                    img_link = subprocess.check_output(['yt-dlp', '-g', photo_page_url]).decode().strip()
                    if img_link:
                        image_urls.append(img_link)
                        print(f"Captured Image: {len(image_urls)}")
                except:
                    continue
        
        if not image_urls:
            print("FAILED: No images found.")
            return

        # 2. Feed to Cloud Bulk API
        api_url = "http://localhost/crevix/cloud_bulk_api.php"
        gallery_title = data.get('title', 'xHamster Gallery')
        
        success_count = 0
        for i, img_url in enumerate(image_urls):
            payload = {
                "url": img_url,
                "title": f"{gallery_title} - Item {i+1}",
                "type": "photo"
            }
            # Add original_url to grouping (we'll modify cloud_bulk_api to handle this or update DB after)
            r = requests.post(api_url, json=payload)
            if r.status_code == 200:
                res = r.json()
                if res.get('status') == 'success':
                    # Update the DB to link it to the gallery URL for Instagram-style grouping
                    conn = mysql.connector.connect(host='localhost', user='root', password='', database='crevix_db')
                    cursor = conn.cursor()
                    cursor.execute("UPDATE media SET original_url = %s WHERE cloud_id = %s", (gallery_url, res['drive_id']))
                    conn.commit()
                    conn.close()
                    success_count += 1
                    print(f"Offloaded to Cloud: {success_count}/{len(image_urls)}")

        print(f"--- GALLERY INGEST COMPLETE: {success_count} Images Offloaded ---")

    except Exception as e:
        print(f"FAILED: {str(e)}")

if __name__ == "__main__":
    fetch_xhamster_gallery("https://xhamster45.desi/photos/gallery/sexy-blonde-to-nude-in-doggy-style-16137865")
