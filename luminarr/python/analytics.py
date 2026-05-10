import mysql.connector
import json
import sys
from datetime import datetime

def get_analytics():
    try:
        # Configuration
        db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'luminarr_db'
        }

        # Connect
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        # 1. Total Sales by Day
        cursor.execute("""
            SELECT DATE(created_at) as date, SUM(total) as revenue 
            FROM orders 
            WHERE status = 'delivered' 
            GROUP BY DATE(created_at) 
            ORDER BY date DESC LIMIT 7
        """)
        sales_history = cursor.fetchall()

        # 2. Top Selling Products
        cursor.execute("""
            SELECT p.name, SUM(oi.quantity) as total_sold
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            GROUP BY oi.product_id
            ORDER BY total_sold DESC LIMIT 5
        """)
        top_products = cursor.fetchall()

        # 3. Category Distribution
        cursor.execute("""
            SELECT c.name, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
        """)
        categories = cursor.fetchall()

        analytics = {
            'sales_history': sales_history,
            'top_products': top_products,
            'category_distribution': categories,
            'generated_at': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }

        print(json.dumps(analytics, default=str))

        cursor.close()
        conn.close()

    except Exception as e:
        print(json.dumps({'error': str(e)}))

if __name__ == "__main__":
    get_analytics()
