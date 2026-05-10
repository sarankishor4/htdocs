import requests
import mysql.connector
import time

def ingest_curated_xhamster_gallery():
    gallery_id = "16501078"
    gallery_url = f"https://xhamster.com/photos/gallery/flash-pussy-car-sex-pantyhose-skirt-shower-jeans-{gallery_id}"
    gallery_title = "Flash Pussy & Pantyhose Collection (Immersive)"
    
    # List of high-res photo IDs extracted by subagent
    photo_ids = [
        "519482144", "519482145", "519482146", "519482139", "519482138",
        "519482135", "519482134", "519482133", "519482132", "519482130",
        "519482129", "519482128", "519482140", "519482136", "519482142",
        "519482141", "519482117", "519482116", "519482110", "519482109",
        "519482108", "519482107", "519482106", "519482105"
    ]
    
    print(f"--- STARTING IMMERSIVE GEST: {gallery_title} ---")
    api_url = "http://localhost/crevix/cloud_bulk_api.php"
    
    success_count = 0
    for i, p_id in enumerate(photo_ids):
        # CDN pattern found by subagent
        # Note: We use the mirror domain if the CDN is blocked, 
        # but usually the CDN is more stable for direct streaming.
        # Mirror fallback: https://xhamster45.desi/photos/gallery/16501078/519482144 -> extract binary
        # Let's use the mirror's proxy to get the binary if possible or the direct CDN.
        
        # We'll use a direct source for the API to fetch
        img_url = f"https://ic-cdn.xhamster.com/galleries/{gallery_id}/{i+1}/1000.jpg"
        
        payload = {
            "url": img_url,
            "title": f"{gallery_title} - Slide {i+1}",
            "type": "photo"
        }
        
        try:
            r = requests.post(api_url, json=payload)
            res = r.json()
            if res.get('status') == 'success':
                # Link to the gallery URL for Instagram grouping
                conn = mysql.connector.connect(host='localhost', user='root', password='', database='crevix_db')
                cursor = conn.cursor()
                cursor.execute("UPDATE media SET original_url = %s WHERE cloud_id = %s", (gallery_url, res['drive_id']))
                conn.commit()
                conn.close()
                success_count += 1
                print(f"Slide {i+1}/{len(photo_ids)} Offloaded.")
            else:
                print(f"Slide {i+1} Failed: {res.get('error')}")
        except Exception as e:
            print(f"Slide {i+1} Error: {str(e)}")
        
        time.sleep(0.5) # Avoid rate limits

    print(f"--- IMMERSIVE INGEST COMPLETE: {success_count} Photos Live! ---")

if __name__ == "__main__":
    ingest_curated_xhamster_gallery()
