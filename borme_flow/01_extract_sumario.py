# -*- coding: utf-8 -*-
import os
import time
import datetime as dt
import requests
import xml.etree.ElementTree as ET
import pymysql

# Prevent PermissionError: [Errno 13] Permission denied: 'virtual_file.log'
if 'SSLKEYLOGFILE' in os.environ:
    os.environ.pop('SSLKEYLOGFILE')

from config import mysql_connect

BASE_SUMARIO = "https://www.boe.es/datosabiertos/api/borme/sumario/{yyyymmdd}"

def get_text(elem, tag):
    child = elem.find(tag)
    return child.text.strip() if child is not None and child.text else None

def parse_sumario_xml(xml_bytes: bytes):
    root = ET.fromstring(xml_bytes)
    sumario = root.find(".//sumario")
    if sumario is None: return None
    md = sumario.find("metadatos")
    fecha_pub_txt = get_text(md, "fecha_publicacion")
    fecha_pub = dt.datetime.strptime(fecha_pub_txt, "%Y%m%d").date() if fecha_pub_txt else None
    diario = sumario.find("diario")
    diario_numero = int(diario.get("numero")) if diario is not None else None
    url_pdf_sumario = get_text(diario.find("sumario_diario"), "url_pdf") if diario is not None else None
    items = []
    if diario is not None:
        for seccion in diario.findall("seccion"):
            sec_code = seccion.get("codigo")
            if sec_code not in ("A", "B", "C"): continue
            if sec_code in ("A", "B"):
                for item in seccion.findall("item"):
                    items.append({
                        "section": sec_code,
                        "ident": get_text(item, "identificador"),
                        "province": get_text(item, "titulo"),
                        "url_pdf": get_text(item, "url_pdf")
                    })
            if sec_code == "C":
                for apartado in seccion.findall("apartado"):
                    for item in apartado.findall("item"):
                        items.append({
                            "section": sec_code,
                            "ident": get_text(item, "identificador"),
                            "province": None,
                            "url_pdf": get_text(item, "url_pdf")
                        })
    return {"date": fecha_pub, "num": diario_numero, "url": url_pdf_sumario, "items": items}

def run(d: dt.date):
    print(f"[*] Extracting {d}...")
    url = BASE_SUMARIO.format(yyyymmdd=d.strftime("%Y%m%d"))
    headers = {"Accept": "application/xml", "User-Agent": "VerificaEmpresas/1.0"}
    resp = requests.get(url, headers=headers, timeout=25)
    if resp.status_code != 200:
        print(f"[!] HTTP {resp.status_code} for {url}")
        return
    p = parse_sumario_xml(resp.content)
    if not p:
        print(f"[!] No data parsed for {d}")
        return
    print(f"[+] Found {len(p['items'])} items for {d}")
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            cur.execute("INSERT INTO borme_sumarios (borme_date, diario_numero, url_pdf_sumario) VALUES (%s,%s,%s) ON DUPLICATE KEY UPDATE diario_numero=VALUES(diario_numero), url_pdf_sumario=VALUES(url_pdf_sumario)", (p["date"], p["num"], p["url"]))
            cur.execute("SELECT id FROM borme_sumarios WHERE borme_date=%s", (p["date"],))
            sid = cur.fetchone()["id"]
            for it in p["items"]:
                cur.execute("INSERT INTO borme_items_raw (sumario_id, section_code, province, identificador, url_pdf) VALUES (%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE url_pdf=VALUES(url_pdf)", (sid, it["section"], it["province"], it["ident"], it["url_pdf"]))
        conn.commit()
    finally:
        conn.close()

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Extract BORME summaries.")
    parser.add_argument("--start", help="Start date (YYYY-MM-DD)")
    parser.add_argument("--end", help="End date (YYYY-MM-DD)")
    args = parser.parse_args()

    if args.start:
        start_date = dt.datetime.strptime(args.start, "%Y-%m-%d").date()
        end_date = dt.datetime.strptime(args.end, "%Y-%m-%d").date() if args.end else dt.date.today()
        
        # Determine step direction: 1 day forward
        step = dt.timedelta(days=1)
        current = start_date
        while current <= end_date:
            run(current)
            current += step
    else:
        conn = mysql_connect()
        start_date = None
        try:
            with conn.cursor() as cur:
                cur.execute("SELECT MAX(borme_date) as last_date FROM borme_sumarios")
                row = cur.fetchone()
                if row and row["last_date"]:
                    start_date = row["last_date"]
        finally:
            conn.close()

        if start_date:
            end_date = dt.date.today()
            step = dt.timedelta(days=1)
            current = start_date
            while current <= end_date:
                run(current)
                current += step
        else:
            # Default: last 7 days (today backwards)
            for i in range(7):
                run(dt.date.today() - dt.timedelta(days=i))
