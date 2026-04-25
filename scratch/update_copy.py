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
    content = content.replace("están contratando proveedores ahora mismo", "están buscando proveedores ahora mismo")

    # 2. Hero Copy
    # In some files it's "Estas empresas tienen necesidades activas — si no contactas ahora, otro proveedor se quedará con la oportunidad."
    content = content.replace(
        "Estas empresas tienen necesidades activas — si no contactas ahora, otro proveedor se quedará con la oportunidad.",
        "Si no contactas ahora, otro proveedor cerrará estas oportunidades en las próximas horas."
    )

    # 3. Urgency Block
    content = content.replace(
        "Estas empresas están siendo contactadas ahora mismo",
        "Varias de estas empresas están siendo contactadas en este momento"
    )
    content = content.replace(
        "Cada día aparecen nuevas oportunidades y otras desaparecen. No dejes que tu competencia se adelante.",
        "Algunas desaparecerán en las próximas horas."
    )

    # 4. Overlay Text
    # We had: "Estás viendo solo una parte — desbloquea el acceso completo para contactar antes que otros proveedores" 
    # Or: "Estás viendo solo una parte — desbloquea el acceso completo"
    overlay_text_pattern = re.compile(r'<p style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem;">(Estás viendo solo una parte.*?)</p>')
    content = overlay_text_pattern.sub(r'<p style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem;">Estas empresas están activas ahora mismo — accede antes que otros proveedores</p>', content)

    # 5. CTA Text Replacements
    # We replace "Acceder antes que tu competencia", "Ver todas las empresas ahora", "Entrar al Radar ahora" 
    # with "Acceder antes que otros proveedores" inside <a> tags.
    cta_texts = [
        "Acceder antes que tu competencia",
        "Ver todas las empresas ahora",
        "Entrar al Radar ahora"
    ]
    for cta_text in cta_texts:
        content = content.replace(f">{cta_text}\n", f">Acceder antes que otros proveedores\n")
        content = content.replace(f">\n                        {cta_text}\n", f">\n                        Acceder antes que otros proveedores\n")
        content = content.replace(f">\n                                        {cta_text}\n", f">\n                                        Acceder antes que otros proveedores\n")
        content = content.replace(f">\n                    {cta_text}\n", f">\n                    Acceder antes que otros proveedores\n")

    # 6. Micro copy below hero actions
    # We find:
    # <div class="ae-radar-page__hero-actions">
    #      <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
    #          Acceder antes que otros proveedores
    #      </a>
    # </div>
    # And add the microcopy after it.
    hero_actions_pattern = re.compile(r'(<div class="ae-radar-page__hero-actions">.*?</div>)', re.DOTALL)
    
    # We don't want to add it multiple times if the script runs multiple times
    if "La mayoría de usuarios recupera la inversión con su primer cliente" not in content:
        def repl_hero_actions(m):
            return m.group(1) + '\n                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.75rem; font-weight: 500; text-align: center;">La mayoría de usuarios recupera la inversión con su primer cliente</p>'
        
        content = hero_actions_pattern.sub(repl_hero_actions, content, count=1)

        # 7. Micro copy below final CTA
        # The final CTA looks like:
        # <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="...">
        #      Acceder antes que otros proveedores
        # </a>
        # Inside the Final CTA Section. Let's find it.
        final_cta_pattern = re.compile(r'(<h2 style="font-size: 2.5rem;.*?</a>)', re.DOTALL)
        def repl_final_cta(m):
            return m.group(1) + '\n                    <p style="font-size: 0.95rem; color: rgba(255,255,255,0.7); margin-top: 1.25rem; font-weight: 500;">La mayoría de usuarios recupera la inversión con su primer cliente</p>'
        content = final_cta_pattern.sub(repl_final_cta, content, count=1)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
        
    print(f"Processed {filepath}")
