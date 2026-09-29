# APIEmpresas.es - Node.js SDK

SDK oficial para interactuar con la API de [APIEmpresas.es](https://apiempresas.es) desde Node.js utilizando TypeScript o JavaScript.

## Instalación

```bash
npm install apiempresas
```

## Inicialización

```typescript
import { ApiEmpresas } from 'apiempresas';

const api = new ApiEmpresas({
  apiKey: 'TU_API_KEY', // Obligatorio
});
```

## Uso Básico

### 1. Obtener datos de una empresa

```typescript
async function fetchCompany() {
  try {
    const company = await api.companies.get('A15075062');
    console.log(company.name);
  } catch (error) {
    console.error('Error fetching company:', error.message);
  }
}
```

### 2. Historial de Actos del BORME

```typescript
async function fetchBorme() {
  const bormeData = await api.companies.borme('A15075062');
  console.log(bormeData.events);
}
```

### 3. Consulta Múltiple (Batch)

```typescript
async function fetchBatch() {
  const response = await api.companies.batch({
    cifs: ['A15075062', 'A46103834']
  });
  console.log('Resultados:', response.data);
  console.log('Coste:', response.meta.cost);
}
```

### 4. Administradores, búsqueda con varios resultados, Match y Radar

```typescript
// Administradores y cargos (Pro/Business)
const conAdmins = await api.companies.get('A15075062', { admin: true });

// Varias empresas por nombre, con paginación
let pagina = await api.companies.searchMultiple('software', { limit: 20 });
while (pagina.meta.next_cursor) {
  pagina = await api.companies.searchMultiple('software', { cursor: pagina.meta.next_cursor });
}

// Match (Business): el sector del vendedor es obligatorio
const match = await api.companies.match('A15075062', 'software');

// Radar de empresas nuevas (Business)
const nuevas = await api.companies.radar({ province: 'Madrid', range: 'semana' });
// Filtros añadidos en 1.2.0: cnae, min_score, main_act_type, has_phone
const software = await api.companies.radar({ cnae: '62', min_score: 70, has_phone: true });
```

Requiere Node.js 18 o superior (usa `fetch` nativo).

### 5. Verificación KYB (Pro) — 2 consultas

```typescript
const v = await api.companies.verify('A46103834', {
  name: 'Mercadona SA',        // se compara con la razón social
  person: 'Juan Roig Alfonso', // ¿es administrador vigente?
  vat: true                    // NIF-IVA en VIES
});
console.log(v.decision_hint);  // 'pass' | 'review' | 'fail'
console.log(v.flags);          // alertas con code, severity y message
```

Cada empresa trae además `status_code` (ACTIVE, INSOLVENCY, EXTINCT...), `status_source`, `status_date` y, en Pro/Business, `financials` (tramo de tamaño y último año de cuentas). Los administradores traen `since`.

### Nombre a CIF (Pro)

```typescript
const { data, meta } = await api.companies.reconcile(['Mercadona', { name: 'Talleres Pérez', province: 'Madrid' }]);
// data[i].status: match (con company.cif) | ambiguous (candidates) | no_match. Solo se cobran los match.
```

### Segmentos de empresas

```typescript
const total = await api.companies.count({ cnae: ['62'], province: 'MADRID', has_phone: true }); // gratis
const { data, meta } = await api.companies.filter({ cnae: ['62'], province: 'MADRID', limit: 100 }); // Business: 5 consultas por fila
// Siguiente página: api.companies.filter({ ...mismosFiltros, cursor: meta.next_cursor })
```

### 6. Vigilancia de empresas (Pro: 100, Business: 1.000) — sin coste

```typescript
await api.watchlist.add(['A46103834', 'A28015865']);
const { data: vigiladas, meta } = await api.watchlist.list();   // meta.watch_limit
const { data: cambios } = await api.watchlist.events({ since: '2026-09-01', types: ['borme_act', 'status_change'] });
await api.watchlist.remove('A28015865');
```

### 7. Webhooks (Business)

```typescript
const hook = await api.webhooks.create({ url: 'https://tu-servidor.com/apiempresas', event: 'watchlist.*' });
// Guarda hook.secret: con él se comprueba la firma.
await api.webhooks.test(hook.id); // envía un test.ping y devuelve lo que respondió tu servidor
```

En tu servidor, comprueba la firma con el cuerpo **en bruto** (no el JSON ya parseado):

```typescript
import express from 'express';
import { constructWebhookEvent } from 'apiempresas';

app.post('/apiempresas', express.raw({ type: 'application/json' }), (req, res) => {
  try {
    const evento = constructWebhookEvent(req.body, req.header('X-ApiEmpresas-Signature'), process.env.APIEMPRESAS_WEBHOOK_SECRET!);
    // evento.event: company.borme_act | company.status_changed | company.risk_level_changed | test.ping
    // Usa evento.id (o la cabecera X-ApiEmpresas-Delivery) para no procesar dos veces el mismo envío.
    res.sendStatus(200);
  } catch {
    res.sendStatus(400);
  }
});
```

Si tu servidor no responde 2xx, se reintenta a los 1 min, 5 min, 30 min, 2 h y 6 h.

## Entorno de Pruebas (Sandbox)

Para probar la integración sin consumir saldo, apunta a la URL del Sandbox con tu misma API Key. Devuelve datos simulados: `A15075062` (empresa encontrada) y `B00000000` (no encontrada).

```typescript
const api = new ApiEmpresas({
  apiKey: 'TU_API_KEY',
  baseURL: 'https://apiempresas.es/api/sandbox/v1'
});
```

## Manejo de Errores

El SDK arrojará un `ApiError` detallado si ocurre algún problema (ej. Plan Insuficiente, CIF inválido).

```typescript
import { ApiError } from 'apiempresas';

try {
  await api.companies.score('B00000000');
} catch (error) {
  if (error instanceof ApiError) {
    console.error(`Status: ${error.status}`);
    console.error(`Message: ${error.message}`);
    console.error(`Code: ${error.code}`); // p. ej. QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID
  }
}
```

Un `429` con `code: 'TOO_MANY_REQUESTS'` se resuelve esperando y reintentando; con `code: 'QUOTA_EXCEEDED'` no: hay que recargar saldo o cambiar de plan. `errorCode` sigue devolviendo el campo `error` de la respuesta, como en versiones anteriores.

## Licencia
MIT
