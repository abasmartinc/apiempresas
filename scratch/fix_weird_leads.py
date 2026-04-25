import re
import os

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

for f in files:
    if not os.path.exists(f):
        continue
        
    with open(f, 'r', encoding='utf-8') as fh:
        c = fh.read()
    
    # 1. Fix white gradients to match tinted background
    # The shell background is very light, so we use a very light blue/white
    c = c.replace('background: linear-gradient(to top, #ffffff 30%, rgba(255,255,255,0.9) 60%, transparent)', 
                  'background: linear-gradient(to top, rgba(245,248,255,1) 30%, rgba(245,248,255,0.9) 60%, transparent)')
    
    c = c.replace('background: linear-gradient(to top, #ffffff 40%, transparent)', 
                  'background: linear-gradient(to top, #f5f8ff 40%, rgba(245,248,255,0) 100%)')

    # 2. Upgrade the first button to match the premium style
    btn_style = 'padding: 1.1rem 3rem; font-size: 1.15rem; font-weight: 800; border-radius: 100px; background: linear-gradient(135deg, #3b82f6, #6366f1); border: none; box-shadow: 0 8px 24px rgba(99,102,241,0.45), 0 2px 8px rgba(59,130,246,0.3); color: white; text-decoration: none; display: inline-block; transition: all 0.25s ease;'
    
    # Target the specific button style in line 385 area
    old_btn_match = 'class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);"'
    c = c.replace(old_btn_match, 'class="ae-radar-page__btn ae-radar-page__btn--primary" style="' + btn_style + '"')

    # 3. Consolidate: if we have premiumLeads, hide the first CTA block to avoid double buttons
    # We wrap the first CTA in an IF check
    first_cta_start = '<div class="ae-radar-page__lead-overlay-cta"'
    if '<?php if (empty($premiumLeads)): ?>' not in c:
        c = c.replace(first_cta_start, '<?php if (empty($premiumLeads)): ?>\n                <div class="ae-radar-page__lead-overlay-cta"', 1)
        # We need to find the corresponding </div>
        # Looking at previous view_file, it's followed by </div> then </div> (closing grid wrap and shell)
        # Actually it's just one </div> for the cta.
        c = c.replace('</p>\n    </div>', '</p>\n    </div>\n                <?php endif; ?>')

    # 4. Fix paywall grid background - it might be white
    c = c.replace('class="ae-radar-page__paywall-grid" aria-hidden="true">', 'class="ae-radar-page__paywall-grid" aria-hidden="true" style="opacity: 0.4;">')

    with open(f, 'w', encoding='utf-8') as fh:
        fh.write(c)
    print(f'Fixed {f}')
