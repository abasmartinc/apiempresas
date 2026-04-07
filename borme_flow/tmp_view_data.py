from config import mysql_connect

def run():
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            cur.execute("SELECT description FROM borme_posts WHERE act_types LIKE '%Constitu%' LIMIT 10")
            rows = cur.fetchall()
            for row in rows:
                print("-" * 50)
                print(row['description'])
    finally:
        conn.close()

if __name__ == "__main__":
    run()
