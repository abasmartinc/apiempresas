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
    console.error(`Code: ${error.errorCode}`);
  }
}
```

## Licencia
MIT
