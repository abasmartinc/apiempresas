import re
import mysql.connector
from mysql.connector import pooling
import logging
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

# --- LOGGING ---
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%H:%M:%S'
)
logger = logging.getLogger(__name__)

# Connection Pool
db_pool = pooling.MySQLConnectionPool(
    pool_name="extraction_pool",
    pool_size=5,
    **DB_CONFIG
)

def get_db():
    return db_pool.get_connection()

def parse_borme_text(text, current_name=None):
    data = {
        'address': None,
        'objeto_social': None,
        'capital': None,
        'fecha_constitucion': None,
        'cnae_code': None,
        'cif': None,
        'admins': [],
        'better_name': None,
        'registry': None,
        'municipality': None
    }
    
    if not text: return data

    # 0. Try to find a better name if current_name looks like a code (e.g. 6202-C-EMROB)
    if current_name and (re.search(r'\d{4}-', current_name) or len(current_name) < 5):
        # In Section II, the name is often after a number at the start of the description
        name_match = re.search(r'\d{3,5}\s+([A-Z0-9\.\,\s\-]+(?:S\.L\.|S\.A\.|S\.L\.U\.|S\.A\.U\.|S\.L|S\.A|SL|SA))', text)
        if name_match:
            data['better_name'] = name_match.group(1).strip()

    # 1. CIF/NIF
    cif_match = re.search(r'(?:CIF|NIF)\.?\s*([A-Z]\d{8})', text, re.IGNORECASE)
    if cif_match:
        data['cif'] = cif_match.group(1).strip().upper()

    # 2. Address
    # Section I: Domicilio: ...
    # Section II: domicilio social de la sociedad, calle ...
    addr_match = re.search(r'Domicilio:\s*(.*?)\.\s*(?:Capital|Datos registrales|Objeto social|Nombramientos|$)', text, re.IGNORECASE)
    if not addr_match:
        addr_match = re.search(r'domicilio social(?:\s+de\s+la\s+sociedad)?,\s*(.*?)(?:\.\s*|, acord|$)', text, re.IGNORECASE)
    
    if addr_match:
        data['address'] = addr_match.group(1).strip()
        # Try to extract municipality/postal code from address
        # Example: CALLE MAYOR 1, 28001 MADRID
        pc_match = re.search(r'(\d{5})\s+([A-Z\s]+)$', data['address'], re.IGNORECASE)
        if pc_match:
            data['municipality'] = pc_match.group(2).strip()

    # 3. Objeto Social
    obj_match = re.search(r'Objeto social:\s*(.*?)\.\s*(?:Domicilio|Capital|Datos registrales|Nombramientos|$)', text, re.IGNORECASE)
    if obj_match:
        data['objeto_social'] = obj_match.group(1).strip()
        # Try to find CNAE inside object
        cnae_match = re.search(r'CNAE(?:\s*2009)?[:\s]*(\d{2}\.?\d{0,2})', data['objeto_social'], re.IGNORECASE)
        if cnae_match:
            data['cnae_code'] = cnae_match.group(1).replace('.', '')

    # 4. Capital
    # Section I: Capital: 3.100,00 Euros
    # Section II: capital social por importe de 1.924.800 euros
    cap_match = re.search(r'Capital(?:\s+social)?(?:\s+por\s+importe\s+de)?[:\s]+([\d\.,]+)\s*Euros', text, re.IGNORECASE)
    if cap_match:
        data['capital'] = cap_match.group(1).strip()

    # 5. Fecha Constitución (Comienzo de operaciones)
    date_match = re.search(r'Comienzo de operaciones:\s*(\d{1,2}\.\d{1,2}\.\d{2,4})', text, re.IGNORECASE)
    if date_match:
        raw_date = date_match.group(1).strip()
        try:
            parts = re.split(r'[\.\/]', raw_date)
            if len(parts) == 3:
                if len(parts[2]) == 2: parts[2] = "20" + parts[2]
                data['fecha_constitucion'] = f"{parts[2]}-{parts[1]}-{parts[0]}"
        except: pass

    # 6. Administrators
    # Pattern: Position: Name1;Name2.
    admin_patterns = [
        (r'Adm\. Unico:\s*(.*?)\.', 'Adm. Unico'),
        (r'Adm\. Solid\.:\s*(.*?)\.', 'Adm. Solid.'),
        (r'Adm\. Mancom\.:\s*(.*?)\.', 'Adm. Mancom.'),
        (r'Socio nico:\s*(.*?)\.', 'Socio Unico'),
        (r'Apoderado:\s*(.*?)\.', 'Apoderado'),
        (r'Apoderado Mancomunado:\s*(.*?)\.', 'Apoderado Mancomunado'),
        (r'Apoderado Solidario:\s*(.*?)\.', 'Apoderado Solidario'),
        (r'Administrador nico de la Sociedad,\s*(.*?)\.', 'Adm. Unico'),
        (r'Administradora nica,\s*(.*?)\.', 'Adm. Unico'),
        (r'Presidente:\s*(.*?)\.', 'Presidente'),
        (r'Secretario:\s*(.*?)\.', 'Secretario'),
        (r'Consejero:\s*(.*?)\.', 'Consejero'),
        (r'Liquidador:\s*(.*?)\.', 'Liquidador'),
        (r'Liquidador Solidario:\s*(.*?)\.', 'Liquidador Solidario'),
        (r'Liquidador Unico:\s*(.*?)\.', 'Liquidador Unico'),
        (r'Representante:\s*(.*?)\.', 'Representante'),
        (r'Auditor:\s*(.*?)\.', 'Auditor'),
        (r'Vocal:\s*(.*?)\.', 'Vocal')
    ]
    
    for pattern, pos in admin_patterns:
        matches = re.findall(pattern, text, re.IGNORECASE)
        for match in matches:
            # Split by semicolon if multiple names
            names = [n.strip() for n in match.split(';')]
            for name in names:
                # Clean up name (sometimes has "D. " or "Dña. ")
                name = re.sub(r'^(?:D\.|Da\.|DÑA\.|DON|DOÑA)\s+', '', name, flags=re.IGNORECASE).strip()
                if name and len(name) > 3 and len(name) < 100:
                    data['admins'].append({'name': name, 'position': pos})

    # 7. Registry / Location (Often at the end of Section II)
    loc_match = re.search(r'([A-Z\s]+),\s*\d{1,2}\s+de\s+[a-z]+\s+de\s+\d{4}', text)
    if loc_match:
        data['registry'] = loc_match.group(1).strip()

    return data

def process_posts():
    conn = get_db()
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("""
        SELECT id, company_name, act_types, description 
        FROM borme_posts 
        WHERE admin_extracted = 0 
        ORDER BY id DESC 
        LIMIT 500
    """)
    posts = cursor.fetchall()
    
    for post in posts:
        # Skip technical/empty posts
        if not post['description'] or len(post['description']) < 50:
            if not re.search(r'S\.L\.|S\.A\.|S\.L\.U\.|S\.A\.U\.', post['company_name']):
                cursor.execute("UPDATE borme_posts SET admin_extracted = 1 WHERE id = %s", (post['id'],))
                conn.commit()
                continue

        parsed = parse_borme_text(post['description'], post['company_name'])
        
        final_name = parsed['better_name'] or post['company_name']
        
        # Skip posts with technical names
        if re.match(r'^\d{2}-\d{4}-[A-Z]-', final_name):
            cursor.execute("UPDATE borme_posts SET admin_extracted = 1 WHERE id = %s", (post['id'],))
            conn.commit()
            continue

        logger.info(f"Processing Post #{post['id']} - {final_name}")

        # 1. Find or Create Company
        # ... (rest of the logic remains similar but I'll make sure it's correct)
        company_id = None
        if parsed['cif']:
            cursor.execute("SELECT id FROM companies WHERE cif = %s LIMIT 1", (parsed['cif'],))
            res = cursor.fetchone()
            if res: company_id = res['id']
            
        if not company_id:
            cursor.execute("SELECT id FROM companies WHERE company_name = %s LIMIT 1", (final_name,))
            res = cursor.fetchone()
            if res: company_id = res['id']
        
        if company_id:
            # Update existing company
            update_fields = []
            params = []
            if parsed['address']: 
                update_fields.append("address = COALESCE(NULLIF(address, ''), %s)")
                params.append(parsed['address'])
            if parsed['objeto_social']:
                update_fields.append("objeto_social = COALESCE(NULLIF(objeto_social, ''), %s)")
                params.append(parsed['objeto_social'])
            if parsed['fecha_constitucion']:
                update_fields.append("fecha_constitucion = COALESCE(fecha_constitucion, %s)")
                params.append(parsed['fecha_constitucion'])
            if parsed['capital']:
                update_fields.append("capital_social_raw = COALESCE(NULLIF(capital_social_raw, ''), %s)")
                params.append(parsed['capital'])
            if parsed['cnae_code']:
                update_fields.append("cnae_code = COALESCE(NULLIF(cnae_code, ''), %s)")
                params.append(parsed['cnae_code'])
            if parsed['cif']:
                update_fields.append("cif = COALESCE(NULLIF(cif, ''), %s)")
                params.append(parsed['cif'])
            if parsed['registry']:
                update_fields.append("registro_mercantil = COALESCE(NULLIF(registro_mercantil, ''), %s)")
                params.append(parsed['registry'])
            if parsed['municipality']:
                update_fields.append("municipality = COALESCE(NULLIF(municipality, ''), %s)")
                params.append(parsed['municipality'])
                
            if update_fields:
                params.append(company_id)
                sql = f"UPDATE companies SET {', '.join(update_fields)} WHERE id = %s"
                cursor.execute(sql, tuple(params))
        else:
            # Create new company only if we have some useful data OR it's a clear company name
            has_useful_data = any([parsed['cif'], parsed['address'], parsed['objeto_social'], parsed['capital']])
            is_clear_company = re.search(r'S\.L\.|S\.A\.|S\.L\.U\.|S\.A\.U\.', final_name, re.IGNORECASE)
            
            if has_useful_data or is_clear_company:
                sql = """
                    INSERT INTO companies (company_name, cif, address, objeto_social, fecha_constitucion, capital_social_raw, cnae_code, registro_mercantil, municipality, created_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())
                """
                cursor.execute(sql, (
                    final_name, 
                    parsed['cif'],
                    parsed['address'], 
                    parsed['objeto_social'], 
                    parsed['fecha_constitucion'], 
                    parsed['capital'],
                    parsed['cnae_code'],
                    parsed['registry'],
                    parsed['municipality']
                ))
                company_id = cursor.lastrowid
                logger.info(f"   Created new company ID #{company_id}")
            else:
                logger.info(f"   Skipped company creation for {final_name} (no useful data)")

        # 2. Link Post to Company
        if company_id:
            cursor.execute("UPDATE borme_posts SET company_id = %s, admin_extracted = 1 WHERE id = %s", (company_id, post['id']))
        else:
            cursor.execute("UPDATE borme_posts SET admin_extracted = 1 WHERE id = %s", (post['id'],))
        
        # 3. Handle Administrators
        if company_id:
            for admin in parsed['admins']:
                sql = "INSERT IGNORE INTO company_administrators (company_id, post_id, action, position, name) VALUES (%s, %s, %s, %s, %s)"
                act_types = post['act_types'] or ''
                
                # Refined action logic
                if 'Constituci' in act_types or 'Nombramiento' in act_types:
                    action = 'Nombramiento'
                elif 'Ceses' in act_types or 'Dimisiones' in act_types:
                    action = 'Cese'
                elif 'Revocaciones' in act_types:
                    action = 'Revocación'
                else:
                    action = 'Cambio'
                    
                cursor.execute(sql, (company_id, post['id'], action, admin['position'], admin['name']))

        conn.commit()
    
    cursor.close()
    conn.close()

if __name__ == "__main__":
    import time
    logger.info("Starting BORME Data Extraction Script...")
    while True:
        try:
            process_posts()
            logger.info("Batch completed. Waiting for new posts...")
            time.sleep(10) # Wait 10 seconds between batches
        except Exception as e:
            logger.error(f"Main Loop Error: {e}")
            time.sleep(30)
