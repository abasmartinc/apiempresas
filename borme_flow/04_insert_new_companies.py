# -*- coding: utf-8 -*-
import os, re, pymysql, unicodedata, datetime

from config import mysql_connect


def slugify(value):
    """
    Normalizes string, converts to lowercase, removes non-alpha characters,
    and converts spaces to hyphens.
    """
    value = str(value)
    value = unicodedata.normalize('NFKD', value).encode('ascii', 'ignore').decode('ascii')
    value = re.sub(r'[^\w\s-]', '', value).strip().lower()
    return re.sub(r'[-\s]+', '-', value)

def clean_company_name(name):
    """
    Removes (R.M. ...) suffixes from company name
    Example: MADECA SL(R.M. SANTA CRUZ DE TENERIFE) -> MADECA SL
    """
    clean = re.sub(r'\s*\(R\.?M\.?.*?\)', '', name, flags=re.IGNORECASE)
    return clean.strip()

def run():
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            while True:
                # 1. Get unprocessed candidates in batches
                cur.execute("SELECT id, post_id, raw_name, borme_date FROM borme_candidates WHERE processed = 0 LIMIT 10000")
                candidates = cur.fetchall()
                
                if not candidates:
                    print("[*] No more candidates to process.")
                    break

                print(f"[*] Processing batch of {len(candidates)} candidates...")

                for cand in candidates:
                    raw_name = cand["raw_name"]
                    cleaned_name = clean_company_name(raw_name)
                    slug = slugify(cleaned_name)
                    
                    # Check if slug already exists to avoid duplicates (basic check)
                    cur.execute("SELECT id FROM companies WHERE slug = %s", (slug,))
                    if cur.fetchone():
                        print(f"    [SKIP] Slug exists for {raw_name} ({slug})")
                        
                        # Try to find the existing company ID and link it
                        cur.execute("SELECT id FROM companies WHERE slug = %s LIMIT 1", (slug,))
                        existing = cur.fetchone()
                        if existing:
                            print(f"    [LINK] Linked to existing company {existing['id']}")
                            cur.execute("UPDATE borme_posts SET company_id = %s WHERE id = %s", (existing["id"], cand["post_id"]))
                        
                        cur.execute("UPDATE borme_candidates SET processed = 1 WHERE id = %s", (cand["id"],))
                        continue

                    # 2. Insert into emp companies
                    now = datetime.datetime.now()
                    print(f"    [NEW] Creating company: {raw_name}")
                    
                    cur.execute("""
                        INSERT INTO companies 
                        (company_name, slug, created_at, updated_at) 
                        VALUES (%s, %s, %s, %s)
                    """, (raw_name, slug, now, now))
                    
                    new_company_id = cur.lastrowid
                    
                    # 3. Associate the post
                    cur.execute("UPDATE borme_posts SET company_id = %s WHERE id = %s", (new_company_id, cand["post_id"]))
                    
                    # 4. Mark candidate as processed
                    cur.execute("UPDATE borme_candidates SET processed = 1 WHERE id = %s", (cand["id"],))
                
                # Commit after every batch to save progress
                conn.commit()
                print(f"[*] Batch committed.")

            print("[*] Done.")

    except Exception as e:
        print(f"[!] Error: {e}")
        conn.rollback()
    finally:
        conn.close()

if __name__ == "__main__": run()
