# Guia de Ejecución de Scripts (Workflow apiempresas_flow)

## Herramientas de Automatización
- `config.py`: Centraliza la conexión a la base de datos (IP, usuario, password). **Edita este archivo para configurar tu entorno en Loading.**
- `00_run_pipeline.py`: Ejecuta automáticamente las primeras 5 fases (Scripts 01 al 05). Es el script que debes programar en el Cron Job de Loading.

## Flujo de Trabajo Organizado
Úsalos siguiendo la numeración o ejecuta el pipeline automatizado:

## Fase 1: Extracción del BORME (Bruto)
1. `01_extract_sumario.py` - Descarga el sumario diario del BOE.
2. `02_parse_details.py` - Extrae el texto de los actos del BORME y llena `borme_posts`.

## Fase 2: Identificación de Empresas (Candidatos)
3. `03_find_new_candidates.py` - Detecta nombres de empresas nuevas en los anuncios del día.
4. `04_insert_new_companies.py` - Registra esos nombres en la tabla `companies` (creando la ficha inicial).
5. `05_associate_companies.py` - Vincula formalmente cada anuncio con su `company_id`.

## Fase 3: Identificación Legal (Obtención de CIF)
6. `06_update_cif_cincodias.py` - Busca el CIF vía API de Cinco Días y fusiona posibles duplicados.
7. `07_update_cif_infonif.py` - Alternativa para buscar el CIF vía API de Infonif.
8. `08_update_cif_from_borme_text.py` - Recupera el CIF directamente del texto del anuncio escrito en el BORME.

## Fase 4: Enriquecimiento de Datos (Ficha Técnica)
9. `09_update_details_cincodias.py` - Obtiene dirección, CNAE y objeto social desde Cinco Días.
10. `10_update_address_infonif.py` - Completa direcciones faltantes consultando la API de Infonif.
11. `11_update_details_empresia.py` - Obtiene capital social, cargos y administradores desde Empresia.
12. `12_update_address_from_borme_acts.py` - Si el BORME anuncia un cambio de domicilio, actualiza la dirección según el texto.

## Fase 5: Geolocalización y Detalles Finales
13. `13_update_coordinates_gps.py` - Genera latitud y longitud a partir de la dirección oficial.
14. `14_update_registry_province.py` - Asocia el Registro Mercantil correcto según la provincia/CP.
15. `15_extract_administrators.py` - Procesa el texto del BORME para listar cargos específicos (Administrador Único, etc.).
16. `16_update_constitution_date.py` - Registra la fecha oficial de constitución de la empresa.
17. `17_update_details_empresia_by_cif.py` - Enriquecimiento de datos buscando directamente por CIF en Empresia.

---
**Nota**: Todos los scripts originales siguen en sus carpetas originales. Esta carpeta es una copia organizada para facilitar el flujo de trabajo diario.
