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

    # 1. Fix Urgency Block Design
    # We will find the urgency block and replace its inline styles to look like a solid left-aligned notification
    urgency_block_pattern = re.compile(r'(<!-- Bloque de Urgencia -->\s*)<div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 1\.75rem; border-radius: 1rem; color: #991b1b; margin: 3rem [^;]+;[^>]+>', re.DOTALL)
    
    # Also handle the case where it might just be `margin: 3rem 0;`
    urgency_block_pattern_2 = re.compile(r'(<!-- Bloque de Urgencia -->\s*)<div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 1\.75rem; border-radius: 1rem; color: #991b1b; margin: 3rem 0;[^>]+>', re.DOTALL)
    
    new_urgency_style = r'\1<div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #ef4444; padding: 1.25rem 1.5rem; border-radius: 0.5rem; color: #991b1b; margin: 1.5rem 0 2.5rem 0; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">'

    content = urgency_block_pattern.sub(new_urgency_style, content)
    content = urgency_block_pattern_2.sub(new_urgency_style, content)

    # 2. Fix ae-radar-page__leads-header alignment
    # The header has `justify-content: space-between` by default in the CSS file.
    # We can inject an inline style or a small style block to override it.
    # We will inject a <style> snippet right after the leads section opens
    section_leads_pattern = re.compile(r'(<section [^>]*class="[^"]*ae-radar-page__section--leads[^"]*"[^>]*>)')
    
    css_fix = """
            <style>
                .ae-radar-page__leads-header {
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    gap: 1.25rem !important;
                }
                .ae-radar-page__leads-header .ae-radar-page__live-badge {
                    margin: 0 !important;
                    align-self: flex-start !important;
                }
                .ae-radar-page__leads-header > div:first-child {
                    max-width: 100% !important;
                }
            </style>"""
            
    # Remove old injected fix if present
    content = re.sub(r'<style>\s*\.ae-radar-page__leads-header\s*{.*?</style>', '', content, flags=re.DOTALL)
    
    def inject_css(m):
        return m.group(1) + css_fix

    content = section_leads_pattern.sub(inject_css, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
