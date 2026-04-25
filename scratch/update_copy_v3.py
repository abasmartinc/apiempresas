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

    # 1. Hero Copy (Second line)
    content = content.replace(
        "Si no contactas hoy, otro proveedor cerrará estas oportunidades antes que tú.",
        "Cada una es una oportunidad real de venta — si no actúas hoy, otro proveedor cerrará estos clientes"
    )

    # 2. Alertas - Remove duplicates
    # We find all occurrences of "<!-- Bloque de Urgencia -->...</div>"
    urgency_block_pattern = re.compile(r'<!-- Bloque de Urgencia -->\s*<div style="background: #fef2f2;.*?</div>\s*</div>', re.DOTALL)
    urgency_blocks = urgency_block_pattern.findall(content)
    if len(urgency_blocks) > 1:
        # keep the first one, remove the rest
        first_idx = content.find('<!-- Bloque de Urgencia -->')
        if first_idx != -1:
            end_of_first = content.find('</div>', content.find('</div>', first_idx) + 1) + 6
            # Remove all other urgency blocks after the first one
            rest_of_content = content[end_of_first:]
            rest_of_content = urgency_block_pattern.sub('', rest_of_content)
            content = content[:end_of_first] + rest_of_content

    # 3 & 4. Modal / Overlay updates
    # We need to rebuild the overlay completely
    overlay_pattern = re.compile(r'<div class="ae-radar-page__lead-overlay-cta">.*?</div>', re.DOTALL)
    new_overlay = """<div class="ae-radar-page__lead-overlay-cta">
                    <p style="font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; font-size: 1.3rem;">Estas empresas están activas ahora mismo</p>
                    <p style="color: #64748b; margin-bottom: 0.25rem; font-size: 1rem; font-weight: 500;">Otros proveedores ya están intentando cerrar estas oportunidades</p>
                    <p style="color: #ef4444; margin-bottom: 1rem; font-size: 1rem; font-weight: 700;">Si no actúas ahora, perderás estas oportunidades frente a tu competencia</p>
                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
                        Acceder ahora antes que otros proveedores
                    </a>
                    <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.75rem; font-weight: 500; text-align: center;">La mayoría de usuarios recupera la inversión con su primer cliente</p>
                    <p style="font-size: 0.85rem; color: #1e293b; margin-top: 0.25rem; font-weight: 700; text-align: center;">Estas empresas están activas ahora mismo — accede antes que otros proveedores</p>
                </div>"""
    
    content = overlay_pattern.sub(new_overlay, content, count=1)

    # 5. CTA Final
    content = content.replace("Accede ahora antes que tu competencia cierre estas oportunidades", "Accede ahora o perderás estas oportunidades en las próximas horas")
    content = content.replace("La mayoría de usuarios recupera la inversión con su primer cliente", "La mayoría de usuarios consigue su primer cliente en días")

    # 6. Bloque Excel
    # Add text below excel button
    # The excel button usually looks like: <a href="<?= site_url('excel/preview?... class="ae-radar-page__excel-btn js-loading-btn">...</a>
    # We can inject a paragraph after it.
    excel_btn_pattern = re.compile(r'(<a href="<\?= site_url\(\'excel/preview\?[^"]+"\s*class="ae-radar-page__excel-btn js-loading-btn">\s*Descargar listado[^<]+</a>)', re.DOTALL)
    def repl_excel_btn(m):
        return m.group(1) + '\n                            <p style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem; text-align: center; font-weight: 500;">Descarga el listado y empieza a contactar hoy mismo</p>'
    content = excel_btn_pattern.sub(repl_excel_btn, content)

    # 7. Limpieza final
    content = content.replace("explorar", "contactar")
    content = content.replace("descubrir", "contactar")
    content = content.replace("información", "ventas")

    # Also Capitalized versions just in case
    content = content.replace("Explorar", "Contactar")
    content = content.replace("Descubrir", "Contactar")
    content = content.replace("Información", "Ventas")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
