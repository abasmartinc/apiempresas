-- =====================================================================
-- Perfilado de datos para la API (solo lectura: no modifica nada)
-- 26-09-2026. Lanzar en HeidiSQL contra la BD `apiempresas` (local).
-- Cada bloque es independiente: si uno falla por una columna que no
-- existe, sigue con el siguiente. Las consultas sobre `companies` y
-- `borme_posts` recorren tablas grandes: pueden tardar varios minutos.
-- Pásame el resultado de cada bloque (copiar como CSV o captura).
-- =====================================================================

USE apiempresas;

-- ---------------------------------------------------------------------
-- 1) Tamaño de las tablas de datos (aproximado, instantáneo)
-- ---------------------------------------------------------------------
SELECT TABLE_NAME AS tabla, TABLE_ROWS AS filas_aprox,
       ROUND((DATA_LENGTH + INDEX_LENGTH)/1024/1024) AS mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('companies','company_enrichment','company_administrators','borme_posts',
                     'company_contracts','company_subsidies','company_risk_profiles',
                     'company_risk_profiles_history','company_radar_scores','company_embeddings',
                     'holdings','company_holdings','company_ratings','user_company_watch',
                     'api_requests','api_usage_daily','api_webhooks','cnae_2009_2025')
ORDER BY TABLE_ROWS DESC;

-- Columnas reales de companies y company_enrichment (para saber qué hay)
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('companies','company_enrichment')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- ---------------------------------------------------------------------
-- 2) Relleno de cada campo de companies (total y solo activas)
--    Lo que la API ya da y lo que tenemos pero NO da
-- ---------------------------------------------------------------------
SELECT
  COUNT(*)                                                         AS total,
  SUM(estado IN ('ACTIVA','Activa','activa'))                      AS activas,
  ROUND(100*AVG(NULLIF(TRIM(address),'') IS NOT NULL),1)           AS pct_direccion,
  ROUND(100*AVG(NULLIF(TRIM(municipality),'') IS NOT NULL),1)      AS pct_municipio,
  ROUND(100*AVG(lat_num IS NOT NULL AND lat_num <> 0),1)           AS pct_geo,
  ROUND(100*AVG(NULLIF(TRIM(cnae_code),'') IS NOT NULL),1)         AS pct_cnae,
  ROUND(100*AVG(NULLIF(TRIM(objeto_social),'') IS NOT NULL),1)     AS pct_objeto,
  ROUND(100*AVG(fecha_constitucion IS NOT NULL),1)                 AS pct_constitucion,
  ROUND(100*AVG(NULLIF(TRIM(capital_social_raw),'') IS NOT NULL),1) AS pct_capital,
  ROUND(100*AVG(NULLIF(TRIM(estado),'') IS NOT NULL),1)            AS pct_estado,
  -- Campos que existen y la API no devuelve:
  ROUND(100*AVG(NULLIF(TRIM(phone),'') IS NOT NULL),1)             AS pct_telefono,
  ROUND(100*AVG(NULLIF(TRIM(phone_mobile),'') IS NOT NULL),1)      AS pct_movil,
  ROUND(100*AVG(NULLIF(TRIM(ventas_raw),'') IS NOT NULL),1)        AS pct_ventas,
  ROUND(100*AVG(NULLIF(TRIM(ult_cuentas_anio),'') IS NOT NULL),1)  AS pct_anio_cuentas,
  ROUND(100*AVG(estado_fecha IS NOT NULL),1)                       AS pct_estado_fecha,
  ROUND(100*AVG(NULLIF(TRIM(duracion_raw),'') IS NOT NULL),1)      AS pct_duracion
FROM companies;

-- Lo mismo, solo empresas activas (lo que de verdad consulta un cliente)
SELECT
  COUNT(*) AS activas,
  ROUND(100*AVG(NULLIF(TRIM(address),'') IS NOT NULL),1)           AS pct_direccion,
  ROUND(100*AVG(lat_num IS NOT NULL AND lat_num <> 0),1)           AS pct_geo,
  ROUND(100*AVG(NULLIF(TRIM(cnae_code),'') IS NOT NULL),1)         AS pct_cnae,
  ROUND(100*AVG(NULLIF(TRIM(phone),'') IS NOT NULL OR NULLIF(TRIM(phone_mobile),'') IS NOT NULL),1) AS pct_algun_tel,
  ROUND(100*AVG(NULLIF(TRIM(ventas_raw),'') IS NOT NULL),1)        AS pct_ventas,
  ROUND(100*AVG(NULLIF(TRIM(ult_cuentas_anio),'') IS NOT NULL),1)  AS pct_anio_cuentas
FROM companies
WHERE estado IN ('ACTIVA','Activa','activa');

-- Código postal (si la columna no existe, este bloque falla: no pasa nada)
SELECT ROUND(100*AVG(NULLIF(TRIM(postal_code),'') IS NOT NULL),1) AS pct_cp FROM companies;

-- ---------------------------------------------------------------------
-- 3) Valores de los campos "sucios" (para normalizar en la API)
-- ---------------------------------------------------------------------
-- Estados tal cual (la API los devuelve sin normalizar)
SELECT estado, COUNT(*) AS n FROM companies GROUP BY estado ORDER BY n DESC LIMIT 40;

-- Tramos de ventas (¿son tramos o importes? ¿cuántos formatos?)
SELECT ventas_raw, COUNT(*) AS n FROM companies
WHERE NULLIF(TRIM(ventas_raw),'') IS NOT NULL
GROUP BY ventas_raw ORDER BY n DESC LIMIT 30;

-- Año de últimas cuentas depositadas (frescura del dato)
SELECT ult_cuentas_anio, COUNT(*) AS n FROM companies
WHERE NULLIF(TRIM(ult_cuentas_anio),'') IS NOT NULL
GROUP BY ult_cuentas_anio ORDER BY ult_cuentas_anio DESC LIMIT 20;

-- Capital social: formatos (muestra)
SELECT capital_social_raw, COUNT(*) AS n FROM companies
WHERE NULLIF(TRIM(capital_social_raw),'') IS NOT NULL
GROUP BY capital_social_raw ORDER BY n DESC LIMIT 15;

-- CNAE dudoso (9900 con CIF que no es de organismo) y CNAE sin mapeo a 2025
SELECT LEFT(cif,1) AS letra, COUNT(*) AS n FROM companies WHERE cnae_code LIKE '99%' GROUP BY 1 ORDER BY n DESC;
SELECT COUNT(*) AS cnae_sin_mapeo_2025
FROM companies c LEFT JOIN cnae_2009_2025 m ON m.cnae_2009 = c.cnae_code
WHERE NULLIF(TRIM(c.cnae_code),'') IS NOT NULL AND m.cnae_2009 IS NULL;

-- ---------------------------------------------------------------------
-- 4) Enriquecimiento de contacto (web, email, teléfono) - hoy oculto en la API
-- ---------------------------------------------------------------------
SELECT
  COUNT(*)                                                         AS filas_enrichment,
  SUM(NULLIF(TRIM(website_official),'') IS NOT NULL)               AS con_web,
  SUM(NULLIF(TRIM(email),'') IS NOT NULL)                          AS con_email,
  SUM(NULLIF(TRIM(phone_enriched),'') IS NOT NULL)                 AS con_tel_enriq,
  SUM(NULLIF(TRIM(ai_pitch),'') IS NOT NULL)                       AS con_ai_pitch,
  SUM(NULLIF(TRIM(ai_tags),'') IS NOT NULL)                        AS con_ai_tags,
  SUM(NULLIF(TRIM(ai_borme_summary),'') IS NOT NULL)               AS con_resumen_borme,
  MIN(updated_at) AS primero, MAX(updated_at) AS ultimo
FROM company_enrichment;

-- Emails genéricos (info@, contacto@...) frente a nominativos (RGPD)
SELECT
  SUM(email REGEXP '^(info|contacto|contact|admin|administracion|comercial|ventas|oficina|hola|hello|recepcion|pedidos|rrhh|facturacion)@') AS genericos,
  SUM(email NOT REGEXP '^(info|contacto|contact|admin|administracion|comercial|ventas|oficina|hola|hello|recepcion|pedidos|rrhh|facturacion)@') AS otros
FROM company_enrichment WHERE NULLIF(TRIM(email),'') IS NOT NULL;

-- ---------------------------------------------------------------------
-- 5) Cobertura de las fuentes que ya tenemos
-- ---------------------------------------------------------------------
-- Administradores
SELECT COUNT(*) AS filas, COUNT(DISTINCT company_id) AS empresas_con_admin,
       COUNT(DISTINCT name) AS personas_distintas FROM company_administrators;
SELECT action, COUNT(*) AS n FROM company_administrators GROUP BY action ORDER BY n DESC LIMIT 15;
SELECT position, COUNT(*) AS n FROM company_administrators GROUP BY position ORDER BY n DESC LIMIT 20;

-- BORME
SELECT COUNT(*) AS actos, COUNT(DISTINCT company_id) AS empresas, MIN(borme_date) AS desde, MAX(borme_date) AS hasta
FROM borme_posts;
SELECT YEAR(borme_date) AS anio, COUNT(*) AS actos FROM borme_posts GROUP BY 1 ORDER BY 1 DESC LIMIT 12;

-- Contratos públicos (API Business) y subvenciones (NO expuestas en la API)
SELECT COUNT(*) AS contratos, COUNT(DISTINCT company_cif) AS empresas,
       MIN(fecha_adjudicacion) AS desde, MAX(fecha_adjudicacion) AS hasta,
       ROUND(SUM(importe_adjudicacion)/1e6) AS millones_eur
FROM company_contracts;
SELECT COUNT(*) AS subvenciones, COUNT(DISTINCT company_cif) AS empresas,
       MIN(fecha_concesion) AS desde, MAX(fecha_concesion) AS hasta
FROM company_subsidies;
-- Columnas de subvenciones (para diseñar el endpoint)
SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('company_subsidies','company_contracts')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- Perfiles de riesgo e histórico
SELECT COUNT(*) AS perfiles, MIN(updated_at) AS mas_antiguo, MAX(updated_at) AS mas_reciente FROM company_risk_profiles;
SELECT risk_level, COUNT(*) AS n FROM company_risk_profiles GROUP BY risk_level ORDER BY n DESC;
SELECT COUNT(*) AS filas_historico, COUNT(DISTINCT cif) AS empresas FROM company_risk_profiles_history;

-- Grupos, scoring radar, embeddings (lookalike)
SELECT COUNT(*) AS holdings, SUM(companies_count) AS empresas_en_holdings FROM holdings;
SELECT COUNT(*) AS radar_scores, SUM(priority_level IS NOT NULL) AS con_prioridad FROM company_radar_scores;
SELECT COUNT(*) AS embeddings FROM company_embeddings;

-- ---------------------------------------------------------------------
-- 6) Demanda real en la API (últimos 90 días)
-- ---------------------------------------------------------------------
-- Llamadas por endpoint, plan y resultado
SELECT r.endpoint, COALESCE(p.slug,'sin_plan') AS plan, r.status_code, COUNT(*) AS llamadas,
       COUNT(DISTINCT r.user_id) AS usuarios
FROM api_requests r
LEFT JOIN user_subscriptions s ON s.id = r.subscription_id
LEFT JOIN api_plans p ON p.id = s.plan_id
WHERE r.created_at >= NOW() - INTERVAL 90 DAY AND r.user_id <> 376
GROUP BY 1,2,3 ORDER BY llamadas DESC LIMIT 80;

-- Funciones de pago que la gente pide sin tenerlas (403 = demanda de upgrade)
SELECT endpoint, COUNT(*) AS rechazos_403, COUNT(DISTINCT user_id) AS usuarios
FROM api_requests
WHERE status_code = 403 AND created_at >= NOW() - INTERVAL 90 DAY
GROUP BY endpoint ORDER BY usuarios DESC;

-- CIF que nos piden y no tenemos (404 en /companies): hueco de datos
SELECT COUNT(*) AS peticiones_404, COUNT(DISTINCT search_term) AS cif_distintos
FROM api_requests
WHERE status_code = 404 AND endpoint LIKE '%companies%' AND created_at >= NOW() - INTERVAL 90 DAY;
SELECT LEFT(search_term,1) AS letra, COUNT(DISTINCT search_term) AS cif_distintos
FROM api_requests
WHERE status_code = 404 AND endpoint LIKE '%companies%' AND created_at >= NOW() - INTERVAL 90 DAY
GROUP BY 1 ORDER BY 2 DESC;

-- De las empresas que consultan los clientes: ¿cuántas tienen contacto, riesgo, contratos...?
WITH consultadas AS (
  SELECT DISTINCT search_term AS cif FROM api_requests
  WHERE status_code = 200 AND endpoint LIKE '%companies%' AND search_term REGEXP '^[A-Z][0-9]{7}[0-9A-J]$'
    AND created_at >= NOW() - INTERVAL 90 DAY AND user_id <> 376
)
SELECT COUNT(*) AS cif_consultados,
  SUM(e.website_official IS NOT NULL AND e.website_official <> '') AS con_web,
  SUM(c.phone IS NOT NULL AND c.phone <> '')                        AS con_tel,
  SUM(NULLIF(TRIM(c.ventas_raw),'') IS NOT NULL)                    AS con_ventas,
  SUM(rp.cif IS NOT NULL)                                           AS con_perfil_riesgo,
  SUM(EXISTS(SELECT 1 FROM company_contracts cc WHERE cc.company_cif = q.cif)) AS con_contratos,
  SUM(EXISTS(SELECT 1 FROM company_subsidies cs WHERE cs.company_cif = q.cif)) AS con_subvenciones
FROM consultadas q
JOIN companies c ON c.cif = q.cif
LEFT JOIN company_enrichment e ON e.company_id = c.id
LEFT JOIN company_risk_profiles rp ON rp.cif = q.cif;

-- Clientes que consultan muchas empresas distintas (candidatos a vigilancia/cartera)
SELECT r.user_id, COALESCE(p.slug,'free') AS plan, COUNT(DISTINCT r.search_term) AS cif_distintos,
       COUNT(*) AS llamadas, MAX(r.created_at) AS ultima
FROM api_requests r
LEFT JOIN user_subscriptions s ON s.id = r.subscription_id
LEFT JOIN api_plans p ON p.id = s.plan_id
WHERE r.status_code = 200 AND r.created_at >= NOW() - INTERVAL 90 DAY AND r.user_id <> 376
GROUP BY r.user_id, plan HAVING cif_distintos >= 20
ORDER BY cif_distintos DESC LIMIT 40;

-- ¿Se repiten los mismos CIF cada mes? (señal de que hacen "vigilancia a mano" consultando otra vez)
SELECT user_id, search_term AS cif, COUNT(DISTINCT DATE_FORMAT(created_at,'%Y-%m')) AS meses_distintos
FROM api_requests
WHERE status_code = 200 AND endpoint LIKE '%companies%' AND created_at >= NOW() - INTERVAL 180 DAY AND user_id <> 376
GROUP BY user_id, search_term HAVING meses_distintos >= 3
ORDER BY meses_distintos DESC LIMIT 50;

-- User agents: integración real (servidor) frente a panel/navegador
SELECT CASE WHEN user_agent LIKE 'Mozilla%' THEN 'navegador'
            WHEN user_agent LIKE 'apiempresas-%' THEN 'SDK'
            WHEN user_agent LIKE '%python%' THEN 'python'
            WHEN user_agent LIKE '%curl%' THEN 'curl'
            WHEN user_agent LIKE '%PostmanRuntime%' THEN 'postman'
            WHEN user_agent LIKE '%Zapier%' OR user_agent LIKE '%make.com%' OR user_agent LIKE '%n8n%' THEN 'no-code'
            ELSE 'otro servidor' END AS cliente,
       COUNT(*) AS llamadas, COUNT(DISTINCT user_id) AS usuarios
FROM api_requests WHERE created_at >= NOW() - INTERVAL 90 DAY AND user_id <> 376
GROUP BY 1 ORDER BY llamadas DESC;

-- Uso de vigilancias y webhooks
SELECT COUNT(*) AS vigilancias, COUNT(DISTINCT user_id) AS usuarios, SUM(active=1) AS activas FROM user_company_watch;
SELECT COUNT(*) AS webhooks_registrados, COUNT(DISTINCT user_id) AS usuarios FROM api_webhooks;
