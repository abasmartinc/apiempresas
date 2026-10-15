-- Textos de la ficha hechos con plantilla en código, sin IA (15-10-2026).
-- Opcional pero recomendado: marca el origen del texto para poder medir plantilla frente a IA.
-- En MySQL 8 / MariaDB 10.3+ añadir una columna al final es instantáneo.

ALTER TABLE company_enrichment ADD COLUMN ai_origen VARCHAR(10) NULL DEFAULT NULL;
-- NULL = IA (o anterior). 'plantilla' = seo:plantilla-lote.

-- Cuántas hay de cada
SELECT COALESCE(ai_origen, 'ia') AS origen, COUNT(*) AS fichas
FROM company_enrichment
WHERE ai_seo_text IS NOT NULL AND ai_seo_text <> ''
GROUP BY COALESCE(ai_origen, 'ia');

-- Prueba A/B (seo:plantilla-lote --mitad): fichas generadas desde el día de arranque,
-- plantilla (crc32(cif) par) frente a IA (impar). Cruzar después con las exportaciones de Search Console.
SELECT CASE WHEN e.ai_origen = 'plantilla' THEN 'plantilla' ELSE 'ia' END AS grupo, COUNT(*) AS fichas
FROM company_enrichment e
WHERE e.updated_at >= '2026-10-16' AND e.ai_seo_text IS NOT NULL
GROUP BY grupo;
