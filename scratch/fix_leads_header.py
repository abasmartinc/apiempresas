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

    # Remove the CSS injected in fix_transition.py
    content = re.sub(r'<style>\s*\.ae-radar-page__leads-header\s*{.*?</style>', '', content, flags=re.DOTALL)

    # 1. Replace the simple header in radar_new_companies.php
    simple_header_pattern = re.compile(r'<div class="ae-radar-page__leads-header">\s*<h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Últimas 10 empresas detectadas</h2>\s*<div class="ae-radar-page__live-badge">\s*<span class="ae-radar-page__live-badge-dot"></span>\s*En tiempo real\s*</div>\s*</div>', re.DOTALL)
    
    new_simple_header = """<div class="ae-radar-page__leads-header" style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0;">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left" style="margin-bottom: 0;">Últimas 10 empresas detectadas</h2>
                    <div class="ae-radar-page__live-badge" style="margin: 0; background: #ecfdf5; color: #059669; border-color: #a7f3d0;">
                        <span class="ae-radar-page__live-badge-dot" style="background: #10b981;"></span>
                        En tiempo real
                    </div>
                </div>
            </div>"""
    
    content = simple_header_pattern.sub(new_simple_header, content)

    # 2. Replace the complex header in the other files
    complex_header_pattern = re.compile(r'<div class="ae-radar-page__leads-header">\s*<div>\s*<div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot">.*?</div>\s*<h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">\s*Leads B2B Recientes\s*</h2>\s*<p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left">\s*(.*?)\s*</p>\s*</div>\s*<div class="ae-radar-page__live-badge">.*?</div>\s*</div>', re.DOTALL)
    
    def repl_complex_header(m):
        subtitle_text = m.group(1).strip()
        return f"""<div class="ae-radar-page__leads-header" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 0;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left" style="margin-bottom: 0; font-size: 2rem;">
                            Leads B2B Recientes
                        </h2>
                        <div class="ae-radar-page__live-badge" style="margin: 0; background: #ecfdf5; color: #059669; border-color: #a7f3d0; font-weight: 700;">
                            <span class="ae-radar-page__live-badge-dot" style="background: #10b981;"></span>
                            Muestra comercial en tiempo real
                        </div>
                    </div>
                    <p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left" style="max-width: 800px; margin: 0; font-size: 1.1rem;">
                        {subtitle_text}
                    </p>
                </div>"""
                
    content = complex_header_pattern.sub(repl_complex_header, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
