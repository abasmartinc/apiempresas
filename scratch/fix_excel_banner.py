import os
import re

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
]

blue_gradient = 'linear-gradient(135deg, #122347 0%, #19366d 55%, #2d57c7 100%)'
section_style = f'background: {blue_gradient} !important; border-radius: 2rem !important; overflow: hidden !important; border: 1px solid rgba(255,255,255,0.1) !important; box-shadow: 0 24px 60px -30px rgba(15, 23, 42, 0.34) !important;'

def fix_excel_section(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()
    
    # Regex to find the whole section
    # We want to replace the current section with a simplified one that doesn't have the inner card
    
    pattern = re.compile(r'<section[^>]*?ae-radar-page__section--excel.*?</section>', re.DOTALL)
    
    def replacement(match):
        content = match.group(0)
        # Extract the button URL and text if possible, or just rebuild it based on existing variables
        # Let's just simplify the existing content by removing the excel-box wrapper and adding styles to section
        
        # 1. Add styles to the section
        new_section = content.replace('<section class="ae-radar-page__section ae-radar-page__section--excel container"', 
                                     f'<section class="ae-radar-page__section ae-radar-page__section--excel container" style="{section_style}"')
        
        # 2. Remove the inner box
        new_section = new_section.replace('<div class="ae-radar-page__excel-box">', '')
        # Remove the closing div of excel-box (it should be the one before </section>)
        new_section = re.sub(r'</div>\s*</section>', '</section>', new_section)
        
        # 3. Fix button wrap
        new_section = new_section.replace('max-width: 400px;', 'max-width: 700px;')
        new_section = new_section.replace('class="ae-radar-page__excel-btn', 'class="ae-radar-page__excel-btn" style="white-space: nowrap !important;"')
        # Clean up double class if it happened
        new_section = new_section.replace('class="ae-radar-page__excel-btn" style="white-space: nowrap !important;" js-loading-btn"', 
                                         'class="ae-radar-page__excel-btn js-loading-btn" style="white-space: nowrap !important;"')

        return new_section

    c = pattern.sub(replacement, c)

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed Excel section in {f}')

for f in files:
    fix_excel_section(f)
