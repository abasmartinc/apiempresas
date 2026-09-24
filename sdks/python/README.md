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
