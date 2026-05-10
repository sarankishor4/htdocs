import requests
import json
import mysql.connector

def restore_id_2_final():
    # Use a confirmed working high-res source
    url = "https://images.unsplash.com/photo-1575936123452-b67c3203c357?fm=jpg&q=80&w=1080"
    api_url = "http://localhost/crevix/cloud_bulk_api.php"
    
    print(f"--- FINAL RESTORATION ID 2 FROM: {url} ---")
    
    try:
        # 1. Perform New Upload
        payload = {"url": url, "title": "Cinematic Vision (Restored ID 2)", "type": "photo"}
        r = requests.post(api_url, json=payload)
        res = r.json()
        
        if res.get('status') == 'success':
            new_cloud_id = res['drive_id']
            
            # 2. Update existing ID 2
            conn = mysql.connector.connect(host='localhost', user='root', password='', database='crevix_db')
            cursor = conn.cursor()
            
            # Find account ID by email
            cursor.execute("SELECT id FROM cloud_accounts WHERE account_email = %s", (res['account'],))
            acc_id = cursor.fetchone()[0]
            
            cursor.execute("""
                UPDATE media SET 
                cloud_id = %s, 
                cloud_account_id = %s,
                original_url = %s,
                title = 'Cinematic Vision (Restored ID 2)',
                is_offloaded = 1
                WHERE id = 2
            """, (new_cloud_id, acc_id, url))
            
            # Delete the NEW entry created by the API
            cursor.execute("DELETE FROM media WHERE id != 2 AND cloud_id = %s", (new_cloud_id,))
            
            conn.commit()
            print(f"SUCCESS: ID 2 has been restored with Cloud ID: {new_cloud_id}")
            conn.close()
        else:
            print(f"FAILED: API Error - {res}")
            
    except Exception as e:
        print(f"FAILED: {str(e)}")

if __name__ == "__main__":
    restore_id_2_final()
