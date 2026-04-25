import re

PREMIUM_HEADER_PERIOD = '''<div style="background: linear-gradient(135deg, rgba(59,130,246,0.04) 0%, rgba(16,185,129,0.04) 100%); border: 1px solid rgba(59,130,246,0.1); border-top: 3px solid transparent; border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; position: relative; overflow: hidden; background-clip: padding-box; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(59,130,246,0.06);">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(to right, #3b82f6, #10b981); border-radius: 1rem 1rem 0 0;"></div>
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
                '''

files_config = [
    {
        "path": r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
        "header": PREMIUM_HEADER_PERIOD,
        "dynamic": None,
    },
    {
        "path": r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
        "header": PREMIUM_HEADER_PERIOD.replace(
            "Leads B2B <span",
            "Leads B2B en <span"
        ).replace(
            "Recientes</span>",
            '<?= esc(ucfirst(mb_strtolower($province))) ?></span>'
        ).replace(
            "Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.",
            "Empresas recién constituidas en <?= esc($province) ?> detectadas en BORME y listas para prospección comercial. Ideal para proveedores locales y nacionales B2B."
        ),
        "dynamic": None,
    },
    {
        "path": r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php",
        "header": PREMIUM_HEADER_PERIOD.replace(
            "Leads B2B <span",
            "Empresas activas en <span"
        ).replace(
            "Recientes</span>",
            '<?= esc($sectorLabel) ?></span>'
        ).replace(
            "Muestra comercial en tiempo real",
            "Muestra comercial en tiempo real"
        ).replace(
            "Actualizado hoy",
            "Actualizado BORME"
        ).replace(
            "Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.",
            "Empresas recién constituidas en el sector de <?= esc(mb_strtolower($sectorLabel)) ?> detectadas en BORME y listas para prospección comercial B2B."
        ),
        "dynamic": None,
    },
]

# Pattern to find the "wrong" header we put (the div with margin-bottom: 1.5rem or inline style)
pattern = re.compile(
    r'<div style="(?:margin-bottom: 1\.5rem|display:flex;flex-direction:column).*?</div>\s*(?=<\?php endif)',
    re.DOTALL
)

for cfg in files_config:
    filepath = cfg["path"]
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    replaced, n = pattern.subn(cfg["header"], content, count=1)
    if n:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(replaced)
        print(f"Fixed {filepath}")
    else:
        print(f"Pattern NOT found in {filepath}")

# National hub - different pattern
national_file = r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php"
with open(national_file, 'r', encoding='utf-8') as f:
    content = f.read()

national_pattern = re.compile(
    r'<div style="margin-bottom: 1\.5rem;">.*?</div>\s*(?=\n\s*<(?:style|<!--|div))',
    re.DOTALL
)

national_header = '''<div style="background: linear-gradient(135deg, rgba(59,130,246,0.04) 0%, rgba(16,185,129,0.04) 100%); border: 1px solid rgba(59,130,246,0.1); border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(59,130,246,0.06);">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(to right, #3b82f6, #10b981); border-radius: 1rem 1rem 0 0;"></div>
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
                <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 0.3rem 0.8rem; border-radius: 999px;">
                    <span style="width: 6px; height: 6px; background: #3b82f6; border-radius: 50%; animation: pulse 2s infinite; display: inline-block;"></span>
                    Muestra comercial en tiempo real
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #10b981; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); padding: 0.3rem 0.8rem; border-radius: 999px;">
                    <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                    En tiempo real
                </span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin: 0 0 0.6rem; letter-spacing: -0.025em; line-height: 1.15;">
                Últimas 10 empresas <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">detectadas</span>
            </h2>
        </div>
        '''

replaced, n = national_pattern.subn(national_header, content, count=1)
if n:
    with open(national_file, 'w', encoding='utf-8') as f:
        f.write(replaced)
    print("Fixed national")
else:
    print("Pattern NOT found in national")
