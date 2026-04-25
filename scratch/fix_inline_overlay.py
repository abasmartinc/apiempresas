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

    # Find the broken inline overlay we inserted in update_copy_v4.py
    broken_overlay_pattern = re.compile(r'<div class="ae-radar-page__lead-overlay-cta" style="position: absolute; bottom: 0; left: 0; right: 0; height: 100%; background: linear-gradient\(to top, #ffffff 60%, rgba\(255,255,255,0\.7\) 80%, transparent\); display: flex; align-items: flex-end; justify-content: center; padding-bottom: 2rem; pointer-events: none; z-index: 10;">.*?</div>', re.DOTALL)

    fixed_overlay = """<div class="ae-radar-page__lead-overlay-cta" style="position: absolute; bottom: 0; left: 0; right: 0; height: 350px; background: linear-gradient(to top, #ffffff 30%, rgba(255,255,255,0.9) 60%, transparent); display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 2rem; z-index: 10;">
        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
            Conseguir estos clientes ahora
        </a>
        <p style="font-weight: 700; color: #1e293b; margin-top: 1rem; margin-bottom: 0; font-size: 1rem; text-align: center;">Estas empresas están activas ahora mismo — accede antes que otros proveedores</p>
    </div>"""

    if broken_overlay_pattern.search(content):
        content = broken_overlay_pattern.sub(fixed_overlay, content, count=1)
    else:
        print(f"Could not find broken overlay in {filepath}")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
