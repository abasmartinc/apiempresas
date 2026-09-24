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
