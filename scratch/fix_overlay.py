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

    overlay_pattern = re.compile(r'<div class="ae-radar-page__lead-overlay-cta">.*?</div>', re.DOTALL)
    
    new_overlay = """<div class="ae-radar-page__lead-overlay-cta" style="height: 100%; min-height: 400px; background: linear-gradient(to top, #ffffff 50%, rgba(255,255,255,0.9) 70%, transparent); justify-content: flex-end; padding-bottom: 3rem;">
                    <div style="background: white; border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,0.05); max-width: 650px; width: 90%; margin: 0 auto; position: relative; z-index: 30; text-align: center;">
                        <div style="width: 48px; height: 48px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V8a5 5 0 0 1 10 0v3"></path></svg>
                        </div>
                        <p style="font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; font-size: 1.4rem; letter-spacing: -0.02em;">Estas empresas están activas ahora mismo</p>
                        <p style="color: #475569; margin-bottom: 0.5rem; font-size: 1.05rem; font-weight: 500;">Otros proveedores ya están intentando cerrar estas oportunidades.</p>
                        <p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.05rem; font-weight: 700;">Si no actúas ahora, perderás estas oportunidades frente a tu competencia.</p>
                        
                        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="width: 100%; justify-content: center; padding: 1.1rem; font-size: 1.15rem; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); border-radius: 0.75rem;">
                            Acceder ahora antes que otros proveedores
                        </a>
                        
                        <p style="font-size: 0.9rem; color: #64748b; margin-top: 1.25rem; font-weight: 500;">La mayoría de usuarios consigue su primer cliente en días</p>
                    </div>
                </div>"""
    
    content = overlay_pattern.sub(new_overlay, content, count=1)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
