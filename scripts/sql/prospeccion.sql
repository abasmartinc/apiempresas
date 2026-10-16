-- Prospección de clientes para el trabajo de freelance (/admin/prospeccion), 16-10-2026.
-- Solo tablas nuevas: no toca ninguna de las existentes. Nombres de tablas y columnas en inglés.
-- La página las crea sola la primera vez que se abre (App\Libraries\Prospeccion::asegurarTablas),
-- así que lanzar este fichero a mano es opcional (por ejemplo, en producción antes de subir).
-- Si existen las tablas de la primera versión (prospeccion_seguimiento / prospeccion_licitaciones,
-- en español), asegurarTablas las renombra con sus datos.

-- Seguimiento de empresas: qué se ha hecho con cada una. Una fila por empresa.
CREATE TABLE IF NOT EXISTS prospect_followups (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id        INT UNSIGNED NOT NULL,
    status            VARCHAR(20)  NOT NULL DEFAULT 'pending',  -- pending | contacted | in_talks | client | discarded
    note              TEXT         NULL,
    next_contact_date DATE         NULL,                        -- cuándo volver a escribir
    source_tab        VARCHAR(20)  NULL,                        -- pestaña desde la que se marcó (nuevas, sin-web, ...)
    created_at        DATETIME     NOT NULL,
    updated_at        DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pf_company (company_id),
    KEY idx_pf_status (status, updated_at),
    KEY idx_pf_next (next_contact_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Licitaciones de informática de la Plataforma de Contratación (PLACSP y plataformas agregadas).
-- Las carga `php spark prospeccion:licitaciones`. Solo se guardan las de los CPV elegidos (por
-- defecto 72, 48, 80533 y 79342). El seguimiento de cada una va en la misma fila (followup_*).
CREATE TABLE IF NOT EXISTS prospect_tenders (
    id                           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    feed_id                      VARCHAR(255)  NOT NULL,   -- <id> de la entrada del feed (estable para cada licitación)
    source                       VARCHAR(20)   NOT NULL,   -- placsp | agregadas
    file_number                  VARCHAR(255)  NULL,       -- expediente
    title                        TEXT          NULL,
    contracting_body             VARCHAR(500)  NULL,       -- órgano de contratación
    contracting_body_nif         VARCHAR(20)   NULL,
    contracting_body_postal_code VARCHAR(10)   NULL,
    contracting_body_city        VARCHAR(255)  NULL,
    province                     VARCHAR(60)   NULL,       -- uno de los 52 nombres de App\Libraries\Provincias
    execution_place              VARCHAR(255)  NULL,
    contract_type                VARCHAR(10)   NULL,       -- código CODICE (1 suministros, 2 servicios, 3 obras)
    procedure_code               VARCHAR(10)   NULL,       -- código CODICE (1 abierto, 9 abierto simplificado...)
    amount_excl_vat              DECIMAL(15,2) NULL,
    amount_incl_vat              DECIMAL(15,2) NULL,
    estimated_value              DECIMAL(15,2) NULL,
    lot_count                    SMALLINT UNSIGNED NULL,
    cpv_codes                    VARCHAR(500)  NULL,       -- todos los CPV, separados por comas
    main_cpv                     VARCHAR(12)   NULL,
    status_code                  VARCHAR(10)   NULL,       -- PUB (en plazo), EV, ADJ, RES, ANUL, PRE; DELETED = retirada del feed
    published_date               DATE          NULL,
    deadline                     DATETIME      NULL,       -- fin de presentación de ofertas
    url                          VARCHAR(500)  NULL,
    feed_updated                 DATETIME      NULL,       -- <updated> de la entrada; solo se actualiza con una más reciente
    followup_status              VARCHAR(20)   NULL,       -- interested | preparing | submitted | won | lost | discarded
    followup_note                TEXT          NULL,
    followup_updated_at          DATETIME      NULL,
    ai_score                     TINYINT UNSIGNED NULL,    -- encaje con el perfil de Adrián (0-100), calculado una vez por la IA
    ai_is_dev                    TINYINT(1)    NULL,       -- 1 = proyecto de desarrollo
    ai_summary                   VARCHAR(500)  NULL,       -- "Qué piden", en una frase
    ai_reasons                   TEXT          NULL,       -- JSON [[texto, "+"|"-"], ...]
    ai_profile_hash              CHAR(32)      NULL,       -- huella del perfil con el que se evaluó
    ai_evaluated_at              DATETIME      NULL,
    competition_json             MEDIUMTEXT    NULL,       -- competencia calculada (una vez, al abrirla por primera vez)
    competition_at               DATETIME      NULL,
    ai_proposal                  MEDIUMTEXT    NULL,       -- "Preparar oferta": de qué va, tareas, qué revisar y borrador del texto (JSON, una vez)
    ai_proposal_at               DATETIME      NULL,
    created_at                   DATETIME      NOT NULL,
    updated_at                   DATETIME      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pt_feed (feed_id),
    KEY idx_pt_open (status_code, deadline),
    KEY idx_pt_province (province, deadline),
    KEY idx_pt_followup (followup_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
