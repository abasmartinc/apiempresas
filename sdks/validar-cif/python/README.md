# validar-cif

Valida **CIF, DNI y NIE** españoles (formato y dígito de control) en **JavaScript, Python y PHP**. Sin dependencias, con tests y con el mismo resultado en los tres lenguajes.

Mantenido por [APIEmpresas.es](https://apiempresas.es). Explicación del algoritmo y más ejemplos: [Validar CIF en PHP, Python y JavaScript](https://apiempresas.es/validar-cif-php-python).

> Un CIF bien formado **no garantiza que la empresa exista**: un número inventado puede cuadrar. Para saber si hay una sociedad con ese CIF, su razón social y si sigue activa, consulta la [API de empresas](https://apiempresas.es) (100 consultas gratis) o el [validador online](https://apiempresas.es/validar-cif).

## JavaScript / Node.js

```bash
npm install validar-cif
```

```js
const { validarCif, validarNif, tipoNif, tipoEntidad } = require('validar-cif');

validarCif('A46103834');      // true
validarCif('a-46.103.834');   // true (limpia guiones, puntos, espacios y minúsculas)
validarCif('A46103835');      // false: el control debería ser 4
validarNif('12345678Z');      // true (CIF, DNI o NIE)
tipoNif('X1234567L');         // 'nie'
tipoEntidad('B00000000');     // 'Sociedad de responsabilidad limitada'
```

## Python

```bash
pip install validar-cif
```

```python
from validar_cif import validar_cif, validar_nif, tipo_nif, tipo_entidad

validar_cif("A46103834")   # True
validar_nif("12345678Z")   # True
tipo_nif("X1234567L")      # 'nie'
```

## PHP

```bash
composer require apiempresas/validar-cif
```

```php
use ApiEmpresas\ValidarCif\Validador;

Validador::cif('A46103834');   // true
Validador::nif('12345678Z');   // true
Validador::tipo('X1234567L');  // 'nie'
```

## Funciones

| JavaScript | Python | PHP | Devuelve |
|---|---|---|---|
| `validarCif` | `validar_cif` | `Validador::cif` | `true` si el CIF de empresa está bien formado |
| `validarDni` | `validar_dni` | `Validador::dni` | `true` si la letra del DNI es correcta |
| `validarNie` | `validar_nie` | `Validador::nie` | `true` si la letra del NIE es correcta |
| `validarNif` | `validar_nif` | `Validador::nif` | `true` si es CIF, DNI o NIE válido |
| `tipoNif` | `tipo_nif` | `Validador::tipo` | `'cif'`, `'dni'`, `'nie'` o nulo |
| `tipoEntidad` | `tipo_entidad` | `Validador::tipoEntidad` | Forma jurídica según la letra del CIF |

## Comprobar si la empresa existe

```bash
curl "https://apiempresas.es/api/v1/companies?cif=A46103834" -H "X-API-KEY: TU_API_KEY"
```

Devuelve razón social, estado (activa, en concurso, extinguida…), CNAE, provincia y, en el plan Pro, administradores y domicilio. SDK oficiales: `npm install apiempresas`, `pip install apiempresas`, `composer require apiempresas/apiempresas-php`. [Crear cuenta gratis](https://apiempresas.es/register?intent=api&plan=free&source=github_validar_cif).

## English

Validates Spanish tax IDs (CIF for companies, DNI and NIE for individuals) in JavaScript, Python and PHP, with no dependencies. To check whether a company actually exists and is active, use the [Spanish company data API](https://spaincompanyapi.com).

## Licencia

MIT
