API REST con los datos de las empresas españolas: identidad, estado, actividad (CNAE), domicilio, administradores y actos del BORME, más puntuación comercial, perfil de riesgo y contratos públicos. Todas las respuestas son JSON y van por HTTPS.

## Fuentes de datos

| Fuente | Actualización |
|---|---|
| BORME (Boletín Oficial del Registro Mercantil) | Cada día hábil |
| Plataforma de Contratación del Sector Público | Cada día |
| Calculado por APIEmpresas | Con cada carga |

- **BORME:** constituciones, nombramientos y ceses, cambios de domicilio, objeto o capital, disoluciones, concursos y extinciones.
- **Plataforma de Contratación:** contratos adjudicados, con órgano, importes y fechas.
- **Calculado por APIEmpresas:** estado registral, puntuación comercial y perfil de riesgo, a partir de esas publicaciones.

Los datos calculados no son un certificado del Registro Mercantil.

## Entornos

| Entorno | URL base | Consumo |
|---|---|---|
| Producción | `https://apiempresas.es/api/v1` | Cada respuesta 200 gasta consultas de tu plan |
| Sandbox | `https://apiempresas.es/api/sandbox/v1` | Gratis. Misma API Key y mismas rutas |

El selector de servidor de esta página empieza en **Sandbox**, así puedes probar las rutas sin gastar consultas. Cámbialo a Producción para ver datos reales.

## Autenticación

Envía tu API Key en la cabecera `X-API-KEY` (también se acepta `Authorization: Bearer <tu_api_key>`). La tienes en tu [panel](https://apiempresas.es/dashboard) nada más [registrarte gratis](https://apiempresas.es/register?intent=api&source=api_reference).

```
curl "https://apiempresas.es/api/v1/companies?cif=A46103834" \
  -H "X-API-KEY: TU_API_KEY"
```

## Sandbox

Solo admite estos CIF:

- `A15075062`: empresa encontrada (Inditex). Todas las respuestas son de ejemplo, con datos fijos y la misma estructura que producción.
- `B00000000`: empresa no encontrada (404).

El Sandbox responde siempre con los datos completos, sin los recortes del plan Free ni límites de cupo: sirve para comprobar el formato, no lo que verás con tu plan. Cada respuesta añade un bloque `sandbox` con el plan mínimo de la ruta y la misma petición en producción. `/webhooks` y `/usage` no están en el Sandbox.

## Planes

| Plan | Consultas | Velocidad | Precio |
|---|---|---|---|
| Free | 100 en total (no se renuevan) | 2 por segundo | Gratis, sin tarjeta |
| Pro | 3.000 al mes | 20 por segundo | 19 €/mes + IVA |
| Business | 10.000 al mes | 20 por segundo | 49 €/mes + IVA |

Cada ruta indica el plan que necesita; con un plan inferior responde 403 con el plan necesario en `required_plan` y el enlace para cambiar.

## Consumo y créditos

- Solo se cobran las respuestas 200. Los errores no gastan nada.
- Mientras te queden consultas en tu plan, cada respuesta 200 gasta 1, sea cual sea la ruta. Excepciones: `/companies/verify` gasta 2; `/companies/batch` y `POST /companies/reconcile`, 1 por empresa encontrada; las filas de `/companies/filter`, 5 por fila.
- No gastan nada: el Sandbox, `/usage`, `/catalog`, `/watchlist`, `/webhooks`, el recuento de `/companies/filter` y las vistas previas (`/companies/score` en Free e `/companies/insights` en Pro).

Cuando se acaban las consultas del plan, o con un [bono sin suscripción](https://apiempresas.es/crear-bono-api), cada respuesta 200 se paga con créditos:

| Créditos | Rutas |
|---|---|
| 1 | `/companies`, `/companies/search`; `/companies/batch` y `POST /companies/reconcile`, 1 por empresa encontrada |
| 2 | `/companies/verify` |
| 3 | score, signals, insights, contact-prep, network, match, radar, borme, contracts y risk-profile |
| 5 por fila | filas de `/companies/filter` |

**Plan Free con bono:** accedes como Pro (datos completos, verify, reconcile, borme, score, signals, radar hasta 100, insights en vista previa y network), sin batch, vigilancia ni rutas de Business. Mientras tengas saldo, cada consulta se paga con créditos aunque te queden consultas gratuitas, y la velocidad sigue en 2 por segundo.

**Plan Free:** las 100 consultas cuentan todo lo consumido desde el 28/05/2026, también con un plan de pago anterior.

**Vistas previas:** lo que se enseña sin cobrar (score en Free, insights en Pro, `business_preview` y los recuentos de algunos 403) tiene un tope conjunto de 25 al día por cuenta. Pasado el tope, score e insights responden 429 `PREVIEW_LIMIT_EXCEEDED`.

## Límites y cabeceras

| Cabecera | Qué indica |
|---|---|
| `X-RateLimit-Limit` / `X-RateLimit-Remaining` / `X-RateLimit-Reset` | Límite por segundo, lo que queda en la ventana actual y cuándo se reinicia |
| `X-Quota-Limit` / `X-Quota-Remaining` | Consultas de tu plan y las que te quedan, ya descontada esta petición |
| `X-Quota-Reset` | Solo en el error de cupo agotado de los planes de pago: cuándo se renueva |
| `X-Request-Id` | Identificador de la petición; dánoslo si escribes a soporte |

## Errores

Los errores siguen RFC 7807 (Problem Details) y conservan `success`, `error` y `message`. Para programar, usa `code`: es estable.

```json
{
  "success": false,
  "error": "COMPANY_NOT_FOUND",
  "code": "COMPANY_NOT_FOUND",
  "message": "Empresa no encontrada.",
  "type": "https://apiempresas.es/docs/errors/company_not_found",
  "title": "COMPANY_NOT_FOUND",
  "status": 404,
  "detail": "Empresa no encontrada.",
  "instance": "req_8f73b1a2c9"
}
```

| HTTP | `code` | Qué significa |
|---|---|---|
| 401 | `API_KEY_MISSING` | Falta la cabecera `X-API-KEY` |
| 401 | `API_KEY_INVALID` | La API Key no existe |
| 403 | `API_KEY_INACTIVE` | La API Key o la cuenta están desactivadas |
| 403 | `PLAN_RESTRICTION` | Tu plan no incluye esta ruta (la respuesta trae el enlace para cambiar) |
| 429 | `TOO_MANY_REQUESTS` | Demasiadas peticiones por segundo: espera lo que diga `Retry-After` y reintenta |
| 429 | `QUOTA_EXCEEDED` | Cupo agotado y sin saldo: no reintentes, recarga o cambia de plan |
| 429 | `IP_LIMIT_EXCEEDED` | Límite de seguridad por IP del plan Free |
| 429 | `PREVIEW_LIMIT_EXCEEDED` | Tope diario de vistas previas gratuitas |
| 400 | `VALIDATION_ERROR` | Falta un parámetro o no es válido |
| 400 | `INVALID_CIF_FORMAT` | El CIF no tiene un formato válido |
| 400 | `FAKE_CIF_NOT_ALLOWED` | CIF de prueba en producción: usa el Sandbox |
| 400 | `AUTONOMO_NOT_SUPPORTED` | DNI o NIE: solo damos datos de sociedades |
| 403 | `UPGRADE_REQUIRED` | Como `PLAN_RESTRICTION`, en `/companies/borme` |
| 403 | `TEST_MODE_RESTRICTION` | En el Sandbox, un CIF que no es de prueba |
| 404 | `COMPANY_NOT_FOUND` | No hay ninguna empresa con ese CIF o nombre |
| 404 | `RISK_PROFILE_NOT_AVAILABLE` | La empresa no tiene perfil de riesgo calculado |
| 404 | `NOT_WATCHING` | Quitas de la vigilancia una empresa que no estaba |
| 404 | `WEBHOOK_NOT_FOUND` | No tienes ningún webhook con ese id |
| 502 | `WEBHOOK_DELIVERY_FAILED` | En `/webhooks/{id}/test`, tu servidor no aceptó el envío de prueba |
| 400 | `INVALID_EVENT` | Al crear un webhook, un evento que no existe |

## Paginación

`/companies/search?multiple=true` y `/companies/filter` devuelven `meta` con `has_more` y `next_cursor`: para la página siguiente, envía ese valor tal cual en `cursor`. `/watchlist` y `/watchlist/events` paginan con `page` y `limit` mientras `meta.has_more` sea `true`.

## SDK oficiales

```
npm install apiempresas
pip install apiempresas
composer require apiempresas/apiempresas-php
```

## Asistentes de IA (MCP)

Servidor MCP oficial para Claude, Cursor, VS Code y otros asistentes: `npx -y apiempresas-mcp` con la variable `APIEMPRESAS_API_KEY`. Configuración paso a paso en la [guía](https://apiempresas.es/documentation#mcp); código en [GitHub](https://github.com/abasmartinc/apiempresas-mcp).

Estado del servicio: [status.apiempresas.es](https://status.apiempresas.es). Soporte: [soporte@apiempresas.es](mailto:soporte@apiempresas.es).
