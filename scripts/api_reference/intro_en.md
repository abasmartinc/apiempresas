REST API with data on Spanish companies: identity, status, activity (CNAE code), registered address, directors and BORME filings, plus commercial score, risk profile and public contracts. All responses are JSON over HTTPS.

## Data sources

| Source | Updated |
|---|---|
| BORME (Spanish Commercial Registry Gazette) | Every business day |
| Spanish Public Procurement Platform | Daily |
| Calculated by APIEmpresas | With each load |

- **BORME:** incorporations, appointments and removals, changes of address, corporate purpose or capital, dissolutions, insolvency proceedings and strike-offs.
- **Public Procurement Platform:** awarded contracts, with contracting body, amounts and dates.
- **Calculated by APIEmpresas:** registry status, commercial score and risk profile, based on those publications.

Calculated data is not a Commercial Registry certificate.

## Environments

| Environment | Base URL | Usage |
|---|---|---|
| Production | `https://spaincompanyapi.com/api/v1` | Every 200 response uses requests from your plan |
| Sandbox | `https://spaincompanyapi.com/api/sandbox/v1` | Free. Same API key and same paths |

The server selector on this page starts on **Sandbox**, so you can try the endpoints without using requests. Switch it to Production to see real data.

## Authentication

Send your API key in the `X-API-KEY` header (`Authorization: Bearer <your_api_key>` is also accepted). You'll find it in your [dashboard](https://spaincompanyapi.com/dashboard) as soon as you [sign up for free](https://spaincompanyapi.com/register?intent=api&source=api_reference_en).

```
curl "https://spaincompanyapi.com/api/v1/companies?cif=A46103834" \
  -H "X-API-KEY: YOUR_API_KEY"
```

## Sandbox

It only accepts these CIFs (Spanish company tax IDs):

- `A15075062`: company found (Inditex). All responses are samples, with fixed data and the same structure as production.
- `B00000000`: company not found (404).

The Sandbox always answers with full data, without the Free plan cuts or quota limits: use it to check the format, not what you will get on your plan. Each response adds a `sandbox` block with the endpoint's minimum plan and the same request in production. `/webhooks` and `/usage` are not in the Sandbox.

## Plans

| Plan | Requests | Speed | Price |
|---|---|---|---|
| Free | 100 in total (not renewed) | 2 per second | Free, no card |
| Pro | 3,000 per month | 20 per second | €19/month + VAT |
| Business | 10,000 per month | 20 per second | €49/month + VAT |

Each endpoint shows the plan it needs; on a lower plan it returns 403 with the plan you need in `required_plan` and a link to upgrade.

## Usage and credits

- Only 200 responses are charged. Errors cost nothing.
- While your plan has requests left, every 200 response uses 1, whatever the endpoint. Exceptions: `/companies/verify` uses 2; `/companies/batch` and `POST /companies/reconcile`, 1 per company found; `/companies/filter` rows, 5 per row.
- Free of charge: the Sandbox, `/usage`, `/catalog`, `/watchlist`, `/webhooks`, the `/companies/filter` count and previews (`/companies/score` on Free and `/companies/insights` on Pro).

When your plan's requests run out, or with [prepaid credits](https://spaincompanyapi.com/buy-api-credits), each 200 response is paid with credits:

| Credits | Endpoints |
|---|---|
| 1 | `/companies`, `/companies/search`; `/companies/batch` and `POST /companies/reconcile`, 1 per company found |
| 2 | `/companies/verify` |
| 3 | score, signals, insights, contact-prep, network, match, radar, borme, contracts and risk-profile |
| 5 per row | `/companies/filter` rows |

**Free plan with credits:** you get Pro access (full data, verify, reconcile, borme, score, signals, radar up to 100, insights preview and network), without batch, monitoring or Business endpoints. While you have credits, every request is paid with them even if you have free requests left, and speed stays at 2 per second.

**Free plan:** the 100 requests count everything used since 28/05/2026, including usage on an earlier paid plan.

**Previews:** what we show without charging (score on Free, insights on Pro, `business_preview` and the counts in some 403 responses) has a shared cap of 25 per day per account. Past the cap, score and insights return 429 `PREVIEW_LIMIT_EXCEEDED`.

## Limits and headers

| Header | Meaning |
|---|---|
| `X-RateLimit-Limit` / `X-RateLimit-Remaining` / `X-RateLimit-Reset` | Per-second limit, what is left in the current window and when it resets |
| `X-Quota-Limit` / `X-Quota-Remaining` | Your plan's requests and how many are left, after this request |
| `X-Quota-Reset` | Only on the quota-exceeded error of paid plans: when it renews |
| `X-Request-Id` | Request identifier; include it if you contact support |

## Errors

Errors follow RFC 7807 (Problem Details) and keep `success`, `error` and `message`. In code, use `code`: it is stable. Messages are in Spanish.

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

| HTTP | `code` | Meaning |
|---|---|---|
| 401 | `API_KEY_MISSING` | The `X-API-KEY` header is missing |
| 401 | `API_KEY_INVALID` | The API key does not exist |
| 403 | `API_KEY_INACTIVE` | The API key or the account is disabled |
| 403 | `PLAN_RESTRICTION` | Your plan does not include this endpoint (the response includes the upgrade link) |
| 429 | `TOO_MANY_REQUESTS` | Too many requests per second: wait as long as `Retry-After` says and retry |
| 429 | `QUOTA_EXCEEDED` | Quota used up and no credits: don't retry, buy credits or upgrade |
| 429 | `IP_LIMIT_EXCEEDED` | Free plan security limit per IP address |
| 429 | `PREVIEW_LIMIT_EXCEEDED` | Daily cap of free previews reached |
| 400 | `VALIDATION_ERROR` | A parameter is missing or not valid |
| 400 | `INVALID_CIF_FORMAT` | The CIF format is not valid |
| 400 | `FAKE_CIF_NOT_ALLOWED` | Test CIF in production: use the Sandbox |
| 400 | `AUTONOMO_NOT_SUPPORTED` | DNI or NIE (individuals): we only provide data on companies |
| 403 | `UPGRADE_REQUIRED` | Same as `PLAN_RESTRICTION`, on `/companies/borme` |
| 403 | `TEST_MODE_RESTRICTION` | In the Sandbox, a CIF that is not a test CIF |
| 404 | `COMPANY_NOT_FOUND` | No company with that CIF or name |
| 404 | `RISK_PROFILE_NOT_AVAILABLE` | The company has no calculated risk profile |
| 404 | `NOT_WATCHING` | You removed a company you were not monitoring |
| 404 | `WEBHOOK_NOT_FOUND` | You have no webhook with that id |
| 502 | `WEBHOOK_DELIVERY_FAILED` | On `/webhooks/{id}/test`, your server did not accept the test delivery |
| 400 | `INVALID_EVENT` | When creating a webhook, an event that does not exist |

## Pagination

`/companies/search?multiple=true` and `/companies/filter` return `meta` with `has_more` and `next_cursor`: for the next page, send that value as is in `cursor`. `/watchlist` and `/watchlist/events` paginate with `page` and `limit` while `meta.has_more` is `true`.

## Official SDKs

```
npm install apiempresas
pip install apiempresas
composer require apiempresas/apiempresas-php
```

## AI assistants (MCP)

Official MCP server for Claude, Cursor, VS Code and other assistants: `npx -y apiempresas-mcp` with the `APIEMPRESAS_API_KEY` variable. Step-by-step setup in the [guide](https://spaincompanyapi.com/docs#mcp); source on [GitHub](https://github.com/abasmartinc/apiempresas-mcp).

Service status: [status.apiempresas.es](https://status.apiempresas.es). Support: [soporte@apiempresas.es](mailto:soporte@apiempresas.es).
