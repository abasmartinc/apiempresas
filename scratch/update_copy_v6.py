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

    # 1. CTA HERO (Ensure it's replaced globally just in case)
    content = content.replace("Conseguir estos clientes ahora", "Acceder a estos clientes antes que tu competencia")

    # 2. MODAL - Simplification
    # We find the inner content of the modal and replace it
    modal_content_pattern = re.compile(r'<p style="font-weight: 800; color: #0f172a; margin-bottom: 0\.5rem; font-size: 1\.5rem; letter-spacing: -0\.02em;">Estas empresas están activas ahora mismo</p>.*?(<a href="<\?= site_url\(\'radar/preview\'\) \?>")', re.DOTALL)
    
    simplified_modal_content = """<p style="font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; font-size: 1.5rem; letter-spacing: -0.02em;">Estas empresas están activas ahora mismo</p>
            <p style="color: #475569; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 500;">Otros proveedores ya están cerrando estas oportunidades</p>
            <p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.1rem; font-weight: 700;">Si no actúas ahora, perderás estos clientes</p>
            \\1"""
            
    content = modal_content_pattern.sub(simplified_modal_content, content, count=1)
    
    # 3. BLOQUE NEGRO
    # Find the H3 inside the economic block
    economic_block_pattern = re.compile(r'(<h3 style="font-size: 1\.85rem; font-weight: 900; line-height: 1\.2; background: linear-gradient\(to right, #4ade80, #22d3ee\); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">.*?</h3>)', re.DOTALL)
    
    def repl_economic_block(m):
        # ensure we don't add it twice
        return m.group(1) + '\n                    <p style="color: #94a3b8; font-size: 1.1rem; margin-top: 1rem; font-weight: 500;">Con 1 cliente cubres el coste mensual</p>'
        
    if "Con 1 cliente cubres el coste mensual" not in content:
        content = economic_block_pattern.sub(repl_economic_block, content, count=1)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
