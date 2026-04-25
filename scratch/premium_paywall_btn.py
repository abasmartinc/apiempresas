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

    # 1. PAYWALL BUTTON — make it premium, centered, full glow
    # Find the paywall actions div and upgrade the button inside
    content = content.replace(
        '<div class="ae-radar-page__paywall-actions">',
        '<div class="ae-radar-page__paywall-actions" style="display: flex; justify-content: center; margin-top: 0.5rem;">'
    )
    content = content.replace(
        '<a href="<?= site_url(\'radar/preview\') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">',
        '<a href="<?= site_url(\'radar/preview\') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary" style="background: linear-gradient(135deg, #3b82f6, #6366f1); border: none; padding: 1.1rem 3rem; font-size: 1.15rem; font-weight: 800; border-radius: 100px; box-shadow: 0 8px 24px rgba(99,102,241,0.45), 0 2px 8px rgba(59,130,246,0.3); letter-spacing: -0.01em; transition: all 0.25s ease; display: inline-block; min-width: 280px; text-align: center;">'
    )

    # 2. BLURRED CARD GLOW — add a glow aura below blurred cards
    # The CSS for is-blurred is injected inline in a <style> block
    content = content.replace(
        """.ae-radar-page__lead-card.is-blurred {
                    filter: blur(5px);
                    opacity: 0.5;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                }""",
        """.ae-radar-page__lead-card.is-blurred {
                    filter: blur(5px);
                    opacity: 0.55;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.35), 0 8px 16px -4px rgba(99,102,241,0.25);
                    border-color: rgba(59,130,246,0.3) !important;
                }"""
    )

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
