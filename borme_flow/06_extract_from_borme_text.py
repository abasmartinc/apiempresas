# -*- coding: utf-8 -*-
"""
06_extract_from_borme_text.py
─────────────────────────────
Lee los registros de borme_posts que ya tienen company_id y extrae:
  1. Administradores (Adm. Único, Adm. Solid., Adm. Mancomunado, Consejero Delegado…)
     → tabla: company_administrators (company_id, post_id, action, position, name)
  2. Fecha de constitución (solo si el acto es "Constitución" y la empresa no tiene fecha aún)
     → companies.fecha_constitucion

Patrones BORME reales:
  Comienzo de operaciones: 25.11.24.
  Nombramientos. Adm. Unico: GARCIA LOPEZ JUAN.
  Ceses/Dimisiones. Adm. Solid.: PEREZ ANA;LOPEZ MARIA.
  Nombramientos. Consejero Delegado: TORRES RUIZ PEDRO.
"""

import re
import datetime
import time
import requests
from config import mysql_connect

# ──────────────────────────────────────────
#  REGEX PATTERNS
# ──────────────────────────────────────────

# Fecha de constitución:  "Comienzo de operaciones: 25.11.24"  or  "12.11.2024"
RE_FECHA = re.compile(
    r"[Cc]omienzo\s+de\s+operaciones\s*[:\-]\s*(\d{1,2}[./]\d{1,2}[./]\d{2,4})"
)

# Objeto Social: Captura el texto hasta el siguiente punto que precede a Domicilio o Capital
RE_OBJETO = re.compile(
    r"[Oo]bjeto\s+social\s*[:\-]\s*(.*?)(?=\. [Dd]omicilio| \.? [Cc]apital| \.? [Dd]atos|\.? $)", 
    re.DOTALL | re.IGNORECASE
)

# Domicilio: Captura la dirección hasta el siguiente bloque relevante
RE_DOMICILIO = re.compile(
    r"[Dd]omicilio\s*[:\-]\s*(.*?)(?=\.? [Cc]apital| \.? [Dd]atos| \.? [Dd]eclaración|\.? $)", 
    re.DOTALL | re.IGNORECASE
)

# Capital: Importe numérico en euros
RE_CAPITAL = re.compile(r"[Cc]apital\s*[:\-]\s*([\d.,]+)\s*[Ee]uros", re.IGNORECASE)

# Ampliación de Capital: Específicamente el nuevo capital suscrito
RE_AMPLIACION = re.compile(r"Ampliaci.n\s+de\s+capital\.\s+Capital\s*[:\-]\s*([\d.,]+)", re.IGNORECASE)
RE_RESULTANTE = re.compile(r"Resultante\s+Suscrito\s*[:\-]\s*([\d.,]+)", re.IGNORECASE)

# Cambio de Domicilio
RE_CAMBIO_DOMICILIO = re.compile(r"Cambio\s+de\s+domicilio\s+social\.\s*(.*?)(?=\. Datos|\.? $)", re.IGNORECASE)

# CNAE: Código de 4 dígitos si aparece
RE_CNAE = re.compile(r"CNAE\s+(\d{4})", re.IGNORECASE)

# Administrator block:  "Adm. Unico: NOMBRE1."  or  "Adm. Solid.: NOMBRE1;NOMBRE2."
# Covers the most common positions in the BORME
POSITION_PATTERNS = [
    ("Adm. Único",           re.compile(r"Adm\.?\s*[UÚ]nico\s*:\s*([^.]+)\.", re.IGNORECASE)),
    ("Adm. Solidario",       re.compile(r"Adm\.?\s*Solid\.?\s*:\s*([^.]+)\.", re.IGNORECASE)),
    ("Adm. Mancomunado",     re.compile(r"Adm\.?\s*Mancom\.?\s*:\s*([^.]+)\.", re.IGNORECASE)),
    ("Consejero Delegado",   re.compile(r"Consejero\s+Delegado\s*:\s*([^.]+)\.", re.IGNORECASE)),
    ("Liquidador",           re.compile(r"Liquidador\s*:\s*([^.]+)\.", re.IGNORECASE)),
    ("Administrador",        re.compile(r"Administrador\s*:\s*([^.]+)\.", re.IGNORECASE)),
]

# Sections that signal an action
RE_NOMBRAMIENTOS = re.compile(r"\bNombramientos\b", re.IGNORECASE)
RE_CESES         = re.compile(r"\bCeses/Dimisiones\b", re.IGNORECASE)


def parse_date(raw: str):
    """Convert '25.11.24' or '25.11.2024' or '25/11/24' to a date object."""
    raw = raw.replace("/", ".").strip().rstrip(".")
    for fmt in ("%d.%m.%y", "%d.%m.%Y"):
        try:
            return datetime.datetime.strptime(raw, fmt).date()
        except ValueError:
            pass
    return None


def split_names(raw: str):
    """Split 'GARCIA LOPEZ JUAN;PEREZ ANA' into a list of clean names."""
    return [n.strip() for n in re.split(r"[;,]", raw) if n.strip()]


def extract_administrators(text: str):
    """
    Returns a list of dicts:
      {'action': 'nombramiento'|'cese', 'position': str, 'name': str}
    """
    results = []

    # Split text into labelled sections: Nombramientos / Ceses/Dimisiones
    # We use a simple approach: scan for section keywords and assume the next
    # sentence block belongs to that action.
    lines = text.replace("\n", " ")

    # Determine action context for each match
    # We'll scan the full text for each position pattern, then check which
    # section keyword appears most recently before the match position.
    for position_label, pattern in POSITION_PATTERNS:
        for m in pattern.finditer(lines):
            span_start = m.start()
            raw_names = m.group(1)

            # Determine action: look backwards for nearest section keyword
            before = lines[:span_start]
            last_nombramiento = before.rfind("Nombramientos")
            last_cese         = before.rfind("Ceses")

            if last_cese > last_nombramiento:
                action = "cese"
            else:
                action = "nombramiento"

            for name in split_names(raw_names):
                stripped_name = name[:255] if isinstance(name, str) else str(name)[:255]
                if len(stripped_name) > 2:  # Skip garbage
                    results.append({
                        "action":   action,
                        "position": position_label,
                        "name":     stripped_name,
                    })

    return results


def geocode_address(address_full: str):
    """
    Usa Nominatim (OpenStreetMap) para obtener lat/long.
    Nominatim requiere un User-Agent identificativo y un límite de 1 req/seg.
    """
    if not address_full:
        return None, None

    url = "https://nominatim.openstreetmap.org/search"
    params = {
        "q": address_full + ", Spain",
        "format": "json",
        "limit": 1,
        "addressdetails": 1
    }
    headers = {
        "User-Agent": "ApiEmpresas-BormeBot/1.0 (papelo.amh@gmail.com)"
    }

    try:
        # Respetamos el límite de 1 seg por petición de Nominatim
        time.sleep(1.1)
        resp = requests.get(url, params=params, headers=headers, timeout=10)
        if resp.status_code == 200:
            data = resp.json()
            if data:
                return float(data[0]["lat"]), float(data[0]["lon"])
    except Exception as e:
        print(f"    [GEO] Error geocodificando {address_full}: {e}")
    
    return None, None


def run():
    conn = mysql_connect()
    total_admins   = 0
    total_fechas   = 0
    total_posts    = 0

    try:
        with conn.cursor() as cur:
            # Fetch posts that have a linked company but haven't been processed
            # for admin/date extraction yet.
            # We process both "Constitución" and "Nombramientos" posts.
            cur.execute("""
                SELECT
                    p.id        AS post_id,
                    p.company_id,
                    p.act_types,
                    p.description,
                    p.borme_date,
                    c.fecha_constitucion
                FROM borme_posts p
                JOIN companies c ON c.id = p.company_id
                WHERE p.company_id IS NOT NULL
                  AND p.admin_extracted = 0
                ORDER BY p.id ASC
                LIMIT 5000
            """)
            posts = cur.fetchall()
            print(f"[*] Posts to process: {len(posts)}")

            for post in posts:
                total_posts += 1
                post_id     = post["post_id"]
                company_id  = post["company_id"]
                act_types   = post["act_types"] or ""
                description = post["description"] or ""

                # ── 1. Datos de la empresa (Constituciones o Ampliaciones) ───────────
                update_fields = {}
                
                # --- A. CONSTITUCIONES ---
                if "Constitu" in act_types:
                    # Fecha de Constitución
                    if not post["fecha_constitucion"]:
                        m_fecha = RE_FECHA.search(description)
                        if m_fecha:
                            val = parse_date(m_fecha.group(1))
                            if val: update_fields["fecha_constitucion"] = val

                    # Objeto Social
                    m_obj = RE_OBJETO.search(description)
                    if m_obj: update_fields["objeto_social"] = m_obj.group(1).strip()

                    # Capital Social Inicial
                    m_cap = RE_CAPITAL.search(description)
                    if m_cap: update_fields["capital_social_raw"] = m_cap.group(1).strip()

                    # CNAE
                    m_cnae = RE_CNAE.search(description)
                    if m_cnae: update_fields["cnae_code"] = m_cnae.group(1).strip()

                # --- B. AMPLIACIONES DE CAPITAL ---
                if "Ampliaci" in act_types:
                    m_res = RE_RESULTANTE.search(description)
                    if m_res:
                        update_fields["capital_social_raw"] = m_res.group(1).strip()
                    else:
                        m_amp = RE_AMPLIACION.search(description)
                        if m_amp: update_fields["capital_social_raw"] = m_amp.group(1).strip()

                # --- C. DOMICILIO (Constitución o Cambio) ---
                target_address = None
                if "Constitu" in act_types:
                    m_dom = RE_DOMICILIO.search(description)
                    if m_dom: target_address = m_dom.group(1).strip()
                elif "Cambio de domicilio" in act_types:
                    m_dom = RE_CAMBIO_DOMICILIO.search(description)
                    if m_dom: target_address = m_dom.group(1).strip()

                if target_address:
                    update_fields["address"] = target_address
                    # Extraer Municipio e intentar Geolocalizar
                    m_mun = re.search(r"\(([^)]+)\)$", target_address)
                    if m_mun:
                        municipio = m_mun.group(1).strip()
                        update_fields["municipality"] = municipio
                        clean_addr = target_address.split("(")[0].strip()
                        lat, lng = geocode_address(f"{clean_addr}, {municipio}")
                        if lat and lng:
                            update_fields["lat_num"] = lat
                            update_fields["lng_num"] = lng
                            print(f"    [GEO] {municipio} -> {lat}, {lng}")

                # --- D. EJECUTAR UPDATE SI HAY CAMBIOS ---
                if update_fields:
                    # Si tenemos cnae_code pero no label, intentamos buscarlo en el mapeo (cargado al inicio)
                    if "cnae_code" in update_fields and "cnae_label" not in update_fields:
                        code = update_fields["cnae_code"]
                        # Buscamos en la base de datos (puedes cargar un dict al inicio para más velocidad si hay muchos)
                        cur.execute("SELECT label_2009 FROM cnae_2009_2025 WHERE cnae_2009 = %s OR cnae_2025 = %s LIMIT 1", (code, code))
                        row_cnae = cur.fetchone()
                        if row_cnae:
                            update_fields["cnae_label"] = row_cnae["label_2009"]

                    placeholders = ", ".join([f"{k} = %s" for k in update_fields.keys()])
                    values = list(update_fields.values())
                    values.append(company_id)
                    query = f"UPDATE companies SET {placeholders} WHERE id = %s"
                    cur.execute(query, tuple(values))
                    total_fechas += 1
                    print(f"    [DATA] company {company_id} updated: {', '.join(update_fields.keys())}")

                # ── 2. Administradores ────────────────────────────────────
                admins = extract_administrators(description)
                for adm in admins:
                    # Avoid duplicates: same company + post + name + position
                    cur.execute("""
                        SELECT id FROM company_administrators
                        WHERE company_id = %s AND post_id = %s AND name = %s AND position = %s
                        LIMIT 1
                    """, (company_id, post_id, adm["name"], adm["position"]))
                    if not cur.fetchone():
                        cur.execute("""
                            INSERT INTO company_administrators
                                (company_id, post_id, action, position, name)
                            VALUES (%s, %s, %s, %s, %s)
                        """, (company_id, post_id, adm["action"], adm["position"], adm["name"]))
                        total_admins = total_admins + 1

                # ── 3. Mark post as processed ─────────────────────────────
                cur.execute(
                    "UPDATE borme_posts SET admin_extracted = 1 WHERE id = %s",
                    (post_id,)
                )

            conn.commit()
            print(f"\n[OK] Done. Posts: {total_posts} | Admins inserted: {total_admins} | Fechas updated: {total_fechas}")

    except Exception as e:
        print(f"[!] Error: {e}")
        conn.rollback()
        raise
    finally:
        conn.close()


if __name__ == "__main__":
    run()
