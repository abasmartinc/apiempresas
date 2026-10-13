<!doctype html>
<html lang="en">

<head>
    <?php // Data sources (13-10-2026), English version of fuentes_datos.php. See Controllers/FuentesDatos.php ?>
    <?= view('partials/head', [
        'title'       => 'Data sources of the Spanish company API: BORME, procurement and registries | SpainCompanyAPI',
        'excerptText' => 'Where SpainCompanyAPI data comes from: BORME, the Public Procurement Platform, TED, BDNS, GLEIF, VIES, regional registries and CartoCiudad. What each source provides, how often it is updated and its licence.',
        'canonical'   => site_url('data-sources'),
    ]) ?>
</head>

<body>

    <?= view('partials/header_en') ?>

<?php

// Fuente, organismo, qué aporta, actualización, dónde se usa, licencia y cita.
$fuentes = [
    [
        'id'     => 'borme',
        'nombre' => 'BORME (Official Gazette of the Commercial Registry)',
        'quien'  => 'Spanish Official State Gazette Agency (BOE)',
        'url'    => 'https://www.boe.es/diario_borme/',
        'aporta' => 'Incorporations, director appointments and resignations, changes of address, corporate purpose or capital, mergers, dissolutions, insolvency proceedings and strike-offs. It is the basis of each company record, its registry status and its signals.',
        'cuando' => 'Every working day, with each gazette',
        'uso'    => 'API and company pages',
        'lic'    => 'Reuse under Spanish Law 37/2007 and the BOE legal notice.',
    ],
    [
        'id'     => 'placsp',
        'nombre' => 'Spanish Public Procurement Platform (PLACSP)',
        'quien'  => 'Spanish Ministry of Finance',
        'url'    => 'https://contrataciondelestado.es/',
        'aporta' => 'Contracts awarded to each company (contracting authority, amounts, dates and lots) and contact details of the contracting authorities.',
        'cuando' => 'Every day, plus yearly files for the history',
        'uso'    => 'API (/companies/contracts) and company pages',
        'lic'    => 'Reuse under Spanish Law 37/2007, citing the source.',
    ],
    [
        'id'     => 'ted',
        'nombre' => 'TED (Tenders Electronic Daily)',
        'quien'  => 'Publications Office of the European Union',
        'url'    => 'https://ted.europa.eu/',
        'aporta' => 'Public contracts published in the Official Journal of the EU, the size of the winning company (we only flag "large company" when several notices agree) and the contact details of contracting authorities.',
        'cuando' => 'Periodic loads',
        'uso'    => 'API (/companies/contracts) and company pages',
        'lic'    => 'Reuse under Decision 2011/833/EU. © European Union.',
    ],
    [
        'id'     => 'bdns',
        'nombre' => 'BDNS (Spanish National Grants Database)',
        'quien'  => 'General Comptroller of the State Administration (IGAE)',
        'url'    => 'https://www.infosubvenciones.es/',
        'aporta' => 'Grants and aid awarded to companies (never to individuals), with the granting body and amount. The Kit Digital programme gives us the headcount band certified by the company itself.',
        'cuando' => 'Every day',
        'uso'    => 'Company pages and risk profile',
        'lic'    => 'Reuse under Spanish Law 37/2007, citing the source.',
    ],
    [
        'id'     => 'gleif',
        'nombre' => 'GLEIF (Global Legal Entity Identifier Foundation)',
        'quien'  => 'GLEIF',
        'url'    => 'https://www.gleif.org/',
        'aporta' => 'LEI code, legal address and relationships between companies of the same group.',
        'cuando' => 'Periodic loads',
        'uso'    => 'Company pages, groups and addresses',
        'lic'    => 'CC0 (public domain).',
    ],
    [
        'id'     => 'vies',
        'nombre' => 'VIES (VAT Information Exchange System)',
        'quien'  => 'European Commission',
        'url'    => 'https://ec.europa.eu/taxation_customs/vies/',
        'aporta' => 'Whether the tax ID is registered for intra-EU transactions (EU VAT number).',
        'cuando' => 'At the time of the request (cached for 24 hours)',
        'uso'    => 'API (/companies/verify)',
        'lic'    => 'Public lookup service of the European Commission.',
    ],
    [
        'id'     => 'autonomicos',
        'nombre' => 'Regional registries of associations, cooperatives and foundations',
        'quien'  => 'Generalitat de Catalunya, Basque Government, Junta de Castilla y León, Government of the Canary Islands and Government of Navarre',
        'url'    => null,
        'aporta' => 'Address, website, registration date and deregistrations of non-profits and cooperatives that do not file in the BORME. Matched by CIF only, and only used to fill gaps.',
        'cuando' => 'One-off loads',
        'uso'    => 'Company pages and API',
        'lic'    => 'Source: Open Data Euskadi (CC BY 4.0); Junta de Castilla y León (CC BY 4.0); open data of the Generalitat de Catalunya, the Government of the Canary Islands and the Government of Navarre, under their reuse terms.',
    ],
    [
        'id'     => 'cartociudad',
        'nombre' => 'CartoCiudad',
        'quien'  => 'Spanish National Geographic Institute (IGN-CNIG)',
        'url'    => 'https://www.cartociudad.es/',
        'aporta' => 'Coordinates from the address. Only accepted when the street and postcode match and the building number is the same or very close.',
        'cuando' => 'When addresses are completed',
        'uso'    => 'API (lat, lng) and maps',
        'lic'    => 'CC BY 4.0. © Instituto Geográfico Nacional.',
    ],
    [
        'id'     => 'web',
        'nombre' => "The company's own website",
        'quien'  => 'Each company (also found through the public Common Crawl index)',
        'url'    => null,
        'aporta' => 'Website, switchboard phone and generic email (info@, admin@…). Only when the website shows the company CIF, as Spanish e-commerce law (LSSI) requires in the legal notice. Never personal emails or mobile numbers.',
        'cuando' => 'Periodic loads',
        'uso'    => 'Company pages',
        'lic'    => 'Data the company itself publishes by law (art. 10 LSSI).',
    ],
];
?>

    <main class="fuentes-page">
        <section class="fp-hero container">
            <span class="fp-kicker">Transparency</span>
            <h1>Data sources of the Spanish company API</h1>
            <p class="fp-lead">SpainCompanyAPI data comes mainly from official publications: the BORME, public procurement, public grants and other registries. Here is each source, what it provides, how often it is updated, where it is used and its licence.</p>
            <p class="fp-fecha">List reviewed on <?= esc(date('j F Y', strtotime($fecha ?? '2026-10-13'))) ?>.</p>
        </section>

        <section class="container fp-reglas">
            <h2>How we use the sources</h2>
            <ul>
                <li><strong>Records are matched by CIF</strong> (Spanish tax ID), checking its control character. For contracts and addresses, the name must also match.</li>
                <li><strong>Secondary sources only fill gaps.</strong> They never replace what the BORME publishes.</li>
                <li><strong>Companies and entities only.</strong> We do not store data on sole traders or personal contacts; for individuals, only the positions the BORME publishes.</li>
                <li><strong>Provenance.</strong> Current loads record which source each value comes from, so it can be reviewed or undone.</li>
            </ul>
        </section>

        <section class="container fp-lista" aria-label="Sources">
            <h2>Official and open sources</h2>
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
                            <div><dt>Updated</dt><dd><?= esc($f['cuando']) ?></dd></div>
                            <div><dt>Used in</dt><dd><?= esc($f['uso']) ?></dd></div>
                            <div><dt>Licence and attribution</dt><dd><?= esc($f['lic']) ?></dd></div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container fp-dos">
            <div>
                <h2>Other public sources</h2>
                <p>Some address, incorporation date, share capital, contact and revenue band data is completed from other public sources on the internet. They only fill gaps and never replace what the BORME publishes.</p>
            </div>
            <div>
                <h2>Data calculated by SpainCompanyAPI</h2>
                <p>Registry status, commercial score, risk profile and size band are calculated from those sources. When a company has no published CNAE code, it may carry one assigned automatically from its corporate purpose. <strong>They are not a Commercial Registry certificate.</strong></p>
            </div>
        </section>

        <section class="container fp-final">
            <h2>Found a wrong value, or want a company page removed?</h2>
            <p>Write to us and we will check it. If a company asks not to appear, we remove its public page.</p>
            <div class="fp-btns">
                <a href="mailto:soporte@apiempresas.es" class="btn-ae btn-ae-primary">Contact us</a>
                <a href="<?= site_url('docs') ?>" class="fp-link">Read the API docs →</a>
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

    <?= view('partials/footer_en') ?>
</body>

</html>
