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

    # 1. Hero Subtitle
    content = content.replace("Si no contactas ahora, otro proveedor cerrará estas oportunidades en las próximas horas.", "Si no contactas hoy, otro proveedor cerrará estas oportunidades antes que tú.")

    # 2. Economic Block
    content = content.replace("generar entre", "generarte entre")

    # 3. Urgency Block
    content = content.replace("Varias de estas empresas están siendo contactadas en este momento", "Varias de estas empresas están siendo contactadas ahora mismo")
    content = content.replace("Algunas desaparecerán en las próximas horas.", "Algunas dejarán de estar disponibles en las próximas horas.")

    # 4. Modal/Overlay updates
    old_overlay_pattern = re.compile(r'<p style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem;">Estas empresas están activas ahora mismo — accede antes que otros proveedores</p>\s*<a href="<\?= site_url\(\'radar/preview\'\) \?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba\(59, 130, 246, 0.3\);">\s*Acceder antes que otros proveedores\s*</a>', re.DOTALL)
    
    new_overlay = """<p style="font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; font-size: 1.3rem;">Estas empresas están activas ahora mismo</p>
                    <p style="color: #64748b; margin-bottom: 1rem; font-size: 1rem; font-weight: 500;">Otros proveedores ya están intentando cerrar estas oportunidades</p>
                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
                        Acceder ahora antes que otros proveedores
                    </a>
                    <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.75rem; font-weight: 500; text-align: center;">La mayoría de usuarios recupera la inversión con su primer cliente</p>"""
    
    content = old_overlay_pattern.sub(new_overlay, content)

    # 5. CTA Final text changes
    content = content.replace("Accede a todas las empresas antes que otros proveedores", "Accede ahora antes que tu competencia cierre estas oportunidades")
    content = content.replace("⚡ Acceso inmediato · Sin tarjeta · Empieza en menos de 10 segundos", "Empieza en menos de 10 segundos · Sin tarjeta · Sin permanencia")
    
    # 6. Micro cleanup (explora, descubre -> contacta, cierra)
    # Be careful not to replace it inside code or variables
    content = content.replace("Explora los principales hubs", "Contacta los principales hubs")
    content = content.replace("Exploración rápida", "Cierre rápido")
    content = content.replace("Exploración por sector", "Clientes por sector")
    content = content.replace("Descubre cada día", "Contacta cada día")
    # Instead of just "Explorar" button text:
    content = content.replace(">Explorar<", ">Cerrar ventas<")
    content = content.replace(">Explorar Sector<", ">Cerrar Sector<")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
