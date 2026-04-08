import os
import re
import time
import unicodedata
import json
import requests
from bs4 import BeautifulSoup
from urllib.parse import urlparse, quote, unquote
import pymysql

def get_env_var(var_name):
    """Simple .env reader for Python since we might not have python-dotenv."""
    env_path = os.path.join(os.path.dirname(__file__), "..", ".env")
    if os.path.exists(env_path):
        with open(env_path, "r") as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#"): continue
                if "=" in line:
                    key, val = line.split("=", 1)
                    if key.strip() == var_name:
                        return val.strip().strip('"').strip("'")
    return os.environ.get(var_name)

# Use remote production credentials
DB_HOST = "217.61.210.127"
DB_USER = "apiempresas_user"
DB_PASS = "WONwyjpsmx3h3$@2"
DB_NAME = "reseller3537_apiempresas"
SERPER_API_KEY = get_env_var("SERPER_API_KEY")

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
}

def clean_company_name(name):
    """Remove SL, SA, etc for better search results."""
    clean = re.sub(r'\b(S\.L\.U\.|SLU|S\.L\.|SL|S\.A\.|SA|SOCIEDAD LIMITADA|SOCIEDAD ANONIMA)\b', '', name, flags=re.IGNORECASE)
    # Remove excessive spaces
    clean = ' '.join(clean.split())
    return clean.strip()

def generate_slugs(name):
    """Generate potential URL slugs for directory probing."""
    def slugify(text):
        text = unicodedata.normalize('NFD', text).encode('ascii', 'ignore').decode('utf-8')
        text = re.sub(r'[^\w\s-]', '', text).lower().strip()
        return re.sub(r'[-\s]+', '-', text)

    base = clean_company_name(name)
    slug = slugify(base)
    
    variants = [slug]
    if not slug.endswith('-sl'): variants.append(f"{slug}-sl")
    if not slug.endswith('-slu'): variants.append(f"{slug}-slu")
    if not slug.endswith('-sa'): variants.append(f"{slug}-sa")
    
    return list(dict.fromkeys(variants)) # Unique list

def search_google_serper(query):
    """Official search via Serper.dev API to avoid blocks."""
    if not SERPER_API_KEY:
        print("    [ERROR] SERPER_API_KEY not found in .env")
        return []
    
    url = "https://google.serper.dev/search"
    payload = json.dumps({"q": query, "hl": "es", "gl": "es"})
    headers = {
        'X-API-KEY': SERPER_API_KEY,
        'Content-Type': 'application/json'
    }
    
    try:
        response = requests.post(url, headers=headers, data=payload, timeout=7)
        if response.status_code == 200:
            return response.json().get('organic', [])
    except Exception as e:
        print(f"    [SERPER ERROR] {e}")
    return []

def scrape_directory_data(url, site_type):
    """Scrapes CIF, Phone and Website from a directory page."""
    data = {"cif": None, "phone": None, "website": None}
    try:
        time.sleep(1)
        resp = requests.get(url, headers=HEADERS, timeout=5)
        if resp.status_code != 200:
            return None
            
        soup = BeautifulSoup(resp.text, 'html.parser')
        
        if site_type == "empresia":
            # CIF
            cif_label = soup.find('span', class_='list-group-item-heading', string=re.compile('CIF', re.I))
            if cif_label:
                cif_val = cif_label.find_next('p', class_='list-group-item-text')
                if cif_val: data["cif"] = cif_val.get_text().strip()
            # Phone
            phone_label = soup.find(string=re.compile('Teléfono', re.I))
            if phone_label:
                p_val = phone_label.find_next('p')
                if p_val: data["phone"] = p_val.get_text().strip()

        elif site_type == "infonif":
            # NIF (data-nif attribute is gold)
            nif_tag = soup.find(attrs={"data-nif": True})
            if nif_tag:
                data["cif"] = nif_tag["data-nif"]
            else:
                th_nif = soup.find('th', string=re.compile('NIF', re.I))
                if th_nif: 
                    td = th_nif.find_next('td')
                    if td: data["cif"] = td.get_text().strip().split()[0]
            # Phone
            th_tel = soup.find('th', string=re.compile('Teléfono', re.I))
            if th_tel:
                td_tel = th_tel.find_next('td')
                if td_tel and td_tel.get_text().strip() != "-":
                    data["phone"] = td_tel.get_text().strip()

        elif site_type == "cincodias":
            h3_nif = soup.find('h3', string=re.compile('NIF', re.I))
            if h3_nif:
                next_div = h3_nif.find_parent('div').find_next_sibling('div')
                if next_div:
                    data["cif"] = next_div.get_text().strip().split()[0]
            h3_tel = soup.find('h3', string=re.compile('Teléfono', re.I))
            if h3_tel:
                next_div_tel = h3_tel.find_parent('div').find_next_sibling('div')
                if next_div_tel:
                    data["phone"] = next_div_tel.get_text().strip()

        if data["cif"]:
            # Strict validation: must be a letter followed by 7 or 8 alphanumeric chars
            data["cif"] = re.sub(r'[^A-Z0-9]', '', data["cif"].upper())[:9]
            if not re.match(r'^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-Z]$', data["cif"]):
                data["cif"] = None
                
        return data if data["cif"] else None
    except Exception as e:
        print(f"    [DIR ERROR] {e}")
        return None

def find_cif_in_directories(name, municipality=""):
    """Tries to find CIF by probing direct URL slugs first, or via Serper Snippets."""
    slugs = generate_slugs(name)
    sites = [
        {"base": "https://www.empresia.es/empresa/", "type": "empresia"},
        {"base": "https://infonif.economia3.com/ficha-empresa/", "type": "infonif"}
    ]
    
    print(f"    [PROBE] Trying direct slugs for {name}...")
    for slug in slugs:
        for site in sites:
            target_url = f"{site['base']}{slug}/"
            res = scrape_directory_data(target_url, site['type'])
            if res and res["cif"]:
                print(f"    [PROBE SUCCESS] Found CIF {res['cif']} at {site['type']}")
                return res
    
    # Search Fallback via Serper (Snippet Extraction + Crawling)
    print(f"    [API SEARCH] Searching directories via Serper for {name}...")
    query = f'"{name}" {municipality} borme cif infonif'
    cif_pattern = re.compile(r'\b([ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-Z])\b', re.I)

    results = search_google_serper(query)
    
    # 1. Extract from Snippets (Visual scraping from API response)
    all_text = " ".join([r.get('title', '') + " " + r.get('snippet', '') for r in results])
    potential_cifs = cif_pattern.findall(all_text)
    if potential_cifs:
        from collections import Counter
        most_common = Counter(potential_cifs).most_common(1)
        if most_common:
            found_cif = most_common[0][0].upper()
            print(f"    [SNIPPET SUCCESS] Found potential CIF in Google API: {found_cif}")
            return {"cif": found_cif, "phone": None, "website": None}

    # 2. Directory Crawling (Plan B)
    for res in results:
        href = res.get('link', '').lower()
        stype = None
        if 'empresia.es' in href: stype = "empresia"
        elif 'infonif.economia3.com' in href: stype = "infonif"
        elif 'cincodias.elpais.com' in href: stype = "cincodias"
        
        if stype:
            data = scrape_directory_data(res.get('link'), stype)
            if data and data["cif"]:
                print(f"    [SEARCH SUCCESS] Found {data['cif']} at {stype}")
                return data
                
    return None

def find_official_website(company_name, municipality="", cif=""):
    """Attempts to find the company website using Serper API."""
    query = f'"{company_name}" {municipality} {cif} web oficial'
    print(f"    [API WEB SEARCH] Looking for official site via Serper...")
    
    results = search_google_serper(query)
    
    skip_domains = [
        'axesor.es', 'informalia.es', 'einforma.com', 'empresia.es', 'boe.es', 
        'borme.es', 'linkedin.com', 'facebook.com', 'instagram.com', 'twitter.com',
        'infocif.es', 'eleconomista.es', 'cylex.es', 'paginasamarillas.es', 'vulka.es',
        'guiaempresa.es', 'infoconcurso.es', 'vincit.com', 'oficinaempleo.com',
        'openthenews.com', 'datocapital.com', 'librebor.me', 'google.com', 'bing.com',
        'economiadigital.es', 'expansion.com', 'elpais.com', 'yahoo.com', 'youtube.com'
    ]
    
    for res in results:
        href = res.get('link', '').strip()
        if not href.startswith('http'): continue
        parsed = urlparse(href)
        domain = parsed.netloc.lower()
        if any(skip in domain for skip in skip_domains): continue
        return href
        
    return None

def scrape_contact_info(website_url):
    """Scrapes a website for phone numbers and emails."""
    info = {"email": None, "phone": None}
    try:
        time.sleep(1)
        resp = requests.get(website_url, headers=HEADERS, timeout=5)
        if resp.status_code != 200: return info
        text = resp.text
        emails = re.findall(r'[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,}', text)
        if emails: info["email"] = emails[0].lower()
        phones = re.findall(r'\b(?:(?:\+|00)34|34)?[6789]\d{8}\b', text)
        if phones: info["phone"] = phones[0]
    except: pass
    return info

def run_enrichment(limit=10):
    conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, db=DB_NAME, cursorclass=pymysql.cursors.DictCursor)
    try:
        with conn.cursor() as cur:
            sql = """
            SELECT c.id, c.company_name, c.municipality, c.cif, crs.score_total 
            FROM companies c
            JOIN company_radar_scores crs ON c.id = crs.company_id
            LEFT JOIN company_enrichment ce ON c.id = ce.company_id
            WHERE (c.cif IS NULL OR c.cif = '' OR ce.email IS NULL OR ce.website_official IS NULL)
              AND crs.score_total > 50
            ORDER BY crs.score_total DESC
            LIMIT %s
            """
            cur.execute(sql, (limit,))
            leads = cur.fetchall()
            print(f"[*] Processing {len(leads)} leads with Two-Step Discovery...")
            
            for lead in leads:
                print(f"--- Lead: {lead['company_name']} ({lead['score_total']}) ---")
                
                cif = lead['cif']
                phone_dir = None
                
                # Step 1: Discover CIF if missing
                if not cif or len(cif) < 5:
                    dir_data = find_cif_in_directories(lead['company_name'], lead['municipality'] or "")
                    if dir_data and dir_data["cif"]:
                        cif = dir_data["cif"]
                        phone_dir = dir_data["phone"]
                        # Update core table
                        cur.execute("UPDATE companies SET cif = %s WHERE id = %s", (cif, lead['id']))
                        print(f"    [UPDATE CORE] CIF {cif} saved.")
                
                # Step 2: Website & Email Discovery
                website = find_official_website(clean_company_name(lead['company_name']), lead['municipality'] or "", cif)
                email = None
                phone_web = None
                
                if website:
                    print(f"    [WEB] Found: {website}")
                    contacts = scrape_contact_info(website)
                    email = contacts["email"]
                    phone_web = contacts["phone"]
                
                # Step 3: Persistence (Enrichment Table)
                final_phone = phone_web or phone_dir
                
                enrich_sql = """
                INSERT INTO company_enrichment 
                    (company_id, website_official, email, phone_enriched, updated_at)
                VALUES (%s, %s, %s, %s, NOW())
                ON DUPLICATE KEY UPDATE
                    website_official = COALESCE(VALUES(website_official), website_official),
                    email = COALESCE(VALUES(email), email),
                    phone_enriched = COALESCE(VALUES(phone_enriched), phone_enriched),
                    updated_at = NOW()
                """
                cur.execute(enrich_sql, (lead['id'], website, email, final_phone))
                
                if final_phone: print(f"    [PHONE] {final_phone}")
                if email: print(f"    [EMAIL] {email}")
                
            conn.commit()
    finally:
        conn.close()

if __name__ == "__main__":
    run_enrichment(limit=100)
