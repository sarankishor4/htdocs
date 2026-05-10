import requests
import json
import mysql.connector

def restore_id_2():
    url = "https://i.pinimg.com/originals/c9/78/be/c978be763d582f3a6336e3c0423984a2.jpg"
    api_url = "http://localhost/crevix/cloud_bulk_api.php"
    
    print(f"--- RESTORING ID 2 FROM: {url} ---")
    
    try:
        # 1. Perform New Upload
        payload = {"url": url, "title": "Pinterest Masterpiece (Restored)", "type": "photo"}
        r = requests.post(api_url, json=payload)
        res = r.json()
        
        if res.get('status') == 'success':
            new_cloud_id = res['drive_id']
            new_acc_id = res['account'] # Actually account_email in current API, let's get ID from DB
            
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
                title = 'Pinterest Masterpiece (Restored)',
                is_offloaded = 1
                WHERE id = 2
            """, (new_cloud_id, acc_id, url))
            
            # Delete the NEW entry created by the API (to avoid duplicates)
            # The API just inserted a new row, we need to delete it and keep only ID 2
            cursor.execute("DELETE FROM media WHERE id != 2 AND cloud_id = %s", (new_cloud_id,))
            
            conn.commit()
            print(f"SUCCESS: ID 2 has been restored with Cloud ID: {new_cloud_id}")
            conn.close()
        else:
            print(f"FAILED: API Error - {res}")
            
    except Exception as e:
        print(f"FAILED: {str(e)}")

if __name__ == "__main__":
    restore_id_2()
