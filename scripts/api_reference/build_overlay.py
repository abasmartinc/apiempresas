import re, json, sys
from bs4 import BeautifulSoup
from markdownify import markdownify as mdf

SRC = sys.argv[1]; OUT = sys.argv[2]
# Dominio de los enlaces site_url(): apiempresas.es (ES) o spaincompanyapi.com (EN)
HOST = sys.argv[3] if len(sys.argv) > 3 else 'https://apiempresas.es'
src = open(SRC, encoding='utf-8').read()
src = re.sub(r"<\?=\s*site_url\('([^']*)'\)\s*\?>", HOST + r"/\1", src)
src = re.sub(r"<\?=\s*base_url\('([^']*)'\)\s*\?>", r"https://apiempresas.es/\1", src)
src = re.sub(r"<\?(php|=).*?\?>", "", src, flags=re.S)
soup = BeautifulSoup(src, 'html.parser')

def md(html):
    t = mdf(html, heading_style='ATX', bullets='-', code_language='')
    t = re.sub(r'\n{3,}', '\n\n', t).strip()
    return t

def split_json_blocks(text):
    """Devuelve (peticion, respuesta) a partir de un bloque con // Petición y // Respuesta
    (o // Request y // Response), o (None, json)."""
    t = text.strip()
    req = None
    if re.search(r'//\s*(Respuesta|Response|Petici[oó]n|Request)', t):
        parts = re.split(r'//\s*(?:Respuesta|Response)[^\n]*\n', t, maxsplit=1)
        if len(parts) == 2:
            req = re.sub(r'//\s*(?:Petici[oó]n|Request)[^\n]*\n', '', parts[0]).strip()
            t = parts[1].strip()
    def load(s):
        if s is None: return None
        s = re.sub(r'^\s*//.*$', '', s, flags=re.M).strip()
        try: return json.loads(s)
        except Exception: return None
    return load(req), load(t)

endpoints = {}
for sec in soup.select('section.docs-section'):
    heads = sec.select('.endpoint-header')
    for idx, h in enumerate(heads):
        badge = h.select_one('.http-badge'); code = h.find('code')
        if not badge or not code: continue
        method = badge.get_text(strip=True).lower(); path = code.get_text(strip=True)
        key = method + ' ' + path
        html_parts, req_ex, res_ex = [], None, None
        # Introducción de la sección (párrafos antes del primer endpoint) solo para el primero
        if idx == 0:
            for sib in sec.children:
                if getattr(sib, 'name', None) is None: continue
                if sib is h or (sib.get('class') and 'endpoint-header' in sib.get('class')) or sib.find(class_='endpoint-header'): break
                if sib.name in ('h2',): continue
                html_parts.append(str(sib))
        # Contenido tras la cabecera hasta la siguiente cabecera
        for sib in h.next_siblings:
            if getattr(sib, 'name', None) is None: continue
            if sib.get('class') and 'endpoint-header' in sib.get('class'): break
            if sib.find(class_='endpoint-header'): break
            if sib.name == 'pre':
                r, j = split_json_blocks(sib.get_text())
                if j is not None and res_ex is None:
                    res_ex = j
                    if r is not None: req_ex = r
                    continue  # el ejemplo va al esquema, no se repite en el texto
            html_parts.append(str(sib))
        desc = md(''.join(html_parts))
        endpoints.setdefault(key, {'description': desc, 'response_example': res_ex, 'request_example': req_ex})

json.dump(endpoints, open(OUT, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
for k, v in endpoints.items():
    print(f"{k:32s} desc {len(v['description']):5d}  res {'sí' if v['response_example'] is not None else '--'}  req {'sí' if v['request_example'] is not None else '--'}")
