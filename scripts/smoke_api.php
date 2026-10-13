<?php
/**
 * Prueba rápida de la API antes de subir a producción (13-10-2026).
 *
 * Uso (desde la carpeta del proyecto):
 *   php scripts/smoke_api.php http://apiempresas.test TU_API_KEY
 *
 * - Sandbox: todas las rutas, no gasta nada.
 * - Producción: unas 4 consultas de tu plan (companies, score, signals, search).
 * - Con una clave Business se prueban también los webhooks (no gastan).
 * Al final dice cuántas comprobaciones fallan. No cambia nada (no crea webhooks ni
 * añade empresas a la vigilancia de producción).
 */

$base = rtrim($argv[1] ?? '', '/');
$key  = $argv[2] ?? '';
if ($base === '' || $key === '') {
    fwrite(STDERR, "Uso: php smoke_api.php http://apiempresas.test TU_API_KEY\n");
    exit(1);
}

$fallos = 0;
$total  = 0;

function llamar(string $metodo, string $url, ?string $key, $body = null): array
{
    $ch = curl_init($url);
    $h  = ['Accept: application/json'];
    if ($key !== null) {
        $h[] = 'X-API-KEY: ' . $key;
    }
    if ($body !== null) {
        $h[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $metodo,
        CURLOPT_HTTPHEADER     => $h,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER         => true,
    ]);
    $raw    = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hsize  = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr($raw, 0, $hsize);
    $cuerpo  = substr($raw, $hsize);
    usleep(600000); // por debajo del límite por segundo del Free
    return [$status, json_decode($cuerpo, true), $headers, $cuerpo];
}

/** $check: callable(array|null $json, string $headers): true o texto del fallo */
function prueba(string $nombre, string $metodo, string $url, ?string $key, $esperado, ?callable $check = null, $body = null): ?array
{
    global $fallos, $total;
    $total++;
    [$st, $j, $h, $c] = llamar($metodo, $url, $key, $body);
    $esperados = (array) $esperado;
    $ok = in_array($st, $esperados, true);
    $motivo = $ok ? '' : "HTTP $st (esperaba " . implode('/', $esperados) . ')';
    if ($ok && $check) {
        $r = $check($j, $h, $st);
        if ($r !== true) {
            $ok = false;
            $motivo = (string) $r;
        }
    }
    if (!$ok) {
        $fallos++;
        $extra = is_array($j) ? (' · code=' . ($j['code'] ?? '-') . ' · ' . mb_substr((string) ($j['message'] ?? ''), 0, 120)) : (' · ' . mb_substr(trim(strip_tags($c)), 0, 120));
        echo "  FALLO  $nombre: $motivo$extra\n";
    } else {
        echo "  ok     $nombre\n";
    }
    return $j;
}

function tiene(array $campos): callable
{
    return function ($j) use ($campos) {
        if (!is_array($j)) return 'la respuesta no es JSON';
        foreach ($campos as $ruta) {
            $n = $j;
            foreach (explode('.', $ruta) as $p) {
                if (!is_array($n) || !array_key_exists($p, $n)) return "falta $ruta";
                $n = $n[$p];
            }
        }
        return true;
    };
}

$sb = $base . '/api/sandbox/v1';
$pr = $base . '/api/v1';
$T  = 'A15075062';

echo "\n== Páginas\n";
$total++;
[$st, , $h] = llamar('GET', $base . '/api/docs', null);
if ($st === 301 && stripos($h, 'api/referencia') !== false) { echo "  ok     /api/docs redirige a /api/referencia\n"; } else { $fallos++; echo "  FALLO  /api/docs: HTTP $st\n"; }
foreach (['/api/referencia', '/api/reference', '/documentation', '/api/docs/openapi.json'] as $p) {
    prueba($p, 'GET', $base . $p, null, 200);
}
foreach (['/api/referencia/openapi.json', '/api/reference/openapi.json'] as $p) {
    prueba($p, 'GET', $base . $p, null, 200, function ($j) {
        return (is_array($j) && isset($j['openapi'], $j['paths']['/catalog'], $j['paths']['/webhooks/{id}/deliveries'])) ? true : 'especificación incompleta';
    });
}

echo "\n== Sandbox (no gasta)\n";
prueba('companies', 'GET', "$sb/companies?cif=$T", $key, 200, tiene(['data.name', 'sandbox']));
prueba('companies 404', 'GET', "$sb/companies?cif=B00000000", $key, 404);
prueba('search', 'GET', "$sb/companies/search?q=inditex", $key, 200);
prueba('verify', 'GET', "$sb/companies/verify?cif=$T", $key, 200);
prueba('score', 'GET', "$sb/companies/score?cif=$T", $key, 200, tiene(['data.score', 'data.priority', 'data.reasons', 'data.last_signal.url_pdf']));
prueba('signals', 'GET', "$sb/companies/signals?cif=$T", $key, 200, tiene(['data.signals.0.type', 'data.signals.0.label', 'data.signals.0.date', 'data.signals.0.probability', 'data.signals.0.url_pdf']));
prueba('borme', 'GET', "$sb/companies/borme?cif=$T", $key, 200);
prueba('network', 'GET', "$sb/companies/network?cif=$T", $key, 200);
prueba('risk-profile', 'GET', "$sb/companies/risk-profile?cif=$T", $key, 200);
prueba('contracts', 'GET', "$sb/companies/contracts?cif=$T", $key, 200);
prueba('insights', 'GET', "$sb/companies/insights?cif=$T", $key, 200);
prueba('contact-prep', 'GET', "$sb/companies/contact-prep?cif=$T", $key, 200);
prueba('match', 'GET', "$sb/companies/match?cif=$T&seller_sector=software", $key, 200);
prueba('radar', 'GET', "$sb/companies/radar", $key, 200);
prueba('filter', 'GET', "$sb/companies/filter?province=Madrid&count_only=true", $key, 200);
prueba('batch', 'POST', "$sb/companies/batch", $key, 200, null, ['cifs' => [$T, 'B00000000']]);
prueba('reconcile', 'POST', "$sb/companies/reconcile", $key, 200, null, ['items' => [['name' => 'Inditex']]]);
prueba('watchlist', 'GET', "$sb/watchlist", $key, 200);
prueba('watchlist/events', 'GET', "$sb/watchlist/events", $key, 200);
prueba('catalog', 'GET', "$sb/catalog", $key, 200, tiene(['data.province', 'data.radar', 'data.webhook_events']));

echo "\n== Producción (gasta unas 4 consultas)\n";
$C = 'A46103834';
prueba('sin clave → 401 API_KEY_MISSING', 'GET', "$pr/companies?cif=$C", null, 401, fn($j) => ($j['code'] ?? '') === 'API_KEY_MISSING' ? true : 'code=' . ($j['code'] ?? '-'));
prueba('clave falsa → 401', 'GET', "$pr/companies?cif=$C", 'clave_que_no_existe', 401);
prueba('CIF mal formado → 400 (no gasta)', 'GET', "$pr/companies?cif=XX", $key, 400);
prueba('companies', 'GET', "$pr/companies?cif=$C", $key, 200, function ($j, $h) {
    if (!isset($j['data']['name'])) return 'falta data.name';
    return stripos($h, 'X-Quota-Remaining') !== false ? true : 'falta la cabecera X-Quota-Remaining';
});
prueba('search', 'GET', "$pr/companies/search?q=mercadona", $key, 200);
$score = prueba('score', 'GET', "$pr/companies/score?cif=$C", $key, [200, 403]);
if (isset($score['data']['fuerza_financiera'])) {
    echo "         (Free: respuesta de vista previa con los campos de siempre)\n";
} elseif (isset($score['data']['last_signal']) && is_array($score['data']['last_signal'])) {
    $total++;
    if (array_key_exists('url_pdf', $score['data']['last_signal'])) { echo "  ok     score: last_signal.url_pdf\n"; } else { $fallos++; echo "  FALLO  score: falta last_signal.url_pdf\n"; }
}
prueba('signals', 'GET', "$pr/companies/signals?cif=$C", $key, [200, 403], function ($j, $h, $st) {
    if ($st === 403) return true; // plan Free sin bono
    return array_key_exists('url_pdf', $j['data']['signals'][0] ?? []) ? true : 'falta signals[0].url_pdf';
});
prueba('usage (no gasta)', 'GET', "$pr/usage", $key, 200);
prueba('catalog (no gasta)', 'GET', "$pr/catalog", $key, 200, tiene(['data.province', 'data.size_band', 'data.status']));
prueba('watchlist (no gasta)', 'GET', "$pr/watchlist", $key, [200, 403]);

echo "\n== Webhooks (no gastan; con Business devuelven 200/404, con otro plan 403)\n";
prueba('webhooks', 'GET', "$pr/webhooks", $key, [200, 403], function ($j, $h, $st) {
    return $st === 403 ? (($j['code'] ?? '') === 'PLAN_RESTRICTION' ? true : 'code=' . ($j['code'] ?? '-')) : true;
});
prueba('deliveries de un id que no existe', 'GET', "$pr/webhooks/999999999/deliveries", $key, [404, 403], function ($j, $h, $st) {
    $esperado = $st === 404 ? 'WEBHOOK_NOT_FOUND' : 'PLAN_RESTRICTION';
    return ($j['code'] ?? '') === $esperado ? true : 'code=' . ($j['code'] ?? '-');
});
prueba('borrar un id que no existe', 'DELETE', "$pr/webhooks/999999999", $key, [404, 403]);
prueba('crear con evento retirado → 400 INVALID_EVENT', 'POST', "$pr/webhooks", $key, [400, 403], function ($j, $h, $st) {
    return $st === 403 || ($j['error'] ?? '') === 'INVALID_EVENT' ? true : 'error=' . json_encode($j['error'] ?? null);
}, ['url' => 'https://example.com/hook', 'event' => 'company.created']);

echo "\n$total comprobaciones, $fallos con fallo.\n";
exit($fallos ? 1 : 0);
