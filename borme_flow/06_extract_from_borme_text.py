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
from config import mysql_connect

# ──────────────────────────────────────────
#  REGEX PATTERNS
# ──────────────────────────────────────────

# Fecha de constitución:  "Comienzo de operaciones: 25.11.24"  or  "12.11.2024"
RE_FECHA = re.compile(
    r"[Cc]omienzo\s+de\s+operaciones\s*[:\-]\s*(\d{1,2}[./]\d{1,2}[./]\d{2,4})"
)

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

                # ── 1. Fecha de constitución ──────────────────────────────
                if "Constitu" in act_types and not post["fecha_constitucion"]:
                    m = RE_FECHA.search(description)
                    if m:
                        fecha = parse_date(m.group(1))
                        if fecha:
                            cur.execute(
                                "UPDATE companies SET fecha_constitucion = %s WHERE id = %s",
                                (fecha, company_id)
                            )
                            total_fechas = total_fechas + 1
                            print(f"    [FECHA] company {company_id} -> {fecha}")

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
