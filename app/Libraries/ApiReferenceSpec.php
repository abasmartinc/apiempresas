<?php

namespace App\Libraries;

use OpenApi\Generator;

/**
 * Especificación OpenAPI para la referencia de la API con Scalar: /api/referencia (es) y
 * /api/reference (en, desde el 13-10-2026).
 *
 * Parte de las anotaciones #[OA\...] de app/Controllers/Api (las mismas que /api/docs) y les
 * aplica app/Docs/openapi_overlay_{es|en}.json: introducción, servidores (Sandbox y producción),
 * grupos por función, plan de cada ruta, textos y ejemplos de respuesta.
 * El overlay se genera a partir de documentation.php / documentation_en_standalone.php
 * (scripts/api_reference). En inglés, los textos de las anotaciones (parámetros y respuestas)
 * se traducen con app/Docs/openapi_i18n_en.json.
 */
class ApiReferenceSpec
{
    private const IDIOMAS = ['es', 'en'];
    private const CACHE_TTL = 3600;

    private static function overlayPath(string $lang): string
    {
        return APPPATH . 'Docs/openapi_overlay_' . $lang . '.json';
    }

    private static function i18nPath(string $lang): string
    {
        return APPPATH . 'Docs/openapi_i18n_' . $lang . '.json';
    }

    public static function get(string $lang = 'es'): array
    {
        $lang  = in_array($lang, self::IDIOMAS, true) ? $lang : 'es';
        $mtime = static fn (string $f) => is_file($f) ? filemtime($f) : 0;
        $cache = \Config\Services::cache();
        $key   = 'api_reference_spec_' . $lang . '_' . $mtime(self::overlayPath($lang)) . '_'
            . $mtime(self::i18nPath($lang)) . '_' . filemtime(__FILE__);
        $spec  = $cache->get($key);
        if (is_array($spec)) {
            return $spec;
        }

        $spec = self::build($lang);
        $cache->save($key, $spec, self::CACHE_TTL);

        return $spec;
    }

    public static function build(string $lang = 'es'): array
    {
        $lang    = in_array($lang, self::IDIOMAS, true) ? $lang : 'es';
        $base    = json_decode((new Generator())->generate([APPPATH . 'Controllers/Api'])->toJson(), true) ?: [];
        $overlay = json_decode((string) @file_get_contents(self::overlayPath($lang)), true);
        if (!is_array($overlay)) {
            return $base;
        }

        $spec = [
            'openapi'    => $base['openapi'] ?? '3.0.0',
            'info'       => $overlay['info'],
            'servers'    => $overlay['servers'],
            'security'   => $base['security'] ?? [['ApiKeyAuth' => []]],
            'tags'       => $overlay['tags'],
            'x-tagGroups' => $overlay['x-tagGroups'],
            'paths'      => [],
            'components' => $base['components'] ?? new \stdClass(),
        ];

        $ovPaths = $overlay['paths'] ?? [];
        // La línea de coste de las anotaciones está en español: en inglés no se añade
        // (la introducción explica el consumo de cada ruta)
        $conCoste = $lang === 'es';

        // 1. Rutas de las anotaciones, sin el prefijo /api/v1 (va en el servidor)
        foreach (($base['paths'] ?? []) as $path => $item) {
            $short = preg_replace('#^/api/v1#', '', $path) ?: '/';
            foreach ($item as $method => $op) {
                if (!is_array($op) || !isset($op['responses'])) {
                    continue;
                }
                $ov = $ovPaths[$short][$method] ?? null;
                if ($ov) {
                    $op = self::applyOverlay($op, $ov, $overlay['production_server'], $conCoste);
                }
                $spec['paths'][$short][$method] = $op;
            }
        }

        // 2. Rutas documentadas que no tienen anotación (p. ej. POST /webhooks/{id}/test)
        foreach ($ovPaths as $short => $methods) {
            foreach ($methods as $method => $ov) {
                if (isset($spec['paths'][$short][$method])) {
                    continue;
                }
                $op = ['responses' => ['200' => ['description' => 'OK']]];
                if (preg_match_all('/\{(\w+)\}/', $short, $m)) {
                    foreach ($m[1] as $name) {
                        $op['parameters'][] = ['name' => $name, 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']];
                    }
                }
                $spec['paths'][$short][$method] = self::applyOverlay($op, $ov, $overlay['production_server'], $conCoste);
            }
        }

        // 3. Orden del overlay (lo más usado primero dentro de cada grupo)
        $ordenadas = [];
        foreach (array_keys($ovPaths) as $short) {
            if (isset($spec['paths'][$short])) {
                $ordenadas[$short] = $spec['paths'][$short];
            }
        }
        $spec['paths'] = $ordenadas + $spec['paths'];

        // 4. Traducción de los textos que vienen de las anotaciones (parámetros, respuestas, esquemas)
        $dic = $lang === 'es' ? null : json_decode((string) @file_get_contents(self::i18nPath($lang)), true);
        if (is_array($dic) && $dic) {
            foreach ($spec['paths'] as $p => $ops) {
                foreach ($ops as $m => $op) {
                    foreach (['parameters', 'responses', 'requestBody'] as $k) {
                        if (isset($op[$k])) {
                            $spec['paths'][$p][$m][$k] = self::traducir($op[$k], $dic);
                        }
                    }
                }
            }
            if (is_array($spec['components'])) {
                $spec['components'] = self::traducir($spec['components'], $dic);
            }
        }

        return $spec;
    }

    /** Sustituye los campos description que estén en el diccionario. No toca los ejemplos. */
    private static function traducir($nodo, array $dic)
    {
        if (!is_array($nodo)) {
            return $nodo;
        }
        foreach ($nodo as $k => $v) {
            if ($k === 'example' || $k === 'examples') {
                continue;
            }
            if ($k === 'description' && is_string($v)) {
                $nodo[$k] = $dic[$v] ?? $v;
            } elseif (is_array($v)) {
                $nodo[$k] = self::traducir($v, $dic);
            }
        }
        return $nodo;
    }

    private static function applyOverlay(array $op, array $ov, array $production, bool $conCoste = true): array
    {
        // El coste de la anotación (si lo hay) se conserva al final
        $coste = '';
        if ($conCoste && preg_match('/\*\*Coste:\*\*.*$/s', (string) ($op['description'] ?? ''), $m)) {
            $coste = "\n\n" . trim($m[0]);
        }

        $op['tags']        = [$ov['tag']];
        $op['summary']     = $ov['summary'];
        $op['description'] = $ov['description'] . $coste;

        if (empty($ov['sandbox'])) {
            $op['servers'] = [$production];
        }

        if ($ov['response_example'] !== null) {
            $code = isset($op['responses']['201']) && !isset($op['responses']['200']) ? '201' : '200';
            $op['responses'][$code]['description'] = $op['responses'][$code]['description'] ?? 'OK';
            $op['responses'][$code]['content']['application/json']['example'] = $ov['response_example'];
        }

        if ($ov['request_example'] !== null) {
            $op['requestBody']['required'] = true;
            $op['requestBody']['content']['application/json']['example'] = $ov['request_example'];
        }

        // Un schema vacío llega como [] (json_decode) y saldría "schema": [], que no es
        // OpenAPI válido: tiene que ser un objeto (13-10-2026)
        foreach (['requestBody'] as $k) {
            foreach (($op[$k]['content'] ?? []) as $tipo => $c) {
                if (array_key_exists('schema', (array) $c) && $c['schema'] === []) {
                    $op[$k]['content'][$tipo]['schema'] = new \stdClass();
                }
            }
        }
        foreach (($op['responses'] ?? []) as $codigo => $r) {
            foreach ((is_array($r) ? ($r['content'] ?? []) : []) as $tipo => $c) {
                if (is_array($c) && array_key_exists('schema', $c) && $c['schema'] === []) {
                    $op['responses'][$codigo]['content'][$tipo]['schema'] = new \stdClass();
                }
            }
        }

        return $op;
    }
}
