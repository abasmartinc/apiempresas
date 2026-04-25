import re

files = [
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php',
    r'd:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php',
]

# Remove orphaned <p> + </div> that appears right before <?php endif; ?>
orphan_pattern = re.compile(
    r'(</div>\n)'                                   # closes our new header div
    r'(\s*<p class="ae-radar-page__section-subtitle[^"]*"[^>]*>.*?</p>\s*\n)'  # orphan <p>
    r'(\s*</div>\s*\n)'                             # orphan </div>
    r'(\s*\?php endif)',                             # next line: endif
    re.DOTALL
)

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as fh:
        content = fh.read()

    fixed, n = orphan_pattern.subn(r'\1\4', content)
    if n:
        with open(filepath, 'w', encoding='utf-8') as fh:
            fh.write(fixed)
        print(f'Fixed {n} instance(s) in {filepath}')
    else:
        print(f'No orphan found in {filepath}')
