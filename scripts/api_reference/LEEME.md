# Referencia de la API (Scalar)

Página: `/api/referencia` (vista `app/Views/api/reference.php`, controlador `ApiReference`).
JSON: `/api/referencia/openapi.json` (`app/Libraries/ApiReferenceSpec.php`).

La especificación sale de las anotaciones `#[OA\...]` de `app/Controllers/Api` y se completa con
`app/Docs/openapi_overlay_es.json`:

- introducción;
- servidores (Sandbox primero, producción después);
- grupos del menú y plan de cada ruta;
- textos y ejemplos.

## Regenerar el overlay

Los textos y ejemplos de cada ruta salen de `app/Views/documentation.php`. La introducción está en
`intro_es.md`. El plan, el grupo y el título de cada ruta están en `META`, dentro de `assemble.py`.

```
pip install beautifulsoup4 markdownify
cd scripts/api_reference
python build_overlay.py ../../app/Views/documentation.php endpoints_es.json
python assemble.py
copy openapi_overlay_es.json ..\..\app\Docs\openapi_overlay_es.json
```

La caché del servidor (1 hora) se renueva sola al cambiar el overlay o `ApiReferenceSpec.php`.

Si se añade una ruta a la API:
- ponle su anotación;
- documéntala en `documentation.php`;
- añádela a `META`, en `assemble.py`.

`assemble.py` falla si `META` y las rutas de la documentación no coinciden.

## Colecciones de Postman

Las cuatro colecciones de `public/docs/` (completa, Free, Pro y Business) se actualizan con
`build_postman.py`, que toma los ejemplos de respuesta del overlay. Hay que ejecutarlo después de
regenerar el overlay:

```
python build_postman.py ..\..\public\docs\apiempresas_postman.json ..\..\app\Docs\openapi_overlay_es.json ..\..\public\docs\apiempresas_postman.json
```

Repetir con `_free`, `_pro` y `_business`. El script no añade peticiones nuevas: una ruta nueva
se añade a mano en Postman (con `{{BASE_URL}}`) y luego se pasa el script.

## Versión en inglés (/api/reference)

Misma página y misma especificación, con `app/Docs/openapi_overlay_en.json`. Los textos salen
de `app/Views/documentation_en_standalone.php`, la introducción de `intro_en.md` y el plan, grupo
y título de cada ruta de `META` en `assemble_en.py`. Los textos de las anotaciones (parámetros y
respuestas), que están en español, se traducen con `app/Docs/openapi_i18n_en.json`: si se añade
o cambia una anotación, hay que añadir su traducción ahí (lo que no esté sale en español).

```
python build_overlay.py ../../app/Views/documentation_en_standalone.php endpoints_en.json https://spaincompanyapi.com
python assemble_en.py
copy openapi_overlay_en.json ..\..\app\Docs\openapi_overlay_en.json
```
