import re

files = [
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php",
]

CARD_OPEN = 'style="background: linear-gradient(135deg, rgba(59,130,246,0.04) 0%, rgba(16,185,129,0.04) 100%); border: 1px solid rgba(59,130,246,0.1); border-top: 3px solid transparent; border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; position: relative; overflow: hidden; background-clip: padding-box; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(59,130,246,0.06);"'

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Step 1: Replace the outer leads-shell div opening to have the premium card styles
    # and remove padding from the inner card wrapper (the small card that only wraps the header)
    content = content.replace(
        '<div class="ae-radar-page__leads-shell">',
        '<div class="ae-radar-page__leads-shell" style="background: linear-gradient(135deg, rgba(59,130,246,0.04) 0%, rgba(16,185,129,0.04) 100%); border: 1px solid rgba(59,130,246,0.1); border-radius: 1.25rem; padding: 2rem 2rem 0; position: relative; overflow: hidden; box-shadow: 0 2px 8px rgba(59,130,246,0.08), 0 20px 40px rgba(0,0,0,0.04);">\n            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(to right, #3b82f6, #10b981);"></div>'
    )

    # Step 2: Strip the inner premium card div (which now just wraps the header text) 
    # Replace it with a plain div that only keeps the padding for the header content
    inner_card_pattern = re.compile(
        r'<div style="background: linear-gradient\(135deg, rgba\(59,130,246,0\.04\)[^"]+box-shadow: 0 1px 3px rgba\(0,0,0,0\.04\), 0 8px 24px rgba\(59,130,246,0\.06\);"\s*>',
        re.DOTALL
    )
    content = inner_card_pattern.sub('<div style="padding-bottom: 1.75rem; position: relative;">', content)

    # Step 3: Remove the inner gradient line div (we now have it on the shell)
    content = content.replace(
        '<div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(to right, #3b82f6, #10b981); border-radius: 1rem 1rem 0 0;"></div>',
        ''
    )

    # Step 4: Fix the overlay gradient - it fades to white but the card background is tinted.
    # Change the overlay gradient to match the card's tinted background.
    content = content.replace(
        'background: linear-gradient(to top, #ffffff 40%, transparent)',
        'background: linear-gradient(to top, #f5f8ff 40%, rgba(245,248,255,0) 100%)'
    )

    # Step 5: Adjust the urgency block margin (was 3rem top, reduce to fit inside card)
    content = content.replace(
        'margin: 1.5rem 0 2.5rem 0; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05)',
        'margin: 0 0 2rem 0; display: flex; align-items: center; gap: 1rem; box-shadow: none'
    )

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
