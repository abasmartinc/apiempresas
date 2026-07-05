import os
import re

dir_path = 'app/Controllers/Api/V1'

replacements = [
    (r'"1. Plan Free / General"', '"1. Plan Free"'),
    (r'"2. Plan Professional"', '"2. Plan Pro"'),
    (r'"2. Planes Pro / Business"', '"2. Plan Pro"'),
    (r'\["2. Plan Pro", "3. Plan Business"\]', '["2. Plan Pro"]')
]

for root, dirs, files in os.walk(dir_path):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = content
            for old, new in replacements:
                new_content = new_content.replace(old, new)
                
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Updated {file}')
