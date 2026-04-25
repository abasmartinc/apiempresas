import re
import os

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

def fix_template(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()

    # 1. Standardize Header Data (Keep dynamic titles if they exist)
    title_match = re.search(r'<h2.*?>(.*?)</h2>', c, re.DOTALL)
    current_title = title_match.group(1) if title_match else 'Leads B2B <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recientes</span>'
    
    # Clean up the title if it has messed up span tags or is the "Últimas 10" one
    if 'Últimas 10' in current_title:
        current_title = 'Leads B2B <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recientes</span>'

    PREMIUM_HEADER = f"""
            <div class="ae-radar-page__leads-shell" style="background: #f8fbff !important; border: 1px solid rgba(59,130,246,0.15) !important; border-radius: 2rem !important; padding: 3rem 2.5rem !important; position: relative !important; overflow: hidden !important; box-shadow: 0 10px 50px -12px rgba(59,130,246,0.12), 0 4px 12px rgba(0,0,0,0.02) !important;">
            <style>
                @keyframes shine {{ to {{ background-position: 200% center; }} }} 
                @keyframes pulse-soft {{ 0%, 100% {{ opacity: 1; }} 50% {{ opacity: 0.7; }} }}
                .ae-radar-page__lead-card.is-blurred {{
                    filter: blur(8px) grayscale(0.2) !important;
                    transform: scale(0.98) !important;
                    opacity: 0.55;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.35), 0 8px 16px -4px rgba(99,102,241,0.25) !important;
                    border-color: rgba(59,130,246,0.3) !important;
                }}
            </style>
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6); background-size: 200% auto; animation: shine 3s linear infinite;"></div>
            
            <div style="padding-bottom: 1.75rem; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 0.3rem 0.8rem; border-radius: 999px;">
                            <span style="width: 6px; height: 6px; background: #3b82f6; border-radius: 50%; display: inline-block; animation: pulse 2s infinite;"></span>
                            Muestra comercial en tiempo real
                        </span>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #10b981; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); padding: 0.3rem 0.8rem; border-radius: 999px;">
                        <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                        Actualizado hoy
                    </span>
                </div>
                <h2 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin: 0 0 0.6rem; letter-spacing: -0.025em; line-height: 1.15;">
                    {current_title}
                </h2>
                <p style="color: #64748b; font-size: 1rem; margin: 0; line-height: 1.65; max-width: 680px;">
                    Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                </p>
            </div>
"""

    # 1. Remove ANY existing shell/header/style code inside the leads section
    # Use the leads section class as anchor
    leads_section_pattern = r'<section[^>]*?class="[^"]*?ae-radar-page__section--leads[^"]*?".*?>'
    match = re.search(leads_section_pattern, c)
    if not match: return
    
    start_pos = match.end()
    
    # Remove everything until the next meaningful content (like urgency block or empty state)
    content_pattern = r'<\?php if \(\$is_low_results.*?<\?php endif; \?>|<!-- Bloque de Urgencia -->'
    content_match = re.search(content_pattern, c[start_pos:], re.DOTALL)
    if content_match:
        c = c[:start_pos] + "\n" + PREMIUM_HEADER + c[start_pos + content_match.start():]
    
    # 2. Limit the loop
    c = c.replace('foreach ($freeLeads as $index => $co)', 'foreach (array_slice($freeLeads, 0, 6) as $index => $co)')

    # 3. Ensure shell is closed
    if '</div>\n        </section>' not in c:
        # Find the end of the section
        end_idx = c.find('</section>', start_pos)
        if end_idx != -1:
             c = c[:end_idx] + '            </div>\n        ' + c[end_idx:]

    # 4. Final deduplication cleanup
    # Remove any extra H2s that might have survived
    c = re.sub(r'<div class="ae-radar-page__leads-shell".*?<div class="ae-radar-page__leads-shell"', r'<div class="ae-radar-page__leads-shell"', c, flags=re.DOTALL)

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed {f}')

for f in files:
    fix_template(f)
