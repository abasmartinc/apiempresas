-- Vigilancia por API (27-09-2026). Tabla propia, separada de user_company_watch
-- (Solvencia): un cliente de la API no recibe los correos de alertas de la web.
-- Lanzar a mano en local y en producción (aquí no se usan migraciones) y comprobar
-- después con SHOW CREATE TABLE api_watchlist: la UNIQUE (user_id, cif) es la que
-- evita duplicados al dar de alta la misma empresa dos veces.

CREATE TABLE IF NOT EXISTS `api_watchlist` (
  `id`         int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    int NOT NULL,
  `cif`        varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_watch_user_cif` (`user_id`, `cif`),
  KEY `idx_api_watch_cif` (`cif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
