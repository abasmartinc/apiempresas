import re

files = {
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php": {
        "title": "Leads B2B Recientes",
        "subtitle": "Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.",
        "badge_text": "Actualizado hoy",
    },
}

# For the period file - restore the correct original structure with just the badge moving up
period_file = r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php"
with open(period_file, 'r', encoding='utf-8') as f:
    content = f.read()

# Find and replace our custom header with the original clean one (badge inline with title)
bad_header = re.compile(
    r'<div class="ae-radar-page__leads-header".*?</div>\s*(?=<\?php endif)',
    re.DOTALL
)

good_header = """<div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot" style="margin: 0;">
                            <span class="ae-radar-page__section-kicker-dot"></span>
                            Muestra comercial en tiempo real
                        </div>
                        <div class="ae-radar-page__live-badge" style="margin: 0;">
                            <span class="ae-radar-page__live-badge-dot"></span>
                            Actualizado hoy
                        </div>
                    </div>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                        Leads B2B Recientes
                    </h2>
                    <p style="color: #64748b; font-size: 1rem; margin-top: 0.5rem; max-width: 720px; line-height: 1.6;">
                        Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                    </p>
                </div>
                """

content = bad_header.sub(good_header, content, count=1)

with open(period_file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed period")

# Province file
province_file = r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php"
with open(province_file, 'r', encoding='utf-8') as f:
    content = f.read()

bad_header_province = re.compile(
    r'<div style="display:flex;flex-direction:column;align-items:flex-start.*?</div>\s*(?=<\?php endif)',
    re.DOTALL
)

good_header_province = """<div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot" style="margin: 0;">
                            <span class="ae-radar-page__section-kicker-dot"></span>
                            Muestra comercial en tiempo real
                        </div>
                        <div class="ae-radar-page__live-badge" style="margin: 0;">
                            <span class="ae-radar-page__live-badge-dot"></span>
                            Actualizado hoy
                        </div>
                    </div>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                        Leads B2B en <?= esc(ucfirst(mb_strtolower($province))) ?>
                    </h2>
                    <p style="color: #64748b; font-size: 1rem; margin-top: 0.5rem; max-width: 720px; line-height: 1.6;">
                        Empresas recién constituidas en <?= esc($province) ?> detectadas en BORME y listas para prospección comercial. Ideal para proveedores locales y nacionales B2B.
                    </p>
                </div>
                """

content = bad_header_province.sub(good_header_province, content, count=1)

with open(province_file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed province")

# Sector file
sector_file = r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php"
with open(sector_file, 'r', encoding='utf-8') as f:
    content = f.read()

bad_header_sector = re.compile(
    r'<div style="display:flex;flex-direction:column;align-items:flex-start.*?</div>\s*(?=<\?php endif)',
    re.DOTALL
)

good_header_sector = """<div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot" style="margin: 0;">
                            <span class="ae-radar-page__section-kicker-dot"></span>
                            Muestra comercial en tiempo real
                        </div>
                        <div class="ae-radar-page__live-badge" style="margin: 0;">
                            <span class="ae-radar-page__live-badge-dot"></span>
                            Actualizado BORME
                        </div>
                    </div>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                        Empresas activas en <?= esc($sectorLabel) ?>
                    </h2>
                    <p style="color: #64748b; font-size: 1rem; margin-top: 0.5rem; max-width: 720px; line-height: 1.6;">
                        Empresas recién constituidas en el sector de <?= esc(mb_strtolower($sectorLabel)) ?> detectadas en BORME y listas para prospección comercial B2B.
                    </p>
                </div>
                """

content = bad_header_sector.sub(good_header_sector, content, count=1)

with open(sector_file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed sector")

# National hub - radar_new_companies.php - also fix
national_file = r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php"
with open(national_file, 'r', encoding='utf-8') as f:
    content = f.read()

bad_header_national = re.compile(
    r'<div class="ae-radar-page__leads-header".*?</div>\s*(?=\n\s*<(?:style|<!--|div))',
    re.DOTALL
)

good_header_national = """<div style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                    <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot" style="margin: 0;">
                        <span class="ae-radar-page__section-kicker-dot"></span>
                        Muestra comercial en tiempo real
                    </div>
                    <div class="ae-radar-page__live-badge" style="margin: 0;">
                        <span class="ae-radar-page__live-badge-dot"></span>
                        En tiempo real
                    </div>
                </div>
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                    Últimas 10 empresas detectadas
                </h2>
            </div>
            """

content = bad_header_national.sub(good_header_national, content, count=1)

with open(national_file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed national")
