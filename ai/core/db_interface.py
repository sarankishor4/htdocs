import mysql.connector
import json

class Database:
    def __init__(self, config_path='core/db_config.json'):
        # In a real app, you'd load from config
        self.config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'ai_nexus'
        }
        self.conn = None

    def connect(self):
        try:
            self.conn = mysql.connector.connect(**self.config)
            return True
        except Exception as e:
            print(f"DB Error: {e}")
            return False

    def query(self, sql, params=None):
        if not self.conn: self.connect()
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(sql, params or ())
        result = cursor.fetchall()
        cursor.close()
        return result

    def execute(self, sql, params=None):
        if not self.conn: self.connect()
        cursor = self.conn.cursor()
        cursor.execute(sql, params or ())
        self.conn.commit()
        cursor.close()
        return True
