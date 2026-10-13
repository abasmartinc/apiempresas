"""Overlay en inglés de la referencia (/api/reference), a partir de documentation_en_standalone.php.

Uso:
  python build_overlay.py ../../app/Views/documentation_en_standalone.php endpoints_en.json https://spaincompanyapi.com
  python assemble_en.py
"""
import json, re

E = json.load(open('endpoints_en.json', encoding='utf-8'))

def strip_first_json_block(desc):
    return re.sub(r"##### Success response\s*\n+```\s*\n\{.*?\n```\s*\n*", "", desc, count=1, flags=re.S)

# --- Correcciones de la extracción (igual que en español) ---
b = E['post /companies/batch']
b['request_example'] = b['response_example']
m = re.search(r"```\s*\n(\{.*?\})\s*\n```", b['description'], flags=re.S)
b['response_example'] = json.loads(m.group(1))
b['description'] = strip_first_json_block(b['description']).replace("##### Request Body (JSON)\n\n", "")

ALL, PRO, BUS = 'Free, Pro and Business', 'Pro and Business', 'Business'
META = {
 'get /companies':            ('Companies', 'Look up a company by CIF', ALL + ' (on Free, address masked and no directors)', True),
 'get /companies/search':     ('Companies', 'Search companies by name', ALL, True),
 'post /companies/batch':     ('Companies', 'Look up to 100 CIFs at once', PRO, True),
 'get /companies/verify':     ('Companies', 'KYB verification in one call', PRO, True),
 'post /companies/reconcile': ('Companies', 'Name to CIF (bulk)', PRO + ' (and Free with credits)', True),
 'get /companies/borme':      ('Registry and BORME', 'BORME filing history', PRO, True),
 'get /companies/signals':    ('Registry and BORME', 'Recent corporate signals', PRO, True),
 'get /companies/network':    ('Registry and BORME', 'Corporate network', PRO, True),
 'get /companies/risk-profile': ('Risk and contracts', 'Risk and solvency profile', BUS, True),
 'get /companies/contracts':  ('Risk and contracts', 'Public contracts and awards', BUS, True),
 'get /companies/score':      ('Prospecting', 'Commercial score (0-100)', ALL + ' (on Free, the number only)', True),
 'get /companies/radar':      ('Prospecting', 'Newly incorporated companies', ALL + ' (10, 100 or 1,000 results per request)', True),
 'get /companies/filter':     ('Prospecting', 'Company segments', BUS + ' (the count is free on every plan)', True),
 'get /companies/insights':   ('Prospecting', 'AI commercial analysis', BUS + ' (preview on Pro)', True),
 'get /companies/contact-prep': ('Prospecting', 'AI contact preparation', BUS, True),
 'get /companies/match':      ('Prospecting', 'Fit with your sector (B2B match)', BUS, True),
 'post /watchlist':           ('Monitoring', 'Add companies to monitoring', PRO + ' (100 companies on Pro, 1,000 on Business)', True),
 'get /watchlist':            ('Monitoring', 'List monitored companies', PRO, True),
 'delete /watchlist/{cif}':   ('Monitoring', 'Stop monitoring a company', PRO, True),
 'get /watchlist/events':     ('Monitoring', 'Changes in monitored companies', PRO, True),
 'get /webhooks':             ('Webhooks', 'List your webhooks', BUS, False),
 'post /webhooks':            ('Webhooks', 'Create a webhook', BUS, False),
 'post /webhooks/{id}/test':  ('Webhooks', 'Send a test event', BUS, False),
 'get /webhooks/{id}/deliveries': ('Webhooks', 'Delivery history', BUS, False),
 'delete /webhooks/{id}':     ('Webhooks', 'Delete a webhook', BUS, False),
 'get /usage':                ('Account', 'Your API key usage', ALL, False),
 'get /catalog':              ('Account', 'Valid filter values', ALL, True),
}
assert set(META) == set(E), set(META) ^ set(E)

paths = {}
for key, (tag, summary, plan, sandbox) in META.items():
    method, path = key.split(' ', 1)
    e = E[key]
    extra = '' if sandbox else '\n\n> Not available in the Sandbox: this endpoint only works in production.'
    body = re.sub(r'\[([^\]]+)\]\(#[^)]+\)', r'\1', e['description'])
    body = re.sub(r'^#{3,6} Success response[^\n]*\n+(?=#|\Z|-|\*|\||\\)', '', body, flags=re.M)
    desc = f"**Plan:** {plan}{extra}\n\n{body}".strip()
    paths.setdefault(path, {})[method] = {
        'tag': tag, 'summary': summary, 'description': desc, 'sandbox': sandbox,
        'request_example': e['request_example'], 'response_example': e['response_example'],
    }

INTRO = open('intro_en.md', encoding='utf-8').read()
overlay = {
 'lang': 'en',
 'info': {'title': 'Spain Company API', 'version': 'v1', 'description': INTRO},
 'servers': [
   {'url': 'https://spaincompanyapi.com/api/sandbox/v1', 'description': 'Sandbox: does not use requests (test CIFs A15075062 and B00000000)'},
   {'url': 'https://spaincompanyapi.com/api/v1', 'description': 'Production: every 200 response uses requests'},
 ],
 'production_server': {'url': 'https://spaincompanyapi.com/api/v1', 'description': 'Production'},
 'tags': [
   {'name': 'Companies', 'description': 'Company data by CIF or by name, in bulk, KYB verification and name to CIF.'},
   {'name': 'Registry and BORME', 'description': 'Filings published in the BORME, recent signals and corporate links.'},
   {'name': 'Risk and contracts', 'description': 'Risk profile calculated from the BORME and awarded public contracts.'},
   {'name': 'Prospecting', 'description': 'Commercial score, new companies, segments and AI sales tools.'},
   {'name': 'Monitoring', 'description': 'Follow changes in your companies without using requests.'},
   {'name': 'Webhooks', 'description': 'Receive changes in monitored companies on your server.'},
   {'name': 'Account', 'description': 'Your API key usage and valid filter values.'},
 ],
 'x-tagGroups': [
   {'name': 'Company data', 'tags': ['Companies', 'Registry and BORME', 'Risk and contracts']},
   {'name': 'Sales', 'tags': ['Prospecting']},
   {'name': 'Alerts', 'tags': ['Monitoring', 'Webhooks']},
   {'name': 'Account', 'tags': ['Account']},
 ],
 'paths': paths,
}
json.dump(overlay, open('openapi_overlay_en.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('ok', len(paths), 'paths', sum(len(v) for v in paths.values()), 'ops')
