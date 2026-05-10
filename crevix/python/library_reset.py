import mysql.connector
import json

def reset_database():
    try:
        conn = mysql.connector.connect(host='localhost', user='root', password='', database='crevix_db')
        cursor = conn.cursor(dictionary=True)
        
        # 1. Capture the Master Video
        cursor.execute("SELECT * FROM media WHERE cloud_id = '1eJesHBb2noy6nZk3m7puq26HSfcfDoBX'")
        video = cursor.fetchone()
        
        if not video:
            print("ERROR: Could not find the master video in the database.")
            return

        # 2. Reset the Table
        cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
        cursor.execute("TRUNCATE TABLE media")
        cursor.execute("TRUNCATE TABLE comments")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
        
        # 3. Re-insert as ID 1
        # Remove ID from the dictionary to let auto-increment handle it or set it manually
        del video['id'] 
        
        columns = ', '.join(video.keys())
        placeholders = ', '.join(['%s'] * len(video))
        sql = f"INSERT INTO media ({columns}) VALUES ({placeholders})"
        
        cursor.execute(sql, list(video.values()))
        conn.commit()
        
        print(f"SUCCESS: Database reset. '{video['title']}' is now Entry #1.")
        conn.close()

    except Exception as e:
        print(f"FAILED: {str(e)}")

if __name__ == '__main__':
    reset_database()
