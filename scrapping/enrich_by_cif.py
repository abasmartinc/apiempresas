import requests
import mysql.connector
from mysql.connector import pooling
from concurrent.futures import ThreadPoolExecutor
import time
import random
import json
import urllib.parse
import logging
from datetime import datetime

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': '217.61.210.127',
    'user': 'apiempresas_user',
    'password': 'WONwyjpsmx3h3$@2',
    'database': 'reseller3537_apiempresas'
}

CSRF_TOKEN = "EDD5814A8797230F6ED48C03E35D2F932D4FD18591943DBE"
BASE_URL = "https://infonif.economia3.com/api/buscador/buscar.asp"

MAX_WORKERS = 5
SLEEP_MIN = 1.0
SLEEP_MAX = 3.0

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0"
]

# --- LOGGING SETUP ---
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] Thread-%(thread)d: %(message)s',
    datefmt='%H:%M:%S'
)
logger = logging.getLogger(__name__)

# Global flag
HAS_POSTAL_CODE = False

# Connection Pool
try:
    db_pool = pooling.MySQLConnectionPool(
        pool_name="cifpool",
        pool_size=MAX_WORKERS + 2,
        **DB_CONFIG
    )
    logger.info("Database connection pool initialized.")
except Exception as e:
    logger.error(f"Failed to initialize DB pool: {e}")
    exit(1)

def check_columns():
    global HAS_POSTAL_CODE
    conn = db_pool.get_connection()
    cursor = conn.cursor()
    cursor.execute("SHOW COLUMNS FROM companies LIKE 'postal_code'")
    HAS_POSTAL_CODE = cursor.fetchone() is not None
    cursor.close()
    conn.close()

def get_headers():
    return {
        "User-Agent": random.choice(USER_AGENTS),
        "Accept": "application/json, text/javascript, */*; q=0.01",
        "X-Requested-With": "XMLHttpRequest",
        "Referer": "https://infonif.economia3.com/"
    }

def log_to_db(conn, company_id, identifier, status, message):
    try:
        cursor = conn.cursor()
        sql = "INSERT INTO scraping_logs (process_name, entity_id, identifier, status, message) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, ("enrich_by_cif", company_id, identifier, status, message))
        conn.commit()
        cursor.close()
    except Exception as e:
        logger.error(f"Error writing to scraping_logs: {e}")

# Global cooldown control
GLOBAL_COOLDOWN_UNTIL = 0

def process_company(company):
    global GLOBAL_COOLDOWN_UNTIL
    
    if time.time() < GLOBAL_COOLDOWN_UNTIL:
        return

    company_id = company['id']
    cif = company['cif']
    
    conn = None
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Double check data is still missing (in case another thread filled it)
        cols = ["registro_mercantil", "municipality", "address"]
        if HAS_POSTAL_CODE:
            cols.append("postal_code")
        
        cursor.execute(f"SELECT {', '.join(cols)} FROM companies WHERE id = %s", (company_id,))
        current = cursor.fetchone()

        query = urllib.parse.quote(cif)
        url = f"{BASE_URL}?q={query}&CSRF={CSRF_TOKEN}"
        
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        response = requests.get(url, headers=get_headers(), timeout=25)
        
        if response.status_code == 200:
            try:
                data = response.json()
            except:
                return

            if data.get('empresas'):
                emp = data['empresas'][0]
                new_data = {
                    'registro_mercantil': emp.get('p'),
                    'municipality': emp.get('loc'),
                    'address': emp.get('dir')
                }
                if HAS_POSTAL_CODE:
                    new_data['postal_code'] = emp.get('cp')
                
                updates = []
                params = []
                for field, val in new_data.items():
                    if val and (not current.get(field) or current.get(field).strip() == ''):
                        updates.append(f"{field} = %s")
                        params.append(val)
                
                if updates:
                    updated_fields = [u.split(' =')[0] for u in updates]
                    params.append(company_id)
                    update_sql = f"UPDATE companies SET {', '.join(updates)} WHERE id = %s"
                    cursor.execute(update_sql, tuple(params))
                    conn.commit()
                    log_to_db(conn, company_id, cif, "success", f"Updated: {', '.join(updated_fields)}")
                    logger.info(f"✅ SUCCESS CIF: {cif} -> New Fields: {', '.join(updated_fields)}")
                else:
                    log_to_db(conn, company_id, cif, "no_update_needed", "Already complete")
                    logger.info(f"ℹ️ NO UPDATE: {cif}")
            else:
                log_to_db(conn, company_id, cif, "not_found", "No results")
                logger.info(f"❓ NOT FOUND CIF: {cif}")
        elif response.status_code == 503:
            logger.warning(f"⚠️ 503 Service Unavailable for CIF {cif}. Cooling down for 5 minutes.")
            GLOBAL_COOLDOWN_UNTIL = time.time() + 300
        else:
            logger.error(f"❌ ERROR: HTTP {response.status_code} for CIF {cif}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION for CIF {cif}: {e}")
    finally:
        if conn:
            conn.close()

def main():
    logger.info("--- Starting CIF Enrichment (Continuous Batch Mode) ---")
    check_columns()
    
    while True:
        try:
            conn = db_pool.get_connection()
            cursor = conn.cursor(dictionary=True)
            
            where_parts = [
                "(registro_mercantil IS NULL OR registro_mercantil = '')",
                "(municipality IS NULL OR municipality = '')",
                "(address IS NULL OR address = '')"
            ]
            if HAS_POSTAL_CODE:
                where_parts.append("(postal_code IS NULL OR postal_code = '')")
            
            where_clause = " OR ".join(where_parts)
            
            # SMART QUERY: 
            # 1. Focus on societies (Starts with letter)
            # 2. Exclude already SUCCEEDED in this process
            # 3. Exclude NOT_FOUND in the last 30 days
            sql = f"""
                SELECT c.id, c.cif FROM companies c
                LEFT JOIN scraping_logs l ON c.id = l.entity_id 
                    AND l.process_name = 'enrich_by_cif' 
                    AND (l.status = 'success' OR (l.status = 'not_found' AND l.created_at > NOW() - INTERVAL 30 DAY))
                WHERE c.cif REGEXP '^[A-Z]' 
                AND ({where_clause}) 
                AND l.id IS NULL
                LIMIT 5000
            """
            cursor.execute(sql)
            companies = cursor.fetchall()
            cursor.close()
            conn.close()
            
            if not companies:
                logger.info("No records left to process. Sleeping 5 minutes...")
                time.sleep(300)
                continue

            logger.info(f"🚀 Processing new batch of {len(companies)} records...")
            
            with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
                executor.map(process_company, companies)
            
            logger.info("✅ Batch finished. Starting next one...")
            time.sleep(5) # Delay to avoid hammering the DB/CPU
            
        except Exception as e:
            logger.error(f"💥 Main Loop Error: {e}")
            time.sleep(60) # Wait before retry

if __name__ == "__main__":
    main()
