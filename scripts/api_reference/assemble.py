import json, re
E = json.load(open('endpoints_es.json', encoding='utf-8'))

def strip_first_json_block(desc):
    return re.sub(r"##### Respuesta de éxito\s*\n+```\s*\n\{.*?\n```\s*\n*", "", desc, count=1, flags=re.S)

# --- Correcciones de la extracción ---
b = E['post /companies/batch']
b['request_example'] = b['response_example']
m = re.search(r"```\s*\n(\{.*?\})\s*\n```", b['description'], flags=re.S)
b['response_example'] = json.loads(m.group(1))
b['description'] = strip_first_json_block(b['description']).replace("##### Cuerpo de la Petición (JSON)\n\n", "")

# Webhooks: desde el 13-10-2026 cada ruta tiene su texto y su ejemplo en documentation.php

# --- Plan, grupo y título de cada ruta (comprobado con la tabla de planes de la home) ---
TODOS, PRO, BUS = 'Free, Pro y Business', 'Pro y Business', 'Business'
META = {
 'get /companies':            ('Empresas', 'Consultar una empresa por CIF', TODOS + ' (en Free, dirección enmascarada y sin administradores)', True),
 'get /companies/search':     ('Empresas', 'Buscar empresas por nombre', TODOS, True),
 'post /companies/batch':     ('Empresas', 'Consultar hasta 100 CIF de una vez', PRO, True),
 'get /companies/verify':     ('Empresas', 'Verificación KYB en una llamada', PRO, True),
 'post /companies/reconcile': ('Empresas', 'De nombre a CIF (en lote)', PRO + ' (y Free con saldo)', True),
 'get /companies/borme':      ('Registro y BORME', 'Historial de actos del BORME', PRO, True),
 'get /companies/signals':    ('Registro y BORME', 'Señales societarias recientes', PRO, True),
 'get /companies/network':    ('Registro y BORME', 'Red de vínculos societarios', PRO, True),
 'get /companies/risk-profile': ('Riesgo y contratos', 'Perfil de riesgo y solvencia', BUS, True),
 'get /companies/contracts':  ('Riesgo y contratos', 'Contratos y adjudicaciones públicas', BUS, True),
 'get /companies/score':      ('Prospección', 'Puntuación comercial (0-100)', TODOS + ' (en Free, solo la cifra)', True),
 'get /companies/radar':      ('Prospección', 'Empresas recién constituidas', TODOS + ' (10, 100 o 1.000 resultados por consulta)', True),
 'get /companies/filter':     ('Prospección', 'Segmentos de empresas', BUS + ' (el recuento es gratis en todos los planes)', True),
 'get /companies/insights':   ('Prospección', 'Análisis comercial con IA', BUS + ' (en Pro, vista previa)', True),
 'get /companies/contact-prep': ('Prospección', 'Preparación de contacto con IA', BUS, True),
 'get /companies/match':      ('Prospección', 'Encaje con tu sector (match B2B)', BUS, True),
 'post /watchlist':           ('Vigilancia', 'Añadir empresas a la vigilancia', PRO + ' (100 empresas en Pro, 1.000 en Business)', True),
 'get /watchlist':            ('Vigilancia', 'Listar empresas vigiladas', PRO, True),
 'delete /watchlist/{cif}':   ('Vigilancia', 'Dejar de vigilar una empresa', PRO, True),
 'get /watchlist/events':     ('Vigilancia', 'Cambios de las empresas vigiladas', PRO, True),
 'get /webhooks':             ('Webhooks', 'Listar tus webhooks', BUS, False),
 'post /webhooks':            ('Webhooks', 'Crear un webhook', BUS, False),
 'post /webhooks/{id}/test':  ('Webhooks', 'Enviar un evento de prueba', BUS, False),
 'get /webhooks/{id}/deliveries': ('Webhooks', 'Historial de entregas', BUS, False),
 'delete /webhooks/{id}':     ('Webhooks', 'Borrar un webhook', BUS, False),
 'get /usage':                ('Cuenta', 'Consumo de tu API Key', TODOS, False),
 'get /catalog':              ('Cuenta', 'Catálogo de valores de los filtros', TODOS, True),
}
assert set(META) == set(E), set(META) ^ set(E)

paths = {}
for key, (tag, summary, plan, sandbox) in META.items():
    method, path = key.split(' ', 1)
    e = E[key]
    extra = '' if sandbox else '\n\n> No está en el Sandbox: esta ruta solo funciona en producción.'
    body = re.sub(r'\[([^\]]+)\]\(#[^)]+\)', r'\1', e['description'])
    # El ejemplo va en el panel de la derecha: fuera los títulos que lo anunciaban
    body = re.sub(r'^#{3,6} Respuesta( de éxito)?[^\n]*\n+(?=#|\Z|-|\*|\||\\)', '', body, flags=re.M)
    desc = f"**Plan:** {plan}{extra}\n\n{body}".strip()
    paths.setdefault(path, {})[method] = {
        'tag': tag, 'summary': summary, 'description': desc, 'sandbox': sandbox,
        'request_example': e['request_example'], 'response_example': e['response_example'],
    }

INTRO = open('intro_es.md', encoding='utf-8').read()
overlay = {
 'info': {'title': 'API Empresas', 'version': 'v1', 'description': INTRO},
 'servers': [
   {'url': 'https://apiempresas.es/api/sandbox/v1', 'description': 'Sandbox: no gasta consultas (CIF de prueba A15075062 y B00000000)'},
   {'url': 'https://apiempresas.es/api/v1', 'description': 'Producción: cada respuesta 200 gasta consultas'},
 ],
 'production_server': {'url': 'https://apiempresas.es/api/v1', 'description': 'Producción'},
 'tags': [
   {'name': 'Empresas', 'description': 'Datos de una empresa por CIF o por nombre, en lote, verificación KYB y de nombre a CIF.'},
   {'name': 'Registro y BORME', 'description': 'Actos publicados en el BORME, señales recientes y vínculos societarios.'},
   {'name': 'Riesgo y contratos', 'description': 'Perfil de riesgo calculado a partir del BORME y contratos públicos adjudicados.'},
   {'name': 'Prospección', 'description': 'Puntuación comercial, empresas nuevas, segmentos y herramientas de venta con IA.'},
   {'name': 'Vigilancia', 'description': 'Sigue los cambios de tus empresas sin gastar consultas.'},
   {'name': 'Webhooks', 'description': 'Recibe en tu servidor los cambios de las empresas vigiladas.'},
   {'name': 'Cuenta', 'description': 'Consumo de tu API Key y valores válidos de los filtros.'},
 ],
 'x-tagGroups': [
   {'name': 'Datos de empresas', 'tags': ['Empresas', 'Registro y BORME', 'Riesgo y contratos']},
   {'name': 'Ventas', 'tags': ['Prospección']},
   {'name': 'Avisos', 'tags': ['Vigilancia', 'Webhooks']},
   {'name': 'Cuenta', 'tags': ['Cuenta']},
 ],
 'paths': paths,
}
json.dump(overlay, open('openapi_overlay_es.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('ok', len(paths), 'paths', sum(len(v) for v in paths.values()), 'ops')
