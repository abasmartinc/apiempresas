import requests
from bs4 import BeautifulSoup
import mysql.connector
from mysql.connector import pooling
from concurrent.futures import ThreadPoolExecutor
import time
import random
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

SEARCH_URL = "https://www.empresia.es/busqueda/"

MAX_WORKERS = 10
SLEEP_MIN = 0.2
SLEEP_MAX = 1.0

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3.1 Mobile/15E148 Safari/604.1"
]

# --- LOGGING SETUP ---
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] Thread-%(thread)d: %(message)s',
    datefmt='%H:%M:%S'
)
logger = logging.getLogger(__name__)

# Connection Pool
try:
    db_pool = pooling.MySQLConnectionPool(
        pool_name="empresiapool",
        pool_size=MAX_WORKERS + 2,
        **DB_CONFIG
    )
    logger.info("Database connection pool initialized.")
except Exception as e:
    logger.error(f"Failed to initialize DB pool: {e}")
    exit(1)

GLOBAL_COOLDOWN_UNTIL = 0

def get_headers():
    return {
        "User-Agent": random.choice(USER_AGENTS),
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
        "Accept-Language": "es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
        "DNT": "1",
        "Connection": "keep-alive",
        "Upgrade-Insecure-Requests": "1"
    }

def log_to_db(conn, company_id, identifier, status, message):
    try:
        cursor = conn.cursor()
        sql = "INSERT INTO scraping_logs (process_name, entity_id, identifier, status, message) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, ("enrich_empresia", company_id, identifier, status, message))
        conn.commit()
        cursor.close()
    except Exception as e:
        logger.error(f"Error writing to scraping_logs: {e}")

def parse_date(date_str):
    if not date_str or date_str == '-':
        return None
    try:
        # Expected: DD/MM/YYYY
        return datetime.strptime(date_str.strip(), '%d/%m/%Y').strftime('%Y-%m-%d')
    except:
        return None

def parse_year(year_str):
    if not year_str: return None
    match = re.search(r'\b(20\d{2}|19\d{2})\b', year_str)
    return int(match.group(1)) if match else None

def extract_postal_code(address_str):
    if not address_str: return None
    match = re.search(r'\b(\d{5})\b', address_str)
    return match.group(1) if match else None

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
        
        # Check current data to avoid overwriting
        cols = ["objeto_social", "fecha_constitucion", "capital_social_raw", "ventas_raw", "registro_mercantil", "ult_cuentas_anio", "address", "postal_code"]
        cursor.execute(f"SELECT {', '.join(cols)} FROM companies WHERE id = %s", (company_id,))
        current = cursor.fetchone()

        url = f"{SEARCH_URL}?q={cif}"
        
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        session = requests.Session()
        response = session.get(url, headers=get_headers(), timeout=30, allow_redirects=True)
        
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 1. Main Name
            name_tag = soup.select_one('div.datos-ident span.bolder')
            found_name = name_tag.get_text(strip=True) if name_tag else None
            
            # 2. Address
            address_icon = soup.find('i', class_='fa-address-book')
            found_address = None
            if address_icon:
                address_text = address_icon.next_sibling
                if address_text:
                    found_address = address_text.strip()
            
            # 3. Key-Value Blocks
            data = {}
            blocks = soup.select('div.list-group-item div.col-sm-4.bl-z')
            obj_social_block = soup.find('span', string='Objeto social')
            if obj_social_block:
                p_tag = obj_social_block.find_next_sibling('p')
                if p_tag: data['Objeto social'] = p_tag.get_text(strip=True)

            for block in blocks:
                heading = block.select_one('.list-group-item-heading')
                text_p = block.select_one('.list-group-item-text')
                if heading and text_p:
                    data[heading.get_text(strip=True)] = text_p.get_text(strip=True)

            # Map fields
            new_data = {
                'objeto_social': data.get('Objeto social'),
                'fecha_constitucion': parse_date(data.get('Fecha constitución')),
                'capital_social_raw': data.get('Capital social'),
                'ventas_raw': data.get('Ventas'),
                'registro_mercantil': data.get('Registro'),
                'ult_cuentas_anio': parse_year(data.get('Últimas cuentas depositadas'))
            }
            
            if found_address:
                new_data['address'] = found_address
                pc = extract_postal_code(found_address)
                if pc: new_data['postal_code'] = pc

            # SAFETY CHECK: Only update if the field is currently EMPTY
            updates = []
            params = []
            for field, val in new_data.items():
                if val:
                    # If DB has nothing, or it's just whitespace, we update
                    current_val = current.get(field)
                    if current_val is None or (isinstance(current_val, str) and current_val.strip() == ''):
                        updates.append(f"{field} = %s")
                        params.append(val)
            
            if updates:
                updated_fields = [u.split(' =')[0] for u in updates]
                params.append(company_id)
                update_sql = f"UPDATE companies SET {', '.join(updates)} WHERE id = %s"
                cursor.execute(update_sql, tuple(params))
                conn.commit()
                log_to_db(conn, company_id, cif, "success", f"Enriched: {', '.join(updated_fields)}")
                logger.info(f"✅ SUCCESS EMPRESIA: {cif} ({found_name}) -> New Fields: {', '.join(updated_fields)}")
            else:
                log_to_db(conn, company_id, cif, "already_filled", "Fields already have data")
                logger.info(f"ℹ️ ALREADY FILLED: {cif}")

        elif response.status_code == 503 or response.status_code == 429:
            logger.warning(f"⚠️ {response.status_code} for {cif}. Cooling down 10 min.")
            GLOBAL_COOLDOWN_UNTIL = time.time() + 600
        else:
            logger.error(f"❌ ERROR: HTTP {response.status_code} for CIF {cif}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION for CIF {cif}: {e}")
    finally:
        if conn:
            conn.close()

def main():
    logger.info("--- Starting Empresia Enrichment (Continuous Batch Mode) ---")
    
    while True:
        try:
            conn = db_pool.get_connection()
            cursor = conn.cursor(dictionary=True)
            
            # Priority: Companies with CIF but missing Foundation Date or Sales
            sql = """
                SELECT c.id, c.cif FROM companies c
                LEFT JOIN scraping_logs l ON c.id = l.entity_id 
                    AND l.process_name = 'enrich_empresia' 
                    AND (l.status IN ('success', 'already_filled') OR (l.status = 'not_found' AND l.created_at > NOW() - INTERVAL 30 DAY))
                WHERE c.cif REGEXP '^[A-Z]'
                AND (c.fecha_constitucion IS NULL OR c.ventas_raw IS NULL OR c.objeto_social IS NULL)
                AND l.id IS NULL
                ORDER BY c.id DESC
                LIMIT 500
            """
            cursor.execute(sql)
            companies = cursor.fetchall()
            cursor.close()
            conn.close()
            
            if not companies:
                logger.info("No records left to process in Empresia. Sleeping 15 minutes...")
                time.sleep(900)
                continue

            logger.info(f"🚀 Empresia Batch: {len(companies)} records...")
            
            with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
                executor.map(process_company, companies)
            
            logger.info("✅ Empresia Batch finished.")
            time.sleep(10)
            
        except Exception as e:
            logger.error(f"💥 Main Loop Error: {e}")
            time.sleep(60)

if __name__ == "__main__":
    main()
