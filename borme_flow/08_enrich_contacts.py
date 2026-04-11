import os
import re
import time
import unicodedata
import json
import requests
from bs4 import BeautifulSoup
from urllib.parse import urlparse, quote, unquote
import pymysql
import config # Import central configuration

def get_env_var(var_name):
    """Retrieve environment variable using config or os.environ."""
    return os.getenv(var_name)

# Database parameters from central config
DB_HOST = config.DB_HOST
DB_USER = config.DB_USER
DB_PASS = config.DB_PASS
DB_NAME = config.DB_NAME
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

def get_similarity(s1, s2):
    """Simple similarity ratio between two strings."""
    if not s1 or not s2: return 0
    s1, s2 = s1.lower(), s2.lower()
    if s1 in s2 or s2 in s1: return 0.8
    # Basic overlap check
    words1 = set(re.findall(r'\w+', s1))
    words2 = set(re.findall(r'\w+', s2))
    if not words1: return 0
    intersection = words1.intersection(words2)
    return len(intersection) / len(words1)

def verify_website_content(url, company_name, cif):
    """Visits the site and checks for CIF or Company Name in critical areas."""
    try:
        resp = requests.get(url, headers=HEADERS, timeout=10)
        if resp.status_code != 200: return False
        text = resp.text.lower()
        soup = BeautifulSoup(resp.text, 'html.parser')
        
        # 1. Check for CIF if provided
        if cif and cif.lower() in text:
            print(f"    [VERIFY] CIF {cif} found in content!")
            return True
        
        # 2. Check for company name in footer/legal or title
        clean_name = clean_company_name(company_name).lower()
        if clean_name in text:
            # Check if it appears in common legal/footer tags
            footer = soup.find('footer')
            if footer and clean_name in footer.get_text().lower():
                print(f"    [VERIFY] Company name found in footer.")
                return True
            
            title = soup.title.string.lower() if soup.title else ""
            if clean_name in title:
                print(f"    [VERIFY] Company name found in page title.")
                return True

        # 3. Simple domain check as fallback
        domain = urlparse(url).netloc.lower()
        if get_similarity(clean_name, domain) > 0.6:
            print(f"    [VERIFY] Domain similarity high: {domain}")
            return True
            
    except Exception as e:
        print(f"    [VERIFY ERROR] {url}: {e}")
    return False

def find_official_website(company_name, municipality="", cif=""):
    """Attempts to find the company website using Serper API with verification."""
    clean_name = clean_company_name(company_name)
    query = f'"{clean_name}" {municipality} web oficial'
    print(f"    [API WEB SEARCH] Looking for official site: {query}")
    
    results = search_google_serper(query)
    
    skip_domains = [
        'axesor.es', 'informalia.es', 'einforma.com', 'empresia.es', 'boe.es', 
        'borme.es', 'linkedin.com', 'facebook.com', 'instagram.com', 'twitter.com',
        'infocif.es', 'eleconomista.es', 'cylex.es', 'paginasamarillas.es', 'vulka.es',
        'guiaempresa.es', 'infoconcurso.es', 'vincit.com', 'oficinaempleo.com',
        'openthenews.com', 'datocapital.com', 'librebor.me', 'google.com', 'bing.com',
        'economiadigital.es', 'expansion.com', 'elpais.com', 'yahoo.com', 'youtube.com',
        'mapa.es', 'justicia.es', 'hacienda.gob.es', 'boe.es', 'notarios.org',
        'infoempresa.com', 'directorio-empresas.es', 'empresite', 'vulka', 'sucatcat'
    ]
    
    candidates = []
    for res in results:
        href = res.get('link', '').strip()
        if not href.startswith('http'): continue
        
        parsed = urlparse(href)
        domain = parsed.netloc.lower()
        
        if any(skip in domain for skip in skip_domains): continue
        
        # Calculate a preliminary score
        title = res.get('title', '')
        snippet = res.get('snippet', '')
        
        score = 0
        if clean_name.lower() in title.lower(): score += 5
        if clean_name.lower() in domain: score += 10
        if clean_name.lower() in snippet.lower(): score += 2
        
        candidates.append({"url": href, "score": score})
    
    # Sort candidates by score
    candidates.sort(key=lambda x: x['score'], reverse=True)
    
    # Verify the top 3 candidates
    for cand in candidates[:3]:
        print(f"    [CANDIDATE] Checking {cand['url']} (Score: {cand['score']})...")
        if cand['score'] > 5 or verify_website_content(cand['url'], company_name, cif):
            return cand['url']
        
    return None

def extract_from_html(html, base_url=""):
    """Internal helper to extract emails and phones from HTML."""
    results = {"email": None, "phone": None}
    
    # 1. Email Extraction
    # Filter out common false positives like .png, .jpg, etc.
    emails = re.findall(r'[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,}', html)
    valid_emails = []
    for e in emails:
        e = e.lower()
        if any(e.endswith(ext) for ext in ['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp']): continue
        # If possible, check if email domain matches base_url domain
        if base_url:
            domain = urlparse(base_url).netloc.lower().replace('www.', '')
            if domain in e:
                # Prioritize emails from the same domain
                valid_emails.insert(0, e)
                continue
        valid_emails.append(e)
    
    if valid_emails:
        results["email"] = valid_emails[0]
        
    # 2. Phone Extraction
    # Spanish format: +34, 0034, or just 9 digits starting with 6, 7, 8, 9
    # We use a pattern that allows spaces, hyphens and dots
    phone_pattern = re.compile(r'(?:(?:\+|00)34[\s.-]?)?([6789]\d{2}[\s.-]?\d{3}[\s.-]?\d{3})\b')
    phones = phone_pattern.findall(html)
    if phones:
        # Clean up the phone number (remove non-digits, ensuring it's 9 digits)
        clean_phone = re.sub(r'\D', '', phones[0])
        if len(clean_phone) >= 9:
            results["phone"] = clean_phone[-9:]
            
    return results

def scrape_contact_info(website_url):
    """Scrapes a website and its common subpages for contact info."""
    print(f"    [SCRAPE] Crawling {website_url}...")
    final_info = {"email": None, "phone": None}
    
    try:
        time.sleep(1)
        resp = requests.get(website_url, headers=HEADERS, timeout=10)
        if resp.status_code != 200: return final_info
        
        soup = BeautifulSoup(resp.text, 'html.parser')
        home_results = extract_from_html(resp.text, website_url)
        final_info.update(home_results)
        
        # If we already have both, stop here
        if final_info["email"] and final_info["phone"]:
            return final_info
            
        # Look for subpages
        subpage_keywords = ['contacto', 'contact', 'aviso-legal', 'legal', 'quienes', 'about', 'privacidad']
        links_to_crawl = []
        
        for link in soup.find_all('a', href=True):
            href = link['href'].lower()
            if any(key in href for key in subpage_keywords):
                # Ensure it's an absolute URL
                if href.startswith('/'):
                    parsed_base = urlparse(website_url)
                    href = f"{parsed_base.scheme}://{parsed_base.netloc}{href}"
                elif not href.startswith('http'):
                    href = website_url.rstrip('/') + '/' + href.lstrip('/')
                
                if website_url in href and href != website_url:
                    links_to_crawl.append(href)
        
        # Deduplicate and limit
        links_to_crawl = list(dict.fromkeys(links_to_crawl))[:3]
        
        for link in links_to_crawl:
            if final_info["email"] and final_info["phone"]: break
            print(f"    [SCRAPE-SUB] Checking {link}...")
            try:
                time.sleep(1)
                s_resp = requests.get(link, headers=HEADERS, timeout=7)
                if s_resp.status_code == 200:
                    sub_results = extract_from_html(s_resp.text, website_url)
                    if sub_results["email"] and not final_info["email"]:
                        final_info["email"] = sub_results["email"]
                    if sub_results["phone"] and not final_info["phone"]:
                        final_info["phone"] = sub_results["phone"]
            except: pass
            
    except Exception as e:
        print(f"    [SCRAPE ERROR] {e}")
        
    return final_info

def run_enrichment(limit=10):
    conn = config.mysql_connect()
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
