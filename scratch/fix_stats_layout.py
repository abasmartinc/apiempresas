import os
import re

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
]

css_files = [
    r'd:\laragon\www\apiempresas\public\css\radar_new_companies.css',
    r'd:\laragon\www\apiempresas\public\css\radar_new_companies_period.css',
    r'd:\laragon\www\apiempresas\public\css\radar_new_companies_province.css',
    r'd:\laragon\www\apiempresas\public\css\radar_new_companies_sector.css',
]

def fix_stats_section(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()
    
    # 1. Remove stats-wrap if it exists
    c = c.replace('<div class="ae-radar-page__stats-wrap">', '')
    # Since we removed 1 div, we must remove 1 closing div
    # The closing div is usually before </section>
    c = re.sub(r'</div>\s*</section>', '</section>', c)
    
    # 2. Ensure section has no background/padding if it was there
    c = c.replace('class="ae-radar-page__section container"', 'class="ae-radar-page__section container" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important;"')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed Stats section in {f}')

def fix_css(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()
    
    # 1. Remove max-width from stats-wrap and stats
    c = re.sub(r'.ae-radar-page__stats-wrap\s*{.*?}', '', c, flags=re.DOTALL)
    c = re.sub(r'max-width:\s*860px;', 'max-width: none !important;', c)
    
    # 2. Ensure stats grid is 100% width
    c = c.replace('.ae-radar-page__stats {', '.ae-radar-page__stats {\n    width: 100% !important;\n    max-width: none !important;')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed CSS in {f}')

for f in files:
    fix_stats_section(f)

for f in css_files:
    fix_css(f)
