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
    content = content.replace("Empieza en menos de 10 segundos · Sin tarjeta · Sin permanencia", "Empieza a contactar en menos de 10 segundos")

    # 2. MODAL - Add urgency line
    modal_text_to_replace = '<p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.1rem; font-weight: 700;">Si no actúas ahora, perderás estos clientes</p>'
    new_modal_text = '<p style="color: #dc2626; margin-bottom: 0.75rem; font-size: 1.1rem; font-weight: 700;">Si no actúas ahora, perderás estos clientes</p>\n            <p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.05rem; font-weight: 600;">Cada minuto que pasa, aumenta la probabilidad de perder estos clientes</p>'
    content = content.replace(modal_text_to_replace, new_modal_text)

    # 3. CTA FINAL
    content = content.replace("Acceder ahora y cerrar estos clientes antes que tu competencia", "Acceder ahora y empezar a contactar estos clientes")
    # In case it was the previous version:
    content = content.replace("Acceder a estos clientes antes que tu competencia\n                    </a>", "Acceder ahora y empezar a contactar estos clientes\n                    </a>")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
