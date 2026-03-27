import os

files = [
    r"c:\laragon\www\apiempresas\public\css\radar_new_companies_period.css",
    r"c:\laragon\www\apiempresas\public\css\radar_new_companies_province.css",
    r"c:\laragon\www\apiempresas\public\css\radar_new_companies_sector.css",
    r"c:\laragon\www\apiempresas\public\css\radar_companies_province.css"
]

target = """.ae-radar-page__lead-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.ae-radar-page__lead-badge::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #6a84d8;
    box-shadow: none;
}

.ae-radar-page__lead-badge::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.65);
}"""

replacement = """.ae-radar-page__lead-top-row {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.ae-radar-page__lead-badge {
    position: absolute;
    top: -1px;
    right: 24px;
    background: linear-gradient(135deg, var(--ae-primary) 0%, var(--ae-primary-dark) 100%);
    color: #fff;
    padding: 6px 14px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 4px 12px rgba(33, 82, 255, 0.25);
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.ae-radar-page__lead-badge::before {
    display: none;
}"""

for filepath in files:
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if target in content:
            new_content = content.replace(target, replacement)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Replaced in {filepath}")
        else:
            print(f"Target not found in {filepath} (Already replaced or different format)")
    else:
        print(f"File not found: {filepath}")
