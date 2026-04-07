import re

# Same regex as in 06_extract_from_borme_text.py
RE_OBJETO = re.compile(
    r"[Oo]bjeto\s+social\s*[:\-]\s*(.*?)(?=\. [Dd]omicilio| \.? [Cc]apital| \.? [Dd]atos|\.? $)", 
    re.DOTALL | re.IGNORECASE
)
RE_DOMICILIO = re.compile(
    r"[Dd]omicilio\s*[:\-]\s*(.*?)(?=\.? [Cc]apital| \.? [Dd]atos| \.? [Dd]eclaración|\.? $)", 
    re.DOTALL | re.IGNORECASE
)
RE_CAPITAL = re.compile(r"[Cc]apital\s*[:\-]\s*([\d.,]+)\s*[Ee]uros", re.IGNORECASE)

samples = [
    "Constitucion. Comienzo de operaciones: 12.11.24. Objeto social: 6810.- Compraventa de bienes inmobiliarios por cuenta propia. 4399.- Otras actividades de construccion especializada. Domicilio: C/ AZORIN 34A 2 8 (JAVEA). Capital: 3.000,00 Euros. Nombramientos. Adm. Unico: MARINES COSTA LILIANA.",
    "Constitucion. Comienzo de operaciones: 13.11.24. Objeto social: Fontaneria, instalaciones de sistemas de calefaccion y aire acondicionado. Domicilio: C/ GARCILASO DE LA VEGA 56 - PLANTA BAJA 18 (MONFORTE DEL CID). Capital: 3.000,00 Euros. Socio Unico: MENENDEZ DIAZ OSVALDO.",
    "Constitucion. Comienzo de operaciones: 28.11.24. Objeto social: La tenencia por cuenta propia de acciones... Domicilio: C/ VILLA 2 (CREVILLENT). Capital: 309.521,00 Euros. Nombramientos."
]

for i, text in enumerate(samples):
    print(f"\n--- Sample {i+1} ---")
    obj = RE_OBJETO.search(text)
    dom = RE_DOMICILIO.search(text)
    cap = RE_CAPITAL.search(text)
    
    print(f"Objeto: {obj.group(1).strip() if obj else 'MISSING'}")
    print(f"Domicilio: {dom.group(1).strip() if dom else 'MISSING'}")
    print(f"Capital: {cap.group(1).strip() if cap else 'MISSING'}")
