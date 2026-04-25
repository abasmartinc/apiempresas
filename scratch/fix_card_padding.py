import re

files = [
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php",
]

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Add padding-bottom to the lead-grid-wrap so the glow has room
    content = content.replace(
        '.ae-radar-page__lead-grid-wrap { position: relative; }',
        '.ae-radar-page__lead-grid-wrap { position: relative; padding-bottom: 3rem; }'
    )

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
