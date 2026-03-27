# -*- coding: utf-8 -*-
import os, re, pymysql

from config import mysql_connect


def norm(n):
    if not n: return ""
    # Remove dots, dashes, and normalize spaces
    s = re.sub(r"[\.\-\,]", " ", n.upper())
    s = re.sub(r"\b(S\.?L\.?|S\.?A\.?|S\.?L\.?U\.?|S\.?A\.?U\.?|SOCIEDAD LIMITADA|SOCIEDAD ANONIMA|S\.?L\.?N\.?E\.?|COOPERATIVA|S\.?C\.?L\.?|SOCIEDAD COOPERATIVA|LIMITADA|ANONIMA|UNIPERSONAL|PROFESIONAL|S\.?L\.?P\.?|S\.?A\.?P\.?)\b", "", s)
    return re.sub(r"\s+", " ", s).strip()

def run():
    conn = mysql_connect()
    try:
        total_processed = 0
        while True:
            # Each batch is a transaction to hold locks while processing
            conn.begin() 
            with conn.cursor() as cur:
                # Fetch up to 1000 post that haven't been processed yet
                # Added FOR UPDATE SKIP LOCKED for parallel safety
                cur.execute("SELECT id, company_name FROM borme_posts WHERE company_id IS NULL AND association_processed = 0 LIMIT 1000 FOR UPDATE SKIP LOCKED")
                posts = cur.fetchall()
                if not posts:
                    conn.rollback() # Release if nothing found
                    if total_processed == 0:
                        print("[*] No pending posts to associate.")
                    else:
                        print(f"[*] Finished processing. Total processed: {total_processed}")
                    break
                
                print(f"[*] Processing batch of {len(posts)} posts (Total so far: {total_processed})...")
                
                # Prepare updates batch
                assoc_updates = []
                processed_ids = []
                
                for post in posts:
                    processed_ids.append(post["id"])
                    
                    orig_name = post["company_name"]
                    n_post = norm(orig_name)
                    
                    # Strategy 1: Exact Match (Index Seek - Ultra Fast)
                    cur.execute("SELECT id FROM companies WHERE company_name = %s LIMIT 1", (orig_name,))
                    m = cur.fetchone()
                    
                    # Strategy 2: Fulltext Search (Inverted Index - Very Fast)
                    if not m and n_post and len(n_post) > 2:
                        words = [w for w in n_post.split() if len(w) >= 2 and w.isalnum()]
                        if words:
                            ft_query = " ".join([f'+"{w}"*' for w in words])
                            try:
                                cur.execute("SELECT id FROM companies WHERE MATCH(company_name) AGAINST (%s IN BOOLEAN MODE) LIMIT 1", (ft_query,))
                                m = cur.fetchone()
                            except pymysql.err.ProgrammingError:
                                pass

                    if m:
                        # print(f"    [MATCH] {orig_name} -> ID {m['id']}")
                        assoc_updates.append((m["id"], post["id"]))
                
                # Apply association updates
                if assoc_updates:
                    print(f"    [+] Committing {len(assoc_updates)} associations...")
                    cur.executemany("UPDATE borme_posts SET company_id=%s WHERE id=%s", assoc_updates)
                
                # Mark ALL processed items as processed=1
                if processed_ids:
                    print(f"    [√] Marking {len(processed_ids)} items as processed...")
                    format_strings = ','.join(['%s'] * len(processed_ids))
                    cur.execute(f"UPDATE borme_posts SET association_processed = 1 WHERE id IN ({format_strings})", tuple(processed_ids))

                conn.commit()
                total_processed += len(posts)
                
                if not assoc_updates:
                    # print("    [-] No matches found in this batch (but cleaned queue).")
                    pass

    except Exception as e:
        print(f"[!] Error: {e}")
    finally:
        conn.close()


if __name__ == "__main__": run()
