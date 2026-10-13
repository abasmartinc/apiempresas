<!doctype html>
<html lang="es">

<head>
    <?php // Fuentes de datos (13-10-2026). Contenido en esta vista; ver Controllers/FuentesDatos.php ?>
    <?= view('partials/head', [
        'title'       => 'Fuentes de datos de la API de empresas: BORME, contratos y registros | APIEmpresas',
        'excerptText' => 'De dónde salen los datos de APIEmpresas: BORME, Plataforma de Contratación, TED, BDNS, GLEIF, VIES, registros autonómicos y CartoCiudad. Qué aporta cada fuente, cada cuánto se actualiza y su licencia.',
        'canonical'   => site_url('fuentes-de-datos'),
    ]) ?>
</head>

<body>

    <?= view('partials/header') ?>

<?php
$fechaTxt = date('d/m/Y', strtotime($fecha ?? '2026-10-13'));

// Fuente, organismo, qué aporta, actualización, dónde se usa, licencia y cita.
$fuentes = [
    [
        'id'     => 'borme',
        'nombre' => 'BORME (Boletín Oficial del Registro Mercantil)',
        'quien'  => 'Agencia Estatal Boletín Oficial del Estado',
        'url'    => 'https://www.boe.es/diario_borme/',
        'aporta' => 'Constituciones, nombramientos y ceses de administradores, cambios de domicilio, objeto social o capital, fusiones, disoluciones, concursos y extinciones. Es la base de la ficha, del estado registral y de las señales.',
        'cuando' => 'Cada día hábil, con cada boletín',
        'uso'    => 'API y fichas',
        'lic'    => 'Reutilización conforme a la Ley 37/2007 y al aviso legal del BOE.',
    ],
    [
        'id'     => 'placsp',
        'nombre' => 'Plataforma de Contratación del Sector Público',
        'quien'  => 'Ministerio de Hacienda',
        'url'    => 'https://contrataciondelestado.es/',
        'aporta' => 'Contratos adjudicados a cada empresa (órgano de contratación, importes, fechas y lotes) y datos de contacto de los organismos que contratan.',
        'cuando' => 'Cada día, y archivos anuales para el histórico',
        'uso'    => 'API (/companies/contracts) y fichas',
        'lic'    => 'Reutilización conforme a la Ley 37/2007, citando la fuente.',
    ],
    [
        'id'     => 'ted',
        'nombre' => 'TED (Tenders Electronic Daily)',
        'quien'  => 'Oficina de Publicaciones de la Unión Europea',
        'url'    => 'https://ted.europa.eu/',
        'aporta' => 'Contratos públicos publicados en el Diario Oficial de la UE, el tamaño de la empresa adjudicataria (solo marcamos "gran empresa" cuando varios anuncios coinciden) y el contacto de los organismos.',
        'cuando' => 'Cargas periódicas',
        'uso'    => 'API (/companies/contracts) y fichas',
        'lic'    => 'Reutilización según la Decisión 2011/833/UE. © Unión Europea.',
    ],
    [
        'id'     => 'bdns',
        'nombre' => 'BDNS (Base de Datos Nacional de Subvenciones)',
        'quien'  => 'Intervención General de la Administración del Estado (IGAE)',
        'url'    => 'https://www.infosubvenciones.es/',
        'aporta' => 'Subvenciones y ayudas concedidas a sociedades (nunca a personas físicas), con organismo e importe. Las convocatorias del Kit Digital nos dan el tramo de empleados que acredita la propia empresa.',
        'cuando' => 'Cada día',
        'uso'    => 'Fichas y perfil de riesgo',
        'lic'    => 'Reutilización conforme a la Ley 37/2007, citando la fuente.',
    ],
    [
        'id'     => 'gleif',
        'nombre' => 'GLEIF (Global Legal Entity Identifier Foundation)',
        'quien'  => 'GLEIF',
        'url'    => 'https://www.gleif.org/',
        'aporta' => 'Código LEI, domicilio legal y relaciones entre sociedades de un mismo grupo.',
        'cuando' => 'Cargas periódicas',
        'uso'    => 'Fichas, grupos y dirección',
        'lic'    => 'CC0 (dominio público).',
    ],
    [
        'id'     => 'vies',
        'nombre' => 'VIES (VAT Information Exchange System)',
        'quien'  => 'Comisión Europea',
        'url'    => 'https://ec.europa.eu/taxation_customs/vies/',
        'aporta' => 'Si el NIF está dado de alta para operaciones intracomunitarias (NIF-IVA).',
        'cuando' => 'En el momento de la consulta (se guarda 24 horas)',
        'uso'    => 'API (/companies/verify)',
        'lic'    => 'Servicio público de consulta de la Comisión Europea.',
    ],
    [
        'id'     => 'autonomicos',
        'nombre' => 'Registros autonómicos de asociaciones, cooperativas y fundaciones',
        'quien'  => 'Generalitat de Catalunya, Gobierno Vasco, Junta de Castilla y León, Gobierno de Canarias y Gobierno de Navarra',
        'url'    => null,
        'aporta' => 'Dirección, web, fecha de inscripción y bajas de entidades sin ánimo de lucro y cooperativas que no publican en el BORME. Solo se cruzan por CIF y solo completan huecos.',
        'cuando' => 'Cargas puntuales',
        'uso'    => 'Fichas y API',
        'lic'    => 'Fuente: Open Data Euskadi (CC BY 4.0); Junta de Castilla y León (CC BY 4.0); datos abiertos de la Generalitat de Catalunya, del Gobierno de Canarias y del Gobierno de Navarra, según sus condiciones de reutilización.',
    ],
    [
        'id'     => 'cartociudad',
        'nombre' => 'CartoCiudad',
        'quien'  => 'Instituto Geográfico Nacional (IGN-CNIG)',
        'url'    => 'https://www.cartociudad.es/',
        'aporta' => 'Coordenadas a partir de la dirección. Solo se aceptan si la calle y el código postal coinciden y el portal es el mismo o está muy cerca.',
        'cuando' => 'Al completar direcciones',
        'uso'    => 'API (lat, lng) y mapas',
        'lic'    => 'CC BY 4.0. © Instituto Geográfico Nacional.',
    ],
    [
        'id'     => 'web',
        'nombre' => 'Web de la propia empresa',
        'quien'  => 'Cada empresa (localizada también con el índice público de Common Crawl)',
        'url'    => null,
        'aporta' => 'Web, teléfono de centralita y email genérico (info@, administracion@…). Solo si la web publica el CIF de la empresa, como obliga la LSSI en el aviso legal. Nunca emails ni móviles de personas.',
        'cuando' => 'Cargas periódicas',
        'uso'    => 'Fichas',
        'lic'    => 'Datos que la propia empresa publica por obligación legal (art. 10 de la LSSI).',
    ],
];
?>

    <main class="fuentes-page">
        <section class="fp-hero container">
            <span class="fp-kicker">Transparencia</span>
            <h1>Fuentes de datos de la API de empresas</h1>
            <p class="fp-lead">Los datos de APIEmpresas salen sobre todo de publicaciones oficiales: el BORME, los contratos públicos, las subvenciones y otros registros. Aquí tienes cada fuente, qué aporta, cada cuánto se actualiza, dónde se usa y su licencia.</p>
            <p class="fp-fecha">Lista revisada el <?= esc($fechaTxt) ?>.</p>
        </section>

        <section class="container fp-reglas">
            <h2>Cómo usamos las fuentes</h2>
            <ul>
                <li><strong>El cruce se hace por CIF</strong>, comprobando el dígito de control. En contratos y direcciones, además, el nombre tiene que casar con la ficha.</li>
                <li><strong>Las fuentes secundarias solo rellenan huecos.</strong> No sustituyen a lo que publica el BORME.</li>
                <li><strong>Solo sociedades y entidades.</strong> No guardamos datos de autónomos ni contactos personales; de las personas, solo los cargos que publica el BORME.</li>
                <li><strong>Origen de cada carga.</strong> Las cargas actuales guardan de qué fuente sale cada dato, para poder revisarlo o deshacerlo.</li>
            </ul>
        </section>

        <section class="container fp-lista" aria-label="Fuentes">
            <h2>Fuentes oficiales y abiertas</h2>
            <div class="fp-grid">
                <?php foreach ($fuentes as $f): ?>
                    <article class="fp-card" id="<?= esc($f['id']) ?>">
                        <h3><?= esc($f['nombre']) ?></h3>
                        <p class="fp-quien">
                            <?php if (!empty($f['url'])): ?>
                                <a href="<?= esc($f['url']) ?>" target="_blank" rel="noopener nofollow"><?= esc($f['quien']) ?> ↗</a>
                            <?php else: ?>
                                <?= esc($f['quien']) ?>
                            <?php endif; ?>
                        </p>
                        <p><?= esc($f['aporta']) ?></p>
                        <dl>
                            <div><dt>Actualización</dt><dd><?= esc($f['cuando']) ?></dd></div>
                            <div><dt>Dónde se usa</dt><dd><?= esc($f['uso']) ?></dd></div>
                            <div><dt>Licencia y cita</dt><dd><?= esc($f['lic']) ?></dd></div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container fp-dos">
            <div>
                <h2>Otras fuentes públicas</h2>
                <p>Algunos datos de dirección, fecha de constitución, capital, contacto y tramo de facturación se completan con otras fuentes públicas en internet. Solo rellenan huecos y no sustituyen a lo que publica el BORME.</p>
            </div>
            <div>
                <h2>Datos calculados por APIEmpresas</h2>
                <p>El estado registral, la puntuación comercial, el perfil de riesgo y el tramo de tamaño se calculan a partir de esas fuentes. Cuando una empresa no tiene CNAE publicado, puede llevar uno clasificado automáticamente a partir de su objeto social. <strong>No son un certificado del Registro Mercantil.</strong></p>
            </div>
        </section>

        <section class="container fp-final">
            <h2>¿Ves un dato mal o quieres que quitemos una ficha?</h2>
            <p>Escríbenos y lo revisamos. Si una empresa nos pide no aparecer, retiramos su ficha pública.</p>
            <div class="fp-btns">
                <a href="<?= site_url('contact') ?>" class="btn-ae btn-ae-primary">Contactar</a>
                <a href="<?= site_url('documentation') ?>" class="fp-link">Ver la documentación de la API →</a>
            </div>
        </section>
    </main>

    <style>
        .fuentes-page { padding: 48px 0 80px; background: #f8fafc; }
        .fuentes-page .container { max-width: 1100px; }
        .fuentes-page > section { background: transparent; padding-top: 0; padding-bottom: 0; box-shadow: none; border: 0; }
        .fuentes-page h2::after, .fuentes-page h1::after { display: none !important; }
        .fuentes-page h1 { font-size: clamp(1.9rem, 4vw, 2.6rem); font-weight: 900; color: #0f172a; letter-spacing: -0.03em; line-height: 1.15; margin: 10px 0 14px; }
        .fuentes-page h2 { font-size: 1.35rem; font-weight: 850; color: #0f172a; margin: 0 0 14px; }
        .fp-kicker { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
        .fp-lead { color: #475569; font-size: 1.08rem; line-height: 1.6; max-width: 780px; margin: 0; }
        .fp-fecha { color: #94a3b8; font-size: 0.85rem; margin: 10px 0 0; }
        .fp-reglas, .fp-lista, .fp-dos, .fp-final { margin-top: 40px; }
        .fp-reglas ul { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .fp-reglas li { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 18px; color: #475569; font-size: 0.95rem; line-height: 1.5; }
        .fp-reglas strong { color: #0f172a; }
        .fp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .fp-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; scroll-margin-top: 90px; }
        .fp-card h3 { font-size: 1.05rem; font-weight: 850; color: #0f172a; margin: 0 0 4px; line-height: 1.3; }
        .fp-card .fp-quien { font-size: 0.85rem; color: #64748b; margin: 0 0 12px; }
        .fp-card .fp-quien a { color: #2563eb; text-decoration: none; font-weight: 700; }
        .fp-card > p { color: #475569; font-size: 0.93rem; line-height: 1.55; margin: 0 0 14px; }
        .fp-card dl { margin: auto 0 0; padding-top: 12px; border-top: 1px solid #f1f5f9; display: grid; gap: 8px; }
        .fp-card dt { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
        .fp-card dd { margin: 2px 0 0; font-size: 0.86rem; color: #334155; line-height: 1.45; }
        .fp-dos { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .fp-dos > div, .fp-final { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; }
        .fp-dos p, .fp-final p { color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0; }
        .fp-btns { display: flex; flex-wrap: wrap; align-items: center; gap: 14px 24px; margin-top: 18px; }
        .fp-link { color: #2563eb; font-weight: 800; text-decoration: none; }
        @media (max-width: 960px) { .fp-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) {
            .fuentes-page { padding-top: 28px; }
            .fp-grid, .fp-reglas ul, .fp-dos { grid-template-columns: 1fr; }
            .fp-card { padding: 18px; }
        }
    </style>

    <?= view('partials/footer') ?>
</body>

</html>
