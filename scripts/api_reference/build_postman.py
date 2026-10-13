"""Actualiza las colecciones de Postman (13-10-2026).

- {{BASE_URL}} en vez de la URL fija: se cambia a Sandbox en un sitio.
- {{WEBHOOK_ID}} en vez de 123.
- Descripción en todas las peticiones, con el coste en consultas y en créditos del bono.
- Ejemplo de respuesta guardado (el de la referencia: app/Docs/openapi_overlay_es.json).
"""
import copy, json, sys
from urllib.parse import urlsplit

SRC, OVERLAY, OUT = sys.argv[1], sys.argv[2], sys.argv[3]
FECHA = '13-10-2026'
PROD = 'https://apiempresas.es/api/v1'

ov = json.load(open(OVERLAY, encoding='utf-8'))['paths']

COSTE = {
    '/companies': 'Coste: 1 consulta (1 crédito con bono).',
    '/companies/search': 'Coste: 1 consulta (1 crédito con bono).',
    '/companies/verify': 'Coste: 2 consultas (2 créditos con bono).',
    '/companies/reconcile': 'Coste: 1 consulta por cada match; ambiguous y no_match no se cobran.',
    '/companies/batch': 'Coste: 1 consulta por empresa encontrada.',
    '/companies/filter': None,  # depende de count_only
    '/usage': 'No gasta consultas. No está en el Sandbox.',
    '/watchlist': 'No gasta consultas.',
    '/watchlist/events': 'No gasta consultas.',
    '/webhooks': 'No gasta consultas. No está en el Sandbox.',
}
TRES = 'Coste: 1 consulta del plan (3 créditos con bono).'

DESC = {
    ('GET', '/companies/score'): 'Puntuación comercial (0-100) con prioridad (muy_alta…muy_baja), motivos y última señal del BORME. En Free solo la cifra, sin gastar consultas (máximo 25 vistas previas al día).',
    ('GET', '/companies/signals'): 'Último acto del BORME que cuenta para la puntuación (como mucho un elemento). probability es la prioridad comercial, no la fiabilidad del evento. 404 si la empresa no existe.',
    ('GET', '/companies/borme'): 'Historial de actos publicados en el BORME. Pro, Business y Free con bono.',
    ('GET', '/companies/network'): 'Red de empresas vinculadas a través de sus administradores. Pro, Business y Free con bono.',
    ('GET', '/companies/risk-profile'): 'Perfil de riesgo y solvencia: nivel, puntuación, dimensiones y alertas. Solo Business. 404 RISK_PROFILE_NOT_AVAILABLE si no está calculado.',
    ('GET', '/companies/contracts'): 'Contratos públicos adjudicados a la empresa (Plataforma de Contratación del Sector Público). Solo Business.',
    ('GET', '/companies/insights'): 'Análisis IA del perfil comercial. En Business, completo; en Pro, vista previa sin coste (profile y conversion_probability).',
    ('GET', '/companies/contact-prep'): 'Enfoque comercial, mensaje sugerido y objeción probable, generados por IA. Solo Business.',
    ('GET', '/webhooks'): 'Tus webhooks, con su estado (is_active, failure_count, last_status_code) y su secret.',
    ('DELETE', '/webhooks/{id}'): 'Borra el webhook. 404 WEBHOOK_NOT_FOUND si no existe.',
    ('POST', '/webhooks/{id}/test'): 'Envía un test.ping a la URL del webhook y devuelve el código HTTP que respondió tu servidor. Si no responde 2xx: 502 WEBHOOK_DELIVERY_FAILED.',
    ('DELETE', '/watchlist/{cif}'): '404 NOT_WATCHING si no estaba en tu vigilancia. No gasta consultas.',
}


def ruta_y_plantilla(raw):
    """('/companies/search', '/companies/search') o ('/webhooks/123/test', '/webhooks/{id}/test')."""
    p = urlsplit(raw).path
    for pre in ('/api/v1', '/api/sandbox/v1'):
        if p.startswith(pre):
            p = p[len(pre):]
    partes = p.strip('/').split('/')
    if partes[0] == 'webhooks' and len(partes) >= 2:
        partes[1] = '{id}'
    if partes[0] == 'watchlist' and len(partes) == 2 and partes[1] != 'events':
        partes[1] = '{cif}'
    return '/' + '/'.join(partes)


def coste(method, tpl, query):
    if tpl == '/companies/filter':
        return 'Gratis (count_only=true).' if any(q['key'] == 'count_only' for q in query) else 'Coste: 5 consultas por fila devuelta (5 créditos con bono).'
    if tpl.startswith('/watchlist'):
        return COSTE['/watchlist']
    if tpl.startswith('/webhooks'):
        return COSTE['/webhooks']
    return COSTE.get(tpl, TRES)


def nuevo_url(raw):
    u = urlsplit(raw)
    sandbox = '/api/sandbox/v1' in u.path
    if sandbox:
        return raw, None
    path = u.path[len('/api/v1'):]
    path = path.replace('/webhooks/123', '/webhooks/{{WEBHOOK_ID}}')
    q = []
    if u.query:
        for par in u.query.split('&'):
            k, _, v = par.partition('=')
            q.append({'key': k, 'value': v})
    raw2 = '{{BASE_URL}}' + path + (('?' + u.query) if u.query else '')
    url = {'raw': raw2, 'host': ['{{BASE_URL}}'], 'path': [s for s in path.strip('/').split('/')]}
    if q:
        url['query'] = q
    return raw2, url


def tratar(it):
    r = it['request']
    raw = r['url']['raw'] if isinstance(r['url'], dict) else r['url']
    # Colección ya convertida: se vuelve a la URL de producción para tratarla igual
    if raw.startswith('{{BASE_URL}}'):
        raw = PROD + raw[len('{{BASE_URL}}'):].replace('{{WEBHOOK_ID}}', '123')
    method = r['method'].upper()
    tpl = ruta_y_plantilla(raw)
    query = r['url'].get('query', []) if isinstance(r['url'], dict) else []

    raw2, url = nuevo_url(raw)
    if url is not None:
        r['url'] = url

    desc = (r.get('description') or '').strip() or DESC.get((method, tpl), '')
    c = coste(method, tpl, query)
    # Vistas previas: no gastan consultas (tope de 25 al día)
    if it.get('name') in ('Scoring Comercial (básico)', 'Insights IA (vista previa)'):
        c = 'No gasta consultas (máximo 25 vistas previas al día), pero hace falta que te quede alguna.'
    if c and c.split(':')[0] not in desc and 'No gasta' not in desc and 'Gratis' not in desc and 'cobra' not in desc:
        desc = (desc + ' ' + c).strip()
    if tpl == '/companies/search' and 'multiple' not in raw:
        desc = desc  # ya explica multiple=true
    r['description'] = desc

    # Ejemplo de respuesta de la referencia
    ex = (ov.get(tpl, {}).get(method.lower()) or {}).get('response_example')
    if ex is not None:
        codigo = 201 if (method == 'POST' and tpl == '/webhooks') else 200
        req = copy.deepcopy(r)
        req.pop('description', None)
        it['response'] = [{
            'name': 'Ejemplo ' + str(codigo),
            'originalRequest': req,
            'status': 'Created' if codigo == 201 else 'OK',
            'code': codigo,
            '_postman_previewlanguage': 'json',
            'header': [{'key': 'Content-Type', 'value': 'application/json'}],
            'body': json.dumps(ex, ensure_ascii=False, indent=2),
        }]
    return it


def walk(items):
    for it in items:
        if 'item' in it:
            walk(it['item'])
        else:
            tratar(it)


d = json.load(open(SRC, encoding='utf-8'))
walk(d['item'])
d['variable'] = [
    {'key': 'API_KEY', 'value': next((v.get('value', '') for v in d.get('variable', []) if v['key'] == 'API_KEY'), ''), 'type': 'string'},
    {'key': 'BASE_URL', 'value': PROD, 'type': 'string', 'description': 'Producción. Para probar sin gastar consultas: https://apiempresas.es/api/sandbox/v1 (solo CIF A15075062 y B00000000).'},
    {'key': 'WEBHOOK_ID', 'value': '123', 'type': 'string', 'description': 'id de uno de tus webhooks (lo devuelve Crear Webhook).'},
]
desc = d['info'].get('description', '')
d['info']['description'] = (
    'Colección de la API de APIEmpresas.es organizada por planes (Free, Pro y Business). '
    'Pon tu clave en la variable API_KEY. BASE_URL apunta a producción; cámbiala a https://apiempresas.es/api/sandbox/v1 '
    'para probar sin gastar consultas. Cada petición trae un ejemplo de respuesta. '
    'Referencia completa: https://apiempresas.es/api/referencia. Actualizada el ' + FECHA + '.'
)
json.dump(d, open(OUT, 'w', encoding='utf-8'), ensure_ascii=False, indent=2)
n = sum(1 for g in d['item'] for it in g['item'])
con = sum(1 for g in d['item'] for it in g['item'] if it.get('response'))
print(OUT, n, 'peticiones,', con, 'con ejemplo')
