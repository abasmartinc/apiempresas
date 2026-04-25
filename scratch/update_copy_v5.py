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

    # 1. & 2. CTA Hero and Subtext
    # Find the hero actions block
    hero_actions_pattern = re.compile(r'(<div class="ae-radar-page__hero-actions">.*?<a[^>]+>)\s*Conseguir estos clientes ahora\s*</a>\s*<p[^>]+>.*?</p>', re.DOTALL)
    
    def repl_hero(m):
        return m.group(1) + '\n                        Acceder a estos clientes antes que tu competencia\n                    </a>\n                    <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.75rem; font-weight: 500; text-align: center;">Empieza en menos de 10 segundos · Sin tarjeta · Sin permanencia</p>'
    
    content = hero_actions_pattern.sub(repl_hero, content, count=1)

    # 3. Bloque Leads (Inline Overlay)
    content = content.replace("Estas empresas están activas ahora mismo — accede antes que otros proveedores", "Estas empresas están activas ahora mismo — accede antes de que otros proveedores las contacten")
    # And change the button in the inline overlay if it says "Conseguir estos clientes ahora"
    # Actually, let's just do a specific replace for the inline overlay button
    inline_overlay_btn_pattern = re.compile(r'(<div class="ae-radar-page__lead-overlay-cta"[^>]+>.*?<a[^>]+>)\s*Conseguir estos clientes ahora\s*</a>', re.DOTALL)
    def repl_inline_overlay(m):
        # We replace the button text with the same as hero for consistency or what did the user say?
        # User didn't specify changing the inline CTA text, just the text BELOW the button.
        # But wait, they said "2. CTA PRINCIPAL — UNIFICAR MENSAJE: REEMPLAZAR TODOS los CTAs principales por 'Acceder antes que otros proveedores'" in the PREVIOUS step,
        # but now they say "CTA HERO - CAMBIO CRÍTICO: Acceder a estos clientes antes que tu competencia".
        # Let's just leave the inline button as is or change it to "Acceder a estos clientes antes que tu competencia" too.
        # Actually, let's just replace all "Conseguir estos clientes ahora" with "Acceder a estos clientes antes que tu competencia" except the final CTA.
        return m.group(1) + '\n        Acceder a estos clientes antes que tu competencia\n    </a>'
    
    content = inline_overlay_btn_pattern.sub(repl_inline_overlay, content)

    # 4. & 5. Modal Updates
    content = content.replace("Si no actúas ahora, perderás estos clientes frente a tu competencia</p>", "Si no actúas ahora, perderás estos clientes frente a tu competencia</p>\n            <p style=\"color: #dc2626; margin-bottom: 2rem; font-size: 1.05rem; font-weight: 600;\">Cada minuto que pasa aumenta la probabilidad de que otro proveedor cierre estas oportunidades</p>")
    content = content.replace("Acceder ahora y contactar primero", "Acceder ahora y contactar antes que otros proveedores")

    # 6. CTA Final
    final_cta_pattern = re.compile(r'(<!-- Final CTA Section -->.*?<a[^>]+>)\s*Conseguir estos clientes ahora\s*</a>', re.DOTALL)
    def repl_final(m):
        return m.group(1) + '\n                        Acceder ahora y cerrar estos clientes antes que tu competencia\n                    </a>'
    content = final_cta_pattern.sub(repl_final, content, count=1)

    # 7. Excel Block - already added previously, just double check it's there
    if "Descarga el listado y empieza a contactar hoy mismo" not in content:
        excel_btn_pattern = re.compile(r'(<a href="<\?= site_url\(\'excel/preview\?[^"]+"\s*class="ae-radar-page__excel-btn js-loading-btn">\s*Descargar listado[^<]+</a>)', re.DOTALL)
        def repl_excel_btn(m):
            return m.group(1) + '\n                            <p style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem; text-align: center; font-weight: 500;">Descarga el listado y empieza a contactar hoy mismo</p>'
        content = excel_btn_pattern.sub(repl_excel_btn, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
