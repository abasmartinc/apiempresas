import re
import os

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

# Even more premium styles
SHELL_STYLE = 'background: #f8fbff !important; border: 1px solid rgba(59,130,246,0.15) !important; border-radius: 2rem !important; padding: 3rem 2.5rem !important; position: relative !important; overflow: hidden !important; box-shadow: 0 10px 50px -12px rgba(59,130,246,0.12), 0 4px 12px rgba(0,0,0,0.02) !important;'
GRADIENT_LINE = '<div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6); background-size: 200% auto; animation: shine 3s linear infinite;"></div>'
SHINE_ANIM = '<style>@keyframes shine { to { background-position: 200% center; } } @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }</style>'

for f in files:
    if not os.path.exists(f):
        continue
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()

    # Apply the shell style with !important to kill cache/overrides
    c = re.sub(r'<div class="ae-radar-page__leads-shell" style="[^"]+">', 
               f'<div class="ae-radar-page__leads-shell" style="{SHELL_STYLE}">\n            {SHINE_ANIM}\n            {GRADIENT_LINE}', c)

    # Improve Paywall Card
    c = c.replace('class="ae-radar-page__paywall-card"', 'class="ae-radar-page__paywall-card" style="border: none !important; box-shadow: 0 40px 100px -20px rgba(15,23,42,0.3), 0 20px 40px -15px rgba(59,130,246,0.2) !important; background: rgba(255,255,255,0.98) !important; backdrop-filter: blur(10px) !important; border-radius: 2rem !important;"')
    
    # Improve Blurred Cards Aura
    c = c.replace('.ae-radar-page__lead-card.is-blurred {', '.ae-radar-page__lead-card.is-blurred {\n                    filter: blur(8px) grayscale(0.2) !important;\n                    transform: scale(0.98) !important;')

    # Fix the Paywall Overlay Background (make it blend)
    c = c.replace('.ae-radar-page__paywall-overlay {', '.ae-radar-page__paywall-overlay {\n        background: linear-gradient(to bottom, rgba(248,251,255,0), rgba(248,251,255,0.95) 40%, #f8fbff 100%) !important;')

    # Ensure no white gradients survive
    c = c.replace('#ffffff 30%', 'rgba(248,251,255,1) 30%')
    c = c.replace('#ffffff 40%', 'rgba(248,251,255,1) 40%')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Polished {f}')
