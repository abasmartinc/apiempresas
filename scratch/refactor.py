import os
import re

files_to_process = [
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php"
]

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Update Hero subtitle
    # Find <p class="ae-radar-page__subtitle">...</p>
    subtitle_pattern = re.compile(r'<p class="ae-radar-page__subtitle">.*?</p>', re.DOTALL)
    new_subtitle = """<p class="ae-radar-page__subtitle" style="font-size: 1.25rem; font-weight: 600; margin-top: 1rem; color: #1e293b;">
                    +<?= number_format($conversion_count ?? 0, 0, ',', '.') ?> empresas en <?= esc($heading_location ?? 'España') ?> están contratando proveedores ahora mismo
                </p>"""
    content = subtitle_pattern.sub(new_subtitle, content, count=1)

    # 2. Update Hero copy
    hero_copy_pattern = re.compile(r'<p class="ae-radar-page__hero-copy" style="([^"]*)">.*?</p>', re.DOTALL)
    def repl_hero_copy(m):
        return f'<p class="ae-radar-page__hero-copy" style="{m.group(1)}">\n                    Estas empresas tienen necesidades activas — si no contactas ahora, otro proveedor se quedará con la oportunidad.\n                </p>'
    content = hero_copy_pattern.sub(repl_hero_copy, content, count=1)

    # 3. Leave only 1 CTA in Hero actions
    hero_actions_pattern = re.compile(r'<div class="ae-radar-page__hero-actions">.*?</div>', re.DOTALL)
    new_hero_actions = """<div class="ae-radar-page__hero-actions">
                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Acceder antes que tu competencia
                    </a>
                </div>"""
    content = hero_actions_pattern.sub(new_hero_actions, content, count=1)

    # 4. Update Economic Block Text
    economic_block_pattern = re.compile(r'<h3 style="font-size: 1.85rem; font-weight: 900; line-height: 1.2; background: linear-gradient\(to right, #4ade80, #22d3ee\); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">.*?</h3>', re.DOTALL)
    new_economic_text = '<h3 style="font-size: 1.85rem; font-weight: 900; line-height: 1.2; background: linear-gradient(to right, #4ade80, #22d3ee); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Estas empresas pueden generar entre <?= $potential_revenue_min ?>€ y <?= $potential_revenue_max ?>€ en ventas reales</h3>'
    content = economic_block_pattern.sub(new_economic_text, content, count=1)

    # Now, we need to extract the leads section and move it right below the Hero section (which ends after economic block or hero actions depending on the file, but we can put it right before the first <section> that is not the hero).
    
    # Let's extract the leads section
    leads_section_pattern = re.compile(r'(<section id="leads-b2b-recientes".*?</section>)', re.DOTALL)
    leads_match = leads_section_pattern.search(content)
    
    if not leads_match:
        print(f"Leads section not found in {filepath}")
        return
        
    leads_section_html = leads_match.group(1)
    
    # Remove it from its original place
    content = content.replace(leads_section_html, '')
    
    # Add Urgency block to the leads section
    urgency_block = """
        <!-- Bloque de Urgencia -->
        <div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 1.75rem; border-radius: 1rem; color: #991b1b; margin: 3rem auto; max-width: 800px; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <span style="font-size: 2rem;">⚠️</span>
            <div>
                <strong style="font-size: 1.2rem; display: block; margin-bottom: 0.25rem;">Varias de estas empresas están siendo contactadas en este momento</strong>
                <p style="margin: 0; opacity: 0.9;">Cada día aparecen nuevas oportunidades y otras desaparecen. No dejes que tu competencia se adelante.</p>
            </div>
        </div>
    """
    
    # Insert Urgency block right before the leads grid
    # Try finding <div class="ae-radar-page__lead-grid-wrap">
    leads_section_html = leads_section_html.replace('<div class="ae-radar-page__lead-grid-wrap">', urgency_block + '\n            <div class="ae-radar-page__lead-grid-wrap">')

    # Update Overlay CTA in leads section
    overlay_pattern = re.compile(r'<div class="ae-radar-page__lead-overlay-cta">.*?</div>', re.DOTALL)
    new_overlay = """<div class="ae-radar-page__lead-overlay-cta">
                        <p style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem;">Estás viendo solo una parte — desbloquea el acceso completo para contactar antes que otros proveedores</p>
                        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
                            Ver todas las empresas ahora
                        </a>
                    </div>"""
    
    if overlay_pattern.search(leads_section_html):
        leads_section_html = overlay_pattern.sub(new_overlay, leads_section_html, count=1)

    # Ensure all CTA buttons in leads section point to /radar/preview
    # (except the "Ver empresa" buttons which have class ae-radar-page__lead-btn)
    # The paywall actions:
    paywall_actions_pattern = re.compile(r'<div class="ae-radar-page__paywall-actions">.*?</div>\s*</div>', re.DOTALL)
    new_paywall_actions = """<div class="ae-radar-page__paywall-actions">
                                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                        <span>Entrar al Radar ahora</span>
                                    </a>
                                </div>
                            </div>"""
    if paywall_actions_pattern.search(leads_section_html):
        leads_section_html = paywall_actions_pattern.sub(new_paywall_actions, leads_section_html, count=1)
        
    # Also update premium-strip actions
    premium_strip_pattern = re.compile(r'<div class="ae-radar-page__premium-strip-actions">.*?</div>\s*</div>\s*</div>', re.DOTALL)
    new_premium_strip = """<div class="ae-radar-page__premium-strip-actions">
                                        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                            Acceder antes que tu competencia
                                        </a>
                                    </div>
                                </div>
                            </div>"""
    if premium_strip_pattern.search(leads_section_html):
        leads_section_html = premium_strip_pattern.sub(new_premium_strip, leads_section_html, count=1)

    # Now we need to insert the leads section right after the hero section.
    # The hero section ends with </section>. So we find the first </section> and insert it there.
    # But wait, there might be secondary panels in the hero we want to move down or remove.
    # Let's remove the <div class="ae-radar-page__hero-panel">...</div>
    hero_panel_pattern = re.compile(r'<div class="ae-radar-page__hero-panel">.*?</div>\s*</div>', re.DOTALL)
    content = hero_panel_pattern.sub('', content, count=1)
    
    # Remove ae-radar-page__hero-alt-downloads if exists
    alt_downloads_pattern = re.compile(r'<div class="ae-radar-page__hero-alt-downloads">.*?</div>', re.DOTALL)
    content = alt_downloads_pattern.sub('', content, count=1)

    # And we move the <div class="ae-radar-page__stats"> or <div class="ae-radar-page__stats-wrap"> to the SEO section below.
    stats_pattern = re.compile(r'(<div class="ae-radar-page__stats(-wrap)?">.*?(</a>\s*</div>\s*</div>|</a>\s*</div>\s*</div>\s*</div>))', re.DOTALL)
    stats_match = stats_pattern.search(content)
    stats_html = ''
    if stats_match:
        stats_html = stats_match.group(1)
        content = content.replace(stats_html, '')
        
    # Now find the first </section> to insert the leads section
    first_section_end = content.find('</section>')
    if first_section_end != -1:
        # insert leads_section_html after </section>
        insert_pos = first_section_end + len('</section>')
        content = content[:insert_pos] + '\n\n' + leads_section_html + '\n\n' + content[insert_pos:]
        
        # If we had stats_html, maybe put it back later, or right after leads section
        if stats_html:
             # Add it after the leads section
             insert_pos = content.find('</section>', insert_pos + 1)
             if insert_pos != -1:
                 content = content[:insert_pos + len('</section>')] + '\n\n<section class="ae-radar-page__section container">' + stats_html + '</section>\n\n' + content[insert_pos + len('</section>'):]

    # Final CTA Section Update
    # Find the section that has 'Final CTA Section'
    final_cta_pattern = re.compile(r'<!-- Final CTA Section -->.*?<section class="ae-radar-page__section container"[^>]*>.*?<h2[^>]*>.*?</h2>.*?<p[^>]*>.*?</p>.*?<a[^>]*>.*?</a>.*?</div>.*?</div>.*?</section>', re.DOTALL)
    new_final_cta = """<!-- Final CTA Section -->
    <section class="ae-radar-page__section container" style="margin-top: 4rem; padding-bottom: 6rem;">
            <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 1.5rem; padding: 4rem 2rem; text-align: center; color: white; position: relative; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="position: relative; z-index: 2;">
                    <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 1.5rem; letter-spacing: -0.02em; color: white;">Accede a todas las empresas antes que otros proveedores</h2>
                    <p style="font-size: 1.25rem; opacity: 0.8; max-width: 700px; margin: 0 auto 1.5rem; color: white;">⚡ Acceso inmediato · Sin tarjeta · Empieza en menos de 10 segundos</p>
                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1.25rem 3.5rem; font-size: 1.25rem; font-weight: 700; border-radius: 100px; background: white; color: #0f172a; box-shadow: 0 0 30px rgba(255,255,255,0.2);">
                        Entrar al Radar ahora
                    </a>
                </div>
                <!-- Subtle background glow -->
                <div style="position: absolute; top: -50%; left: -20%; width: 100%; height: 200%; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);"></div>
            </div>
        </section>"""
        
    if final_cta_pattern.search(content):
        content = final_cta_pattern.sub(new_final_cta, content, count=1)
        
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
        
    print(f"Processed {filepath}")

for f in files_to_process:
    process_file(f)
