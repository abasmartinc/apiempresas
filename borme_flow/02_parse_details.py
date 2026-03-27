# -*- coding: utf-8 -*-
import os, io, re, requests, pdfplumber, pymysql, datetime as dt

# Prevent PermissionError: [Errno 13] Permission denied: 'virtual_file.log'
if 'SSLKEYLOGFILE' in os.environ:
    os.environ.pop('SSLKEYLOGFILE')

from config import mysql_connect


HTTP_HEADERS = {"Accept": "application/pdf", "User-Agent": "VerificaEmpresas/1.0"}
RE_ITEM_LINE = re.compile(r"(?m)^\s*(\d{1,6})\s*-\s*(.+?)\s*$")
KNOWN_ACTS = ["Constitución","Ampliación de capital","Reducción de capital","Cambio de domicilio social","Cambio de objeto social","Nombramientos","Revocaciones","Extinción","Disolución","Fusión","Transformación","Ceses/Dimisiones","Reelecciones","Declaración de unipersonalidad","Pérdida del caracter de unipersonalidad","Escisión","Situación concursal"]



def clean_name(raw):
    s = re.sub(r"\s+", " ", raw or "").strip()
    return re.sub(r"\s*[–—\-.]\s*$", "", s)

def split_blocks(txt):
    lines = [l.strip() for l in (txt.splitlines() if txt else []) if l.strip()]
    blocks, idxs = [], []
    for i, line in enumerate(lines):
        m = RE_ITEM_LINE.match(line)
        if m: idxs.append((i, clean_name(m.group(2))))
    if not idxs: return []
    for k, (i, company) in enumerate(idxs):
        start = i + 1
        end = idxs[k+1][0] if k + 1 < len(idxs) else len(lines)
        blocks.append((company, "\n".join(lines[start:end]).strip()))
    return blocks

def process_item(cur, item):
    print(f"[*] Parsing {item['url_pdf']}")
    resp = requests.get(item["url_pdf"], headers=HTTP_HEADERS, timeout=64)
    if resp.status_code != 200: return
    with pdfplumber.open(io.BytesIO(resp.content)) as pdf:
        for page in pdf.pages:
            for name, block in split_blocks(page.extract_text()):
                acts = ", ".join([a for a in KNOWN_ACTS if re.search(r"\b"+re.escape(a)+r"\b", block, re.IGNORECASE)]) or "Otros"
                cur.execute("INSERT INTO borme_posts (borme_date, company_name, act_types, description, url_pdf) VALUES (%s,%s,%s,%s,%s)", (item["borme_date"], name, acts, block, item["url_pdf"]))
    cur.execute("UPDATE borme_items_raw SET processed=1 WHERE id=%s", (item["id"],))

def run():
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            cur.execute("SELECT r.*, s.borme_date FROM borme_items_raw r JOIN borme_sumarios s ON s.id=r.sumario_id WHERE r.processed=0 AND r.url_pdf IS NOT NULL")
            for item in cur.fetchall():
                process_item(cur, item)
                conn.commit()
    finally: conn.close()

if __name__ == "__main__": run()
