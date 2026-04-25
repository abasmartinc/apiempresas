import re
import os

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

# The EXACT leads section template we want to inject
# We will replace the entire content between <section id="leads-b2b-recientes" ...> and its closing </section>

SECTION_TEMPLATE_START = r'<section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">'
SECTION_TEMPLATE_END = r'</section>'

PREMIUM_HEADER = """
            <div class="ae-radar-page__leads-shell" style="background: #f8fbff !important; border: 1px solid rgba(59,130,246,0.15) !important; border-radius: 2rem !important; padding: 3rem 2.5rem !important; position: relative !important; overflow: hidden !important; box-shadow: 0 10px 50px -12px rgba(59,130,246,0.12), 0 4px 12px rgba(0,0,0,0.02) !important;">
            <style>
                @keyframes shine { to { background-position: 200% center; } } 
                @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
                .ae-radar-page__lead-card.is-blurred {
                    filter: blur(8px) grayscale(0.2) !important;
                    transform: scale(0.98) !important;
                    opacity: 0.55;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.35), 0 8px 16px -4px rgba(99,102,241,0.25) !important;
                    border-color: rgba(59,130,246,0.3) !important;
                }
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
                    Leads B2B <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recientes</span>
                </h2>
                <p style="color: #64748b; font-size: 1rem; margin: 0; line-height: 1.65; max-width: 680px;">
                    Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                </p>
            </div>
"""

def polish_file(f):
    if not os.path.exists(f): return
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()

    # Find the section
    start_idx = c.find('<section id="leads-b2b-recientes"')
    if start_idx == -1: return
    
    # 1. Ensure only ONE shell starts right after the section
    # First, remove any existing shell/shine code that might be duplicated or messy
    c = re.sub(r'<div class="ae-radar-page__leads-shell".*?<!-- Bloque de Urgencia -->', '<!-- Bloque de Urgencia -->', c, flags=re.DOTALL)
    
    # Insert the premium header right after the section tag
    c = c.replace('<section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">', 
                  f'<section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">\n{PREMIUM_HEADER}')

    # 2. Limit the loop to avoid "repetition"
    c = c.replace('foreach ($freeLeads as $index => $co)', 'foreach (array_slice($freeLeads, 0, 6) as $index => $co)')
    
    # 3. Ensure the shell is closed before </section>
    # Find the end of this specific section
    end_idx = c.find('</section>', start_idx)
    if end_idx != -1:
        # Check if </div> is already before </section>
        if '</div>\n        </section>' not in c[end_idx-20:end_idx+20]:
             c = c[:end_idx] + '            </div>\n        ' + c[end_idx:]

    # 4. Clean up any lingering duplicated headers or bad styles
    c = c.replace('<div style="padding-bottom: 1.75rem; position: relative;">', '')
    # Actually, let's just make sure we don't have duplicated H2s with "Últimas 10 empresas"
    c = re.sub(r'<h2.*?Últimas 10 empresas.*?</h2>', '', c, flags=re.DOTALL)
    
    # 5. Fix the paywall overlay background to match the NEW shell background
    c = c.replace('background: linear-gradient(to top, #ffffff 30%', 'background: linear-gradient(to top, #f8fbff 30%')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Polished {f}')

for f in files:
    polish_file(f)
