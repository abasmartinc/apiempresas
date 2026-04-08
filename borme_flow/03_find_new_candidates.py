# -*- coding: utf-8 -*-
import os, re, pymysql, unicodedata

from config import mysql_connect


def norm(n):
    if not n: return ""
    s = "".join(ch for ch in unicodedata.normalize("NFKD", n.upper()) if not unicodedata.combining(ch))
    s = re.sub(r"\b(S\.?L\.?|S\.?A\.?|S\.?L\.?U\.?|S\.?A\.?U\.?|SOCIEDAD LIMITADA|SOCIEDAD ANONIMA|COOPERATIVA)\b", "", s)
    return re.sub(r"\s+", " ", re.sub(r"[^\w\s]", " ", s)).strip()

def run():
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            # Query to find posts not associated (company_id IS NULL) and not scanned for candidates
            # We JOIN with borme_items_raw to get the province from the summary
            query = """
                SELECT p.id, p.company_name, p.borme_date, i.province 
                FROM borme_posts p 
                LEFT JOIN borme_items_raw i ON i.url_pdf = p.url_pdf
                LEFT JOIN borme_candidates c ON c.post_id = p.id 
                WHERE p.company_id IS NULL 
                  AND p.candidate_processed = 0 
                  AND c.id IS NULL 
                ORDER BY p.id DESC 
                LIMIT 300000
            """
            print(f"[*] Executing query: {query}")
            cur.execute(query)
            rows = cur.fetchall()
            print(f"[*] Found {len(rows)} potential candidates.")
            
            inserted_count = 0
            processed_ids = []
            
            for it in rows:
                processed_ids.append(it["id"])
                
                nm = norm(it["company_name"])
                # Check if this normalized name already exists in candidates (even if different post_id)
                cur.execute("SELECT id FROM borme_candidates WHERE normalized_name = %s LIMIT 1", (nm,))
                if not cur.fetchone():
                    cur.execute("""
                        INSERT INTO borme_candidates 
                        (post_id, raw_name, normalized_name, borme_date, province) 
                        VALUES (%s,%s,%s,%s,%s)
                    """, (it["id"], it["company_name"], nm, it["borme_date"], it["province"]))
                    inserted_count += 1
            
            # Mark ALL processed items as candidate_processed=1
            if processed_ids:
                print(f"[*] Marking {len(processed_ids)} items as processed...")
                format_strings = ','.join(['%s'] * len(processed_ids))
                cur.execute(f"UPDATE borme_posts SET candidate_processed = 1 WHERE id IN ({format_strings})", tuple(processed_ids))
            
            conn.commit()
            print(f"[*] Process finished. Inserted {inserted_count} new candidates.")
    finally: conn.close()

if __name__ == "__main__": run()
