import requests
import mysql.connector
from mysql.connector import pooling
from concurrent.futures import ThreadPoolExecutor
import time
import random
import json
import urllib.parse
import logging
import re
from datetime import datetime

import os
from dotenv import load_dotenv

# Load .env
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': os.getenv("DB_HOST", "217.61.210.127"),
    'user': os.getenv("DB_USER", "apiempresas_user"),
    'password': os.getenv("DB_PASS", "WONwyjpsmx3h3$@2"),
    'database': os.getenv("DB_NAME", "reseller3537_apiempresas")
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

def clean_name_for_search(name):
    if not name: return ""
    # 1. Remove anything in parentheses
    name = re.sub(r'\(.*?\)', '', name)
    # 2. Suffixes to remove for cleaner search
    suffixes = [
        r'\bSOCIEDAD LIMITADA UNIPERSONAL\b',
        r'\bSOCIEDAD ANONIMA UNIPERSONAL\b',
        r'\bSOCIEDAD LIMITADA\b',
        r'\bSOCIEDAD ANONIMA\b',
        r'\bS\.L\.U\.\b',
        r'\bS\.A\.U\.\b',
        r'\bS\.L\.\b',
        r'\bS\.A\.\b',
        r'\bSLU\b',
        r'\bSAU\b',
        r'\bSL\b',
        r'\bSA\b',
    ]
    for s in suffixes:
        name = re.sub(s, '', name, flags=re.IGNORECASE)
    return ' '.join(name.split()).strip()

def normalize_for_comparison(n):
    if not n: return ""
    n = n.lower()
    # Remove common suffixes again just in case
    n = clean_name_for_search(n).lower()
    # Remove non-alphanumeric
    n = re.sub(r'[^a-z0-9]', '', n)
    return n

def is_same_company(original, target):
    norm_orig = normalize_for_comparison(original)
    norm_target = normalize_for_comparison(target)
    if not norm_orig or not norm_target: return False
    # Check if one is contained in other (common for shortened names)
    return (norm_orig in norm_target) or (norm_target in norm_orig)

def log_to_db(conn, company_id, identifier, status, message):
    try:
        cursor = conn.cursor()
        sql = "INSERT INTO scraping_logs (process_name, entity_id, identifier, status, message) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, ("enrich_by_name", company_id, identifier, status, message))
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
    name = company['company_name']
    
    conn = None
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT id FROM scraping_logs WHERE process_name = 'enrich_by_name' AND entity_id = %s AND status = 'success'", (company_id,))
        if cursor.fetchone():
            return

        query_name = clean_name_for_search(name)
        if not query_name:
            query_name = name # Fallback

        query = urllib.parse.quote(query_name)
        url = f"{BASE_URL}?q={query}&CSRF={CSRF_TOKEN}"
        
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        response = requests.get(url, headers=get_headers(), timeout=25)
        
        if response.status_code == 200:
            try:
                data = response.json()
            except:
                return

            empresas = data.get('empresas', [])
            if not empresas:
                log_to_db(conn, company_id, name, "not_found", "No results")
                logger.info(f"❓ NOT FOUND: {name}")
                return

            # Find the best match
            best_emp = None
            for emp in empresas:
                # Based on API inspection, 'rs' is Razón Social
                result_name = emp.get('rs') or emp.get('n') or emp.get('nombre') 
                if not result_name: continue
                
                if is_same_company(name, result_name):
                    best_emp = emp
                    break
            
            if not best_emp:
                log_to_db(conn, company_id, name, "not_found", f"No matching result among {len(empresas)} options")
                logger.info(f"❓ NO MATCH: {name} (found {len(empresas)} results but none matched)")
                return

            emp = best_emp
            nif = emp.get('nif')
            provincia = emp.get('p') or emp.get('r') # 'r' often contains the province/registry
            municipio = emp.get('loc')
            direccion = emp.get('dir')
            cp = emp.get('cp')
                
            # SAFETY CHECK: Only update if empty
            new_data = {
                'cif': nif,
                'registro_mercantil': provincia,
                'municipality': municipio,
                'address': direccion
            }
            if HAS_POSTAL_CODE:
                new_data['postal_code'] = cp

            updates = []
            params = []
            # Check current data before update
            cursor.execute("SELECT cif, registro_mercantil, municipality, address, postal_code FROM companies WHERE id = %s", (company_id,))
            current = cursor.fetchone()

            for field, val in new_data.items():
                if val:
                    curr_val = current.get(field)
                    if curr_val is None or (isinstance(curr_val, str) and curr_val.strip() == ''):
                        updates.append(f"{field} = %s")
                        params.append(val)

            if updates:
                updated_fields = [u.split(' =')[0] for u in updates]
                params.append(company_id)
                update_sql = f"UPDATE companies SET {', '.join(updates)} WHERE id = %s"
                cursor.execute(update_sql, tuple(params))
                conn.commit()
                log_to_db(conn, company_id, name, "success", f"Enriched: {', '.join(updated_fields)}")
                logger.info(f"✅ SUCCESS: {name} -> New Fields: {', '.join(updated_fields)}")
            else:
                log_to_db(conn, company_id, name, "already_filled", "Data already exists")
                logger.info(f"ℹ️ ALREADY FILLED: {name}")

        elif response.status_code == 503:
            logger.warning(f"⚠️ 503 Service Unavailable for {name}. Cooling down for 5 minutes.")
            GLOBAL_COOLDOWN_UNTIL = time.time() + 300
        else:
            logger.error(f"❌ ERROR: HTTP {response.status_code} for {name}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION for {name}: {e}")
    finally:
        if conn:
            conn.close()

def main():
    logger.info("--- Starting Name Enrichment (Continuous Batch Mode) ---")
    check_columns()
    
    while True:
        try:
            conn = db_pool.get_connection()
            cursor = conn.cursor(dictionary=True)
            
            # SMART QUERY:
            # 1. Focus on companies without CIF
            # 2. Exclude already SUCCEEDED in this process
            # 3. Exclude NOT_FOUND in the last 30 days
            sql = """
                SELECT c.id, c.company_name FROM companies c
                LEFT JOIN scraping_logs l ON c.id = l.entity_id 
                    AND l.process_name = 'enrich_by_name' 
                    AND (l.status IN ('success', 'already_filled') OR (l.status = 'not_found' AND l.created_at > NOW() - INTERVAL 30 DAY))
                WHERE (c.cif IS NULL OR c.cif = '') 
                AND c.company_name IS NOT NULL AND c.company_name != ''
                AND l.id IS NULL
                ORDER BY c.id DESC
                LIMIT 2000
            """
            cursor.execute(sql)
            companies = cursor.fetchall()
            cursor.close()
            conn.close()
            
            if not companies:
                logger.info("No records left to process. Sleeping 10 minutes...")
                time.sleep(600)
                continue

            logger.info(f"🚀 Processing new batch of {len(companies)} records...")
            
            with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
                executor.map(process_company, companies)
            
            logger.info("✅ Batch finished. Starting next one...")
            time.sleep(5) # Delay to avoid hammering
            
        except Exception as e:
            logger.error(f"💥 Main Loop Error: {e}")
            time.sleep(60)

if __name__ == "__main__":
    main()
