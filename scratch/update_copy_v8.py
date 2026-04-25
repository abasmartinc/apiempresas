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

    # 1. HERO - Change subtext
    content = content.replace("Empieza a contactar en menos de 10 segundos", "Más de 40 empresas nuevas aparecen cada día — las primeras en contactar son las que cierran")

    # 2. ALERTA - Change main text
    content = content.replace("Varias de estas empresas están siendo contactadas ahora mismo", "Varias de estas empresas dejarán de estar disponibles en las próximas horas")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
