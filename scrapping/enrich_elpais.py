import requests
from bs4 import BeautifulSoup
import mysql.connector
from mysql.connector import pooling
from concurrent.futures import ThreadPoolExecutor
import time
import random
import re
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

BASE_SEARCH_URL = "https://cincodias.elpais.com/directorio-empresas/results/"
BASE_DOMAIN = "https://cincodias.elpais.com"

# Stealth Settings
MAX_WORKERS = 3 
SLEEP_MIN = 2.0
SLEEP_MAX = 5.0

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
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
        pool_name="elpaispool",
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
    ua = random.choice(USER_AGENTS)
    return {
        "User-Agent": ua,
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
        "Accept-Language": "es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
        "Accept-Encoding": "gzip, deflate, br",
        "Connection": "keep-alive",
        "Upgrade-Insecure-Requests": "1"
    }

def log_to_db(conn, company_id, identifier, status, message):
    try:
        cursor = conn.cursor()
        sql = "INSERT INTO scraping_logs (process_name, entity_id, identifier, status, message) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, ("enrich_elpais", company_id, identifier, status, message))
        conn.commit()
        cursor.close()
    except Exception as e:
        logger.error(f"Error writing to scraping_logs: {e}")

def parse_address_info(address_text):
    zip_match = re.search(r'(\d{5})', address_text)
    zip_code = zip_match.group(1) if zip_match else None
    munic_match = re.search(r', (.*?) \((.*?)\)', address_text)
    municipality = munic_match.group(1) if munic_match else None
    province = munic_match.group(2) if munic_match else None
    if zip_code:
        address_part = address_text.split(zip_code)[0].strip()
    else:
        address_part = address_text.split(',')[0].strip()
    return address_part, zip_code, municipality, province

def process_company(company):
    company_id = company['id']
    cif = company['cif']
    conn = None
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Double check data
        cols = ["registro_mercantil", "municipality", "address", "objeto_social", "cnae_code"]
        if HAS_POSTAL_CODE: cols.append("postal_code")
        cursor.execute(f"SELECT {', '.join(cols)} FROM companies WHERE id = %s", (company_id,))
        current = cursor.fetchone()

        search_url = f"{BASE_SEARCH_URL}{cif}"
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        session = requests.Session()
        session.headers.update(get_headers())
        response = session.get(search_url, timeout=15)
        
        if response.status_code == 403:
            logger.error(f"Blocked (403) for {cif}. Cooling down...")
            time.sleep(20)
            return
            
        if response.status_code != 200: return
            
        soup = BeautifulSoup(response.text, 'html.parser')
        company_link_tag = soup.find('a', href=re.compile(r'^/directorio-empresas/empresa/'))
        
        if not company_link_tag:
            log_to_db(conn, company_id, cif, "not_found", "No link found")
            logger.info(f"❓ NOT FOUND: {cif}")
            return
            
        detail_url = BASE_DOMAIN + company_link_tag['href']
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        detail_response = session.get(detail_url, timeout=15)
        if detail_response.status_code != 200: return
            
        detail_soup = BeautifulSoup(detail_response.text, 'html.parser')
        data = {}
        rows = detail_soup.find_all('li', class_='MuiListItem-root')
        for row in rows:
            label_tag = row.find('h3')
            if not label_tag: continue
            label = label_tag.get_text().strip()
            value_div = row.find('div', class_='MuiGrid-grid-xs-7') or row.find('div', class_='MuiGrid-grid-xs-6')
            if not value_div: continue
            value = value_div.get_text().strip()
            if "RAZÓN SOCIAL" in label: data['name'] = value
            elif "DOMICILIO" in label: data['address_raw'] = value
            elif "ACTIVIDAD CNAE" in label:
                cnae_match = re.match(r'(\d{4})\s+(.*)', value)
                if cnae_match:
                    data['cnae_code'] = cnae_match.group(1)
                    data['cnae_label'] = cnae_match.group(2).split('INFORME COMERCIAL')[0].strip()
            elif "OBJETO SOCIAL" in label: data['objeto_social'] = value
            elif "WEB" in label and value != "-": data['url'] = value

        if data:
            if 'address_raw' in data:
                addr, zip_code, city, prov = parse_address_info(data['address_raw'])
                data['address'] = addr
                data['zip'] = zip_code
                data['municipality'] = city
                data['province'] = prov

            updates = []
            params = []
            field_map = {'cnae_code': 'cnae_code', 'cnae_label': 'cnae_label', 'address': 'address', 
                         'municipality': 'municipality', 'province': 'registro_mercantil', 
                         'objeto_social': 'objeto_social', 'url': 'url'}
            if HAS_POSTAL_CODE: field_map['zip'] = 'postal_code'

            for f_key, db_col in field_map.items():
                val = data.get(f_key)
                if val and (not current.get(db_col) or str(current.get(db_col)).strip() == ''):
                    updates.append(f"{db_col} = %s")
                    params.append(val)

            if updates:
                params.append(company_id)
                cursor.execute(f"UPDATE companies SET {', '.join(updates)} WHERE id = %s", tuple(params))
                conn.commit()
                log_to_db(conn, company_id, cif, "success", "Enriched")
                logger.info(f"✅ SUCCESS: {cif}")
            else:
                log_to_db(conn, company_id, cif, "no_update_needed", "Already complete")
                logger.info(f"ℹ️ NO UPDATE: {cif}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION {cif}: {e}")
    finally:
        if conn: conn.close()

def main():
    logger.info("--- Starting El Pais Enrichment (Smart Filtering) ---")
    check_columns()
    try:
        conn = db_pool.get_connection()
        cursor = conn.cursor(dictionary=True)
        # Filter: missing object/cnae, start with letter, exclude success/recent not_found
        sql = """
            SELECT c.id, c.cif FROM companies c
            LEFT JOIN scraping_logs l ON c.id = l.entity_id 
                AND l.process_name = 'enrich_elpais' 
                AND (l.status = 'success' OR (l.status = 'not_found' AND l.created_at > NOW() - INTERVAL 30 DAY))
            WHERE c.cif REGEXP '^[A-Z]' 
            AND (c.objeto_social IS NULL OR c.objeto_social = '' OR c.cnae_code IS NULL OR c.cnae_code = '')
            AND l.id IS NULL
            ORDER BY RAND()
            LIMIT 500
        """
        cursor.execute(sql)
        companies = cursor.fetchall()
        cursor.close()
        conn.close()
    except Exception as e:
        logger.error(f"Fetch failed: {e}")
        return
        
    if not companies:
        logger.info("No records to process.")
        return

    logger.info(f"Processing {len(companies)} records. Stealth & Smart Mode.")
    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        executor.map(process_company, companies)
    logger.info("--- Finished ---")

if __name__ == "__main__":
    main()
