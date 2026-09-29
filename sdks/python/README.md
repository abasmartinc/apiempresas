# APIEmpresas.es - SDK para Python

SDK oficial para consultar la API de [APIEmpresas.es](https://apiempresas.es) desde Python 3.7 o superior.

## Instalación

```bash
pip install apiempresas
```

## Uso

```python
from apiempresas import ApiEmpresas, ApiError

api = ApiEmpresas('TU_API_KEY')

# Datos de una empresa (devuelve directamente el objeto de la empresa)
empresa = api.companies.get('A15075062')
print(empresa['name'])

# Con administradores y cargos (Pro/Business)
empresa = api.companies.get('A15075062', admin=True)

# Varias empresas por nombre, con paginación (devuelve data y meta)
pagina = api.companies.search_multiple('software', limit=20)
siguiente = pagina['meta'].get('next_cursor')
if siguiente:
    pagina = api.companies.search_multiple('software', cursor=siguiente)

# Consulta múltiple (devuelve data y meta)
lote = api.companies.batch(['A15075062', 'A46103834'])

# Match (Business): el sector del vendedor es obligatorio
match = api.companies.match('A15075062', 'software')

# Radar de empresas nuevas (Business)
nuevas = api.companies.radar(province='Madrid', range='semana')
```

Los métodos devuelven el contenido de `data`, salvo cuando la respuesta trae también `meta` (`batch`, `search_multiple`, `radar`), que devuelven la respuesta completa.

### Verificación KYB (Pro) — 2 consultas

```python
v = api.companies.verify('A46103834', name='Mercadona SA', person='Juan Roig Alfonso', vat=True)
print(v['decision_hint'])   # pass | review | fail
```

Cada empresa trae además `status_code` (ACTIVE, INSOLVENCY, EXTINCT...), `status_source`, `status_date` y, en Pro/Business, `financials`. Los administradores traen `since`.

El Radar acepta también `cnae`, `min_score`, `main_act_type` y `has_phone`.

### Nombre a CIF (Pro)

```python
r = api.companies.reconcile(['Mercadona', {'name': 'Talleres Pérez', 'province': 'Madrid'}])
# r['data'][i]['status']: match (con company.cif) | ambiguous (candidates) | no_match. Solo se cobran los match.
```

### Segmentos de empresas

```python
total = api.companies.count(cnae=['62'], province='MADRID', has_phone=True)   # gratis
pagina = api.companies.filter(cnae=['62'], province='MADRID', limit=100)      # Business: 5 consultas por fila
# Siguiente página: cursor=pagina['meta']['next_cursor'] con los mismos filtros
```

### Vigilancia de empresas (Pro: 100, Business: 1.000) — sin coste

```python
api.watchlist.add(['A46103834', 'A28015865'])
lista = api.watchlist.list()                     # {'success', 'data', 'meta'}
cambios = api.watchlist.events(since='2026-09-01', types=['borme_act', 'status_change'])
api.watchlist.remove('A28015865')
```

### Webhooks (Business)

```python
hook = api.webhooks.create('https://tu-servidor.com/apiempresas', 'watchlist.*')
# Guarda hook['secret']: con él se comprueba la firma.
api.webhooks.test(hook['id'])
```

En tu servidor, comprueba la firma con el cuerpo en bruto (Flask):

```python
from apiempresas import construct_webhook_event, ApiError

@app.post('/apiempresas')
def apiempresas_webhook():
    try:
        evento = construct_webhook_event(request.get_data(), request.headers.get('X-ApiEmpresas-Signature'), SECRET)
    except ApiError:
        return '', 400
    # evento['event']: company.borme_act | company.status_changed | company.risk_level_changed | test.ping
    return '', 200
```

Si tu servidor no responde 2xx, se reintenta a los 1 min, 5 min, 30 min, 2 h y 6 h.

## Sandbox

Para probar sin gastar consultas, con tu misma API Key y datos simulados:

```python
api = ApiEmpresas('TU_API_KEY', base_url='https://apiempresas.es/api/sandbox/v1')
```

## Errores

```python
try:
    api.companies.get('A15075062')
except ApiError as e:
    print(e.status)      # código HTTP
    print(str(e))        # mensaje legible
    print(e.code)        # p. ej. QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID
    print(e.error_code)  # campo "error" de la respuesta (como en versiones anteriores)
```

Un `429` con `code == 'TOO_MANY_REQUESTS'` se resuelve esperando y reintentando; con `QUOTA_EXCEEDED` no: hay que recargar saldo o cambiar de plan.

## Licencia

MIT
