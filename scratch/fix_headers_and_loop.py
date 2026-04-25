import re
import os

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

# The common styles and structure
SHINE_ANIM = '<style>@keyframes shine { to { background-position: 200% center; } } @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }</style>'
GRADIENT_LINE = '<div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6); background-size: 200% auto; animation: shine 3s linear infinite;"></div>'
SHELL_STYLE = 'background: #f8fbff !important; border: 1px solid rgba(59,130,246,0.15) !important; border-radius: 2rem !important; padding: 3rem 2.5rem !important; position: relative !important; overflow: hidden !important; box-shadow: 0 10px 50px -12px rgba(59,130,246,0.12), 0 4px 12px rgba(0,0,0,0.02) !important;'

def fix_file(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()

    # 1. Detect the leads section
    # Usually starts with <section id="leads-b2b-recientes" ...>
    # In some files it might be different. Let's find the section.
    section_start = c.find('<section id="leads-b2b-recientes"')
    if section_start == -1:
        # Fallback if ID is missing
        section_start = c.find('<section class="ae-radar-page__section ae-radar-page__section--leads')
    
    if section_start == -1: return

    # Find the shell start or create it
    # We want everything from the header to the end of the paywall/grid inside the shell
    
    # Let's rebuild the section content from scratch to be safe
    # We need to preserve the PHP variables and loops
    
    # 2. Limit the freeLeads loop to max 6 items if blurred
    c = c.replace('foreach ($freeLeads as $index => $co)', 'foreach (array_slice($freeLeads, 0, 6) as $index => $co)')

    # 3. Fix duplicated headers in radar_new_companies.php
    if 'radar_new_companies.php' in f:
        # Remove the messed up header
        messy_header_regex = r'<div style="padding-bottom: 1\.75rem; position: relative;">.*?</h2>\s*</div>\s+<div class="ae-radar-page__live-badge".*?</h2>\s*</div>'
        c = re.sub(messy_header_regex, '', c, flags=re.DOTALL)
        
        # Ensure the shell exists
        if 'ae-radar-page__leads-shell' not in c:
            shell_insertion = f'<div class="ae-radar-page__leads-shell" style="{SHELL_STYLE}">\n            {SHINE_ANIM}\n            {GRADIENT_LINE}'
            c = c.replace('<section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">', 
                          f'<section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">\n            {shell_insertion}')
            # We need to close the shell at the end of the section (before </section>)
            # But the section might end after paywall.
            # Let's find the closing </section>
            parts = c.split('</section>')
            # Find the index of the part containing leads-b2b-recientes
            for i, part in enumerate(parts):
                if 'id="leads-b2b-recientes"' in part:
                    parts[i] = part + '\n            </div>'
                    break
            c = '</section>'.join(parts)

    # 4. Standardize the header title across all files to "Leads B2B Recientes"
    c = re.sub(r'Últimas 10 empresas detectadas', 'Leads B2B <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recientes</span>', c)
    
    # Ensure there's only ONE header block
    # If there are two header blocks (one with style, one with class), remove the second one.
    c = c.replace('<h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">\n                    Leads B2B <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recientes</span>\n                </h2>', '')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed {f}')

for f in files:
    fix_file(f)
