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
```

Requiere Node.js 18 o superior (usa `fetch` nativo).

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
