import requests
import json
import os
import re

def audit_file():
    # 1. Get Token from DB
    import mysql.connector
    conn = mysql.connector.connect(host='localhost', user='root', password='', database='crevix_db')
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM cloud_accounts LIMIT 1")
    acc = cursor.fetchone()
    
    # 2. Get Config for Client ID/Secret
    config_text = open('google_config.php').read()
    client_id = re.search(r"define\('GOOGLE_CLIENT_ID', '(.*)'\);", config_text).group(1)
    client_secret = re.search(r"define\('GOOGLE_CLIENT_SECRET', '(.*)'\);", config_text).group(1)
    
    # 3. Refresh Token
    r = requests.post('https://oauth2.googleapis.com/token', data={
        'client_id': client_id,
        'client_secret': client_secret,
        'refresh_token': acc['refresh_token'],
        'grant_type': 'refresh_token'
    })
    token = r.json().get('access_token')
    
    # 4. Check File Size (Final Master Repaired File)
    drive_id = "1eJesHBb2noy6nZk3m7puq26HSfcfDoBX"
    r = requests.get(f'https://www.googleapis.com/drive/v3/files/{drive_id}?fields=size,name,mimeType,parents', 
                     headers={'Authorization': f'Bearer {token}'})
    
    print("GOOGLE_RESPONSE:" + json.dumps(r.json()))
    conn.close()

if __name__ == '__main__':
    audit_file()
