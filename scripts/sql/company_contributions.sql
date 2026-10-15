-- Aportaciones de datos de contacto desde la ficha ("Añadir teléfono / web / email"), 15-10-2026.
-- Ver app/Services/ContributionService.php. Una fila por dato aportado; las de un mismo envío
-- comparten `envio`. No se aplica nada a la ficha hasta que el administrador lo aprueba
-- (/admin/aportaciones) o lo aplica `php spark datos:verificar-aportaciones --aplicar`
-- (solo las comprobadas en la web de la empresa).

CREATE TABLE IF NOT EXISTS company_contributions (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id           INT UNSIGNED NOT NULL,
    envio                CHAR(32)     NOT NULL,              -- mismo valor para los datos de un mismo envío; va en el enlace de confirmación
    campo                VARCHAR(16)  NOT NULL,              -- telefono | web | correo
    valor                VARCHAR(255) NOT NULL,              -- ya normalizado (teléfono en cifras, web con https://, email en minúsculas)
    valor_escrito        VARCHAR(500) NULL,                  -- tal cual lo escribieron
    relacion             VARCHAR(16)  NOT NULL DEFAULT 'otro',  -- empresa | cliente | otro
    email_contacto       VARCHAR(190) NULL,                  -- de quien aporta (obligatorio si dice ser de la empresa)
    estado               VARCHAR(16)  NOT NULL DEFAULT 'pendiente',  -- pendiente | verificado | aprobado | rechazado
    verificacion         VARCHAR(32)  NULL,                  -- cif_en_web | en_web_empresa | email_dominio | manual
    verificacion_detalle VARCHAR(500) NULL,                  -- dónde se encontró / por qué no
    verificado_at        DATETIME     NULL,                  -- última vez que lo miró el comando
    email_confirmado_at  DATETIME     NULL,                  -- pulsó el enlace del correo
    anterior             VARCHAR(500) NULL,                  -- lo que había en la ficha al aplicarlo (para deshacer)
    revisado_at          DATETIME     NULL,
    nota_admin           VARCHAR(255) NULL,
    ip                   VARCHAR(45)  NULL,
    user_agent           VARCHAR(255) NULL,
    created_at           DATETIME     NOT NULL,
    updated_at           DATETIME     NULL,
    PRIMARY KEY (id),
    KEY idx_cc_company (company_id),
    KEY idx_cc_estado (estado, created_at),
    KEY idx_cc_ip (ip, created_at),
    KEY idx_cc_envio (envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
