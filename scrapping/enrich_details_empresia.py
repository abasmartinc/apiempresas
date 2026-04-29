# -*- coding: utf-8 -*-
import requests
from bs4 import BeautifulSoup
import mysql.connector
from mysql.connector import pooling
from concurrent.futures import ThreadPoolExecutor
import time
import random
import logging
import re
import os
from datetime import datetime
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

BASE_URL = "https://www.empresia.es/empresa/"

MAX_WORKERS = 10
SLEEP_MIN = 0.5
SLEEP_MAX = 1.5

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
        pool_name="detailspool",
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
        cursor.execute(sql, ("enrich_details_empresia", company_id, identifier, status, message))
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
        
        # Check what fields we need
        cursor.execute("SELECT address, phone, fecha_constitucion, cnae_code FROM companies WHERE id = %s", (company_id,))
        current = cursor.fetchone()

        url = f"{BASE_URL}{cif}/"
        
        time.sleep(random.uniform(SLEEP_MIN, SLEEP_MAX))
        
        response = requests.get(url, headers=get_headers(), timeout=30, allow_redirects=True)
        
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, 'html.parser')
            
            new_data = {}
            
            # 1. Phone
            phone_icon = soup.find('i', class_='fa-phone')
            if phone_icon:
                phone_text = phone_icon.next_sibling
                if phone_text:
                    phone = phone_text.strip()
                    if phone and (not current['phone'] or current['phone'].strip() == ''):
                        new_data['phone'] = phone
            
            # 2. Address
            address_icon = soup.find('i', class_='fa-address-book')
            if address_icon:
                address_text = address_icon.next_sibling
                if address_text:
                    address = address_text.strip()
                    if address and (not current['address'] or current['address'].strip() == ''):
                        new_data['address'] = address
                        pc = extract_postal_code(address)
                        if pc: new_data['postal_code'] = pc

            # 3. Key-Value Blocks (CNAE and Foundation Date)
            # CNAE (inside Objeto Social)
            obj_social_heading = soup.find('span', string='Objeto social', class_='list-group-item-heading')
            if obj_social_heading:
                p_tag = obj_social_heading.find_next_sibling('p', class_='list-group-item-text')
                if p_tag:
                    text = p_tag.get_text(strip=True)
                    match = re.search(r'CNAE\s*(\d+)\s*-\s*(.*)', text, re.IGNORECASE)
                    if match and (not current['cnae_code'] or current['cnae_code'].strip() == ''):
                        new_data['cnae_code'] = match.group(1).strip()
                        new_data['cnae_label'] = match.group(2).strip()

            # Foundation Date
            const_heading = soup.find('span', string='Fecha constitución', class_='list-group-item-heading')
            if const_heading:
                p_tag = const_heading.find_next_sibling('p', class_='list-group-item-text')
                if p_tag:
                    f_date = parse_date(p_tag.get_text(strip=True))
                    if f_date and (not current['fecha_constitucion']):
                        new_data['fecha_constitucion'] = f_date

            if new_data:
                updates = []
                params = []
                for field, val in new_data.items():
                    updates.append(f"{field} = %s")
                    params.append(val)
                
                params.append(company_id)
                update_sql = f"UPDATE companies SET {', '.join(updates)} WHERE id = %s"
                cursor.execute(update_sql, tuple(params))
                conn.commit()
                log_to_db(conn, company_id, cif, "success", f"Updated: {', '.join(new_data.keys())}")
                logger.info(f"✅ SUCCESS: {cif} -> Updated {len(new_data)} fields")
            else:
                log_to_db(conn, company_id, cif, "already_filled", "No new data to update")
                logger.info(f"ℹ️ NO CHANGES: {cif}")

        elif response.status_code == 404:
            log_to_db(conn, company_id, cif, "not_found", "Page 404")
            logger.info(f"❌ 404: {cif}")
        elif response.status_code == 503 or response.status_code == 429:
            logger.warning(f"⚠️ {response.status_code} for {cif}. Cooling down 10 min.")
            GLOBAL_COOLDOWN_UNTIL = time.time() + 600
        else:
            logger.error(f"❌ HTTP {response.status_code} for {cif}")
            
    except Exception as e:
        logger.error(f"💥 EXCEPTION for {cif}: {e}")
    finally:
        if conn:
            conn.close()

def main():
    logger.info("--- Starting Full Details Enrichment from Empresia ---")
    
    while True:
        try:
            conn = db_pool.get_connection()
            cursor = conn.cursor(dictionary=True)
            
            # Select companies with CIF but missing some key details
            sql = """
                SELECT c.id, c.cif FROM companies c
                LEFT JOIN scraping_logs l ON c.id = l.entity_id 
                    AND l.process_name = 'enrich_details_empresia' 
                    AND (l.status IN ('success', 'already_filled') OR (l.status = 'not_found' AND l.created_at > NOW() - INTERVAL 30 DAY))
                WHERE c.cif IS NOT NULL AND c.cif != ''
                AND (c.address IS NULL OR c.address = '' 
                     OR c.phone IS NULL OR c.phone = '' 
                     OR c.fecha_constitucion IS NULL 
                     OR c.cnae_code IS NULL OR c.cnae_code = '')
                AND l.id IS NULL
                ORDER BY c.id DESC
                LIMIT 500
            """
            cursor.execute(sql)
            companies = cursor.fetchall()
            cursor.close()
            conn.close()
            
            if not companies:
                logger.info("No records left to process. Sleeping 30 minutes...")
                time.sleep(1800)
                continue

            logger.info(f"🚀 Processing batch of {len(companies)} records...")
            
            with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
                executor.map(process_company, companies)
            
            logger.info("✅ Batch finished.")
            time.sleep(10)
            
        except Exception as e:
            logger.error(f"💥 Main Loop Error: {e}")
            time.sleep(60)

if __name__ == "__main__":
    main()
