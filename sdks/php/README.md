# APIEmpresas.es - SDK para PHP

SDK oficial para consultar la API de [APIEmpresas.es](https://apiempresas.es) desde PHP 7.4 o superior (extensiones `curl` y `json`).

## Instalación

```bash
composer require apiempresas/apiempresas-php
```

## Uso

```php
require_once 'vendor/autoload.php';

use ApiEmpresas\ApiEmpresas;
use ApiEmpresas\Exceptions\ApiException;

$api = new ApiEmpresas('TU_API_KEY');

// Datos de una empresa
$empresa = $api->companies->get('A15075062');
echo $empresa['name'];

// Con administradores y cargos (Pro/Business)
$empresa = $api->companies->get('A15075062', ['admin' => true]);

// Varias empresas por nombre, con paginación
$pagina = $api->companies->searchMultiple('software', ['limit' => 20]);
if (!empty($pagina['meta']['next_cursor'])) {
    $pagina = $api->companies->searchMultiple('software', ['cursor' => $pagina['meta']['next_cursor']]);
}

// Consulta múltiple
$lote = $api->companies->batch(['A15075062', 'A46103834']);

// Match (Business): el sector del vendedor es obligatorio
$match = $api->companies->match('A15075062', 'software');

// Radar de empresas nuevas (Business)
$nuevas = $api->companies->radar(['province' => 'Madrid', 'range' => 'semana']);
```

### Verificación KYB (Pro) — 2 consultas

```php
$v = $api->companies->verify('A46103834', [
    'name'   => 'Mercadona SA',        // se compara con la razón social
    'person' => 'Juan Roig Alfonso',   // ¿es administrador vigente?
    'vat'    => true,                  // NIF-IVA en VIES
]);
echo $v['decision_hint'];              // pass | review | fail
```

Cada empresa trae además `status_code` (ACTIVE, INSOLVENCY, EXTINCT...), `status_source`, `status_date` y, en Pro/Business, `financials`. Los administradores traen `since`.

### Radar con filtros (Business)

```php
$nuevas = $api->companies->radar(['province' => 'Madrid', 'cnae' => '62', 'min_score' => 70, 'has_phone' => true]);
```

### Nombre a CIF (Pro)

```php
$r = $api->companies->reconcile(['Mercadona', ['name' => 'Talleres Pérez', 'province' => 'Madrid']]);
// $r['data'][i]['status']: match (con company.cif) | ambiguous (candidates) | no_match. Solo se cobran los match.
```

### Segmentos de empresas

```php
$total = $api->companies->count(['cnae' => ['62'], 'province' => 'MADRID', 'has_phone' => true]); // gratis
$pagina = $api->companies->filter(['cnae' => ['62'], 'province' => 'MADRID', 'limit' => 100]);    // Business: 5 consultas por fila
// Siguiente página: 'cursor' => $pagina['meta']['next_cursor'] con los mismos filtros
```

### Vigilancia de empresas (Pro: 100, Business: 1.000) — sin coste

```php
$api->watchlist->add(['A46103834', 'A28015865']);
$lista   = $api->watchlist->list();                       // ['data' => [...], 'meta' => [...]]
$cambios = $api->watchlist->events(['since' => '2026-09-01', 'types' => ['borme_act', 'status_change']]);
$api->watchlist->remove('A28015865');
```

### Webhooks (Business)

```php
$hook = $api->webhooks->create(['url' => 'https://tu-servidor.com/apiempresas', 'event' => 'watchlist.*']);
// Guarda $hook['secret']: con él se comprueba la firma.
$api->webhooks->test($hook['id']);
```

En tu servidor, comprueba la firma con el cuerpo en bruto:

```php
use ApiEmpresas\Resources\Webhooks;

$raw = file_get_contents('php://input');
try {
    $evento = Webhooks::constructEvent($raw, $_SERVER['HTTP_X_APIEMPRESAS_SIGNATURE'] ?? null, getenv('APIEMPRESAS_WEBHOOK_SECRET'));
    // $evento['event']: company.borme_act | company.status_changed | company.risk_level_changed | test.ping
    http_response_code(200);
} catch (\ApiEmpresas\Exceptions\ApiException $e) {
    http_response_code(400);
}
```

Si tu servidor no responde 2xx, se reintenta a los 1 min, 5 min, 30 min, 2 h y 6 h.

## Sandbox

Para probar sin gastar consultas, con tu misma API Key y datos simulados:

```php
$api = new ApiEmpresas('TU_API_KEY', 'https://apiempresas.es/api/sandbox/v1');
```

## Errores

```php
try {
    $api->companies->get('A15075062');
} catch (ApiException $e) {
    echo $e->getStatusCode();  // código HTTP
    echo $e->getMessage();     // mensaje legible
    echo $e->getApiCode();     // p. ej. QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID
    echo $e->getErrorCode();   // campo "error" de la respuesta (como en versiones anteriores)
}
```

Un `429` con código `TOO_MANY_REQUESTS` se resuelve esperando y reintentando; con `QUOTA_EXCEEDED` no: hay que recargar saldo o cambiar de plan.

## Licencia

MIT
