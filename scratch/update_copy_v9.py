import os
import re

files_to_process = [
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php"
]

for filepath in files_to_process:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1 & 2. CTA HERO & CTA INTERMEDIO
    # We just replace the exact text
    content = content.replace("Acceder a estos clientes antes que tu competencia", "Ver estas empresas antes que tu competencia")

    # 3. CTA FINAL
    content = content.replace("Acceder ahora y empezar a contactar estos clientes", "Acceder ahora y contactar estas empresas antes que otros")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
