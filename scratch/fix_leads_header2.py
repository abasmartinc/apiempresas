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

    # ----------------------------------------------------------------
    # 1. Fix complex header (period / province / sector)
    # ----------------------------------------------------------------
    complex_header_pattern = re.compile(
        r'<div class="ae-radar-page__leads-header"[^>]*>.*?</div>\s*</div>',
        re.DOTALL
    )

    def is_complex(m):
        return 'Leads B2B Recientes' in m.group(0)

    new_complex_header = '''<div class="ae-radar-page__leads-header" style="display:flex;flex-direction:column;align-items:flex-start;gap:0.5rem;margin-bottom:0;">
                    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.78rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;background:#ecfdf5;border:1px solid #a7f3d0;padding:0.25rem 0.75rem;border-radius:999px;">
                            <span style="width:7px;height:7px;background:#10b981;border-radius:50%;display:inline-block;animation:pulse 2s infinite;"></span>
                            Muestra comercial en tiempo real
                        </span>
                    </div>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left" style="margin:0;font-size:2rem;">
                        Leads B2B Recientes
                    </h2>
                    <p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left" style="margin:0;max-width:720px;font-size:1rem;color:#475569;">
                        Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                    </p>
                </div>'''

    # Replace only the complex header (the one with Leads B2B Recientes)
    def repl_if_complex(m):
        if 'Leads B2B Recientes' in m.group(0):
            return new_complex_header
        return m.group(0)

    content = complex_header_pattern.sub(repl_if_complex, content)

    # ----------------------------------------------------------------
    # 2. Fix simple header (national hub - radar_new_companies.php)
    # ----------------------------------------------------------------
    simple_header_pattern = re.compile(
        r'<div class="ae-radar-page__leads-header"[^>]*>.*?Últimas 10 empresas detectadas.*?</div>\s*</div>',
        re.DOTALL
    )

    new_simple_header = '''<div class="ae-radar-page__leads-header" style="display:flex;flex-direction:column;align-items:flex-start;gap:0.5rem;margin-bottom:0;">
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <span style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.78rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;background:#ecfdf5;border:1px solid #a7f3d0;padding:0.25rem 0.75rem;border-radius:999px;">
                        <span style="width:7px;height:7px;background:#10b981;border-radius:50%;display:inline-block;"></span>
                        En tiempo real
                    </span>
                </div>
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left" style="margin:0;font-size:2rem;">
                    Últimas 10 empresas detectadas
                </h2>
            </div>'''

    content = simple_header_pattern.sub(new_simple_header, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Done: {filepath}")
