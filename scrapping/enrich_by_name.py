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

MAX_WORKERS = 15
SLEEP_MIN = 0.1
SLEEP_MAX = 0.5

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

# Global flag for column existence
HAS_POSTAL_CODE = False

# Connection Pool
try:
    db_pool = pooling.MySQLConnectionPool(
        pool_name="mypool",
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
    if HAS_POSTAL_CODE:
        logger.info("Column 'postal_code' is present.")
    else:
        logger.warning("Column 'postal_code' not found. It will be skipped for now.")

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
        cursor.execute(sql, ("enrich_by_name", company_id, identifier, status, message))
        conn.commit()
        cursor.close()
    except Exception as e:
        logger.error(f"Error writing to scraping_logs: {e}")

def process_company(company):
    company_id = company['id']
    name = company['company_name']
    
    conn = None
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT id FROM scraping_logs WHERE process_name = 'enrich_by_name' AND entity_id = %s AND status = 'success'", (company_id,))
        if cursor.fetchone():
            return

        query = urllib.parse.quote(name)
        url = f"{BASE_URL}?q={query}&CSRF={CSRF_TOKEN}"
        
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        response = requests.get(url, headers=get_headers(), timeout=10)
        
        if response.status_code == 200:
            try:
                data = response.json()
            except:
                return

            if data.get('empresas'):
                emp = data['empresas'][0]
                nif = emp.get('nif')
                provincia = emp.get('p')
                municipio = emp.get('loc')
                direccion = emp.get('dir')
                cp = emp.get('cp')
                
                if HAS_POSTAL_CODE:
                    update_sql = "UPDATE companies SET cif = %s, registro_mercantil = %s, municipality = %s, address = %s, postal_code = %s WHERE id = %s"
                    cursor.execute(update_sql, (nif, provincia, municipio, direccion, cp, company_id))
                else:
                    update_sql = "UPDATE companies SET cif = %s, registro_mercantil = %s, municipality = %s, address = %s WHERE id = %s"
                    cursor.execute(update_sql, (nif, provincia, municipio, direccion, company_id))
                
                conn.commit()
                log_to_db(conn, company_id, name, "success", f"Enriched: {nif}")
                logger.info(f"✅ SUCCESS: {name} -> {nif}")
            else:
                log_to_db(conn, company_id, name, "not_found", "No results")
                logger.info(f"❓ NOT FOUND: {name}")
        else:
            logger.error(f"❌ ERROR: HTTP {response.status_code} for {name}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION for {name}: {e}")
    finally:
        if conn:
            conn.close()

def main():
    logger.info("--- Starting Name Enrichment Process ---")
    check_columns()
    
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, company_name FROM companies WHERE cif IS NULL OR cif = '' LIMIT 2000")
        companies = cursor.fetchall()
        cursor.close()
        conn.close()
    except Exception as e:
        logger.error(f"Initial DB fetch failed: {e}")
        return
    
    if not companies:
        logger.info("No companies found.")
        return

    logger.info(f"Processing {len(companies)} records...")
    
    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        executor.map(process_company, companies)
    
    logger.info(f"--- Process Finished ---")

if __name__ == "__main__":
    main()
