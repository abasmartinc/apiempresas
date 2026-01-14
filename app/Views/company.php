<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => 'index,follow',
    ]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>

            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="<?=site_url() ?>dashboard">Dashboard</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
        </nav>

        <div class="desktop-only">
            <?php if(!session('logged_in')){ ?>
                <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">
                    <span>Iniciar sesión</span>
                </a>
            <?php } else { ?>
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout">
                    <span>Salir</span>
                </a>
            <?php } ?>
        </div>
    </div>
</header>

<main style="padding:40px 0 70px;">
    <section class="container search-section">
        <div class="search-card">
            
            <div class="result">
                <?php
                    $statusRaw = (string)($company['status'] ?? '');
                    $isActive  = strtoupper($statusRaw) === 'ACTIVA';
                    $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

                    $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
                        ? ($company['cnae'] . ' · ' . $company['cnae_label'])
                        : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

                    $jsonForCode = ['success' => true, 'data' => $company];
                    $jsonPretty  = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                ?>
                <article class="company-card">
                    <header class="company-card__header">
                        <div>
                            <div class="company-card__eyebrow">Ficha registral</div>
                            <h1 class="company-card__name" style="font-size: 1.5rem; margin: 0;"><?= esc($company['name'] ?? '-') ?></h1>
                            <div class="company-card__meta">
                                <?= esc(($company['cif'] ?? $company['nif'] ?? '-') . ' · ' . ($company['province'] ?? $company['provincia'] ?? '-')) ?>
                            </div>
                        </div>
                        <div class="<?= esc($statusClass) ?>">
                            <span class="company-status__dot"></span>
                            <span><?= esc($statusRaw ?: '-') ?></span>
                        </div>
                    </header>

                    <section class="company-card__body">
                        <dl class="company-card__grid">
                            <div><dt>CIF</dt><dd><?= esc($company['cif'] ?? $company['nif'] ?? '-') ?></dd></div>
                            <div><dt>CNAE</dt><dd><?= esc($cnaeFull ?: '-') ?></dd></div>
                            <div><dt>Provincia</dt><dd><?= esc($company['province'] ?? $company['provincia'] ?? '-') ?></dd></div>
                            <div><dt>Fecha de constitución</dt><dd><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></dd></div>
                            <div class="company-card__purpose"><dt>Objeto social</dt><dd><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></dd></div>
                        </dl>
                    </section>

                    <div class="company-card__footer">
                        <button type="button" class="btn-json-api" id="btnToggleJson">Ver JSON de la API</button>
                    </div>

                    <pre class="company-card__json is-hidden" id="jsonBlock"><code><?= esc($jsonPretty) ?></code></pre>
                </article>
            </div>
            
            <div style="margin-top: 2rem; text-align: center;">
                <a href="<?= site_url('search_company') ?>" class="minor">← Volver al buscador</a>
            </div>

        </div>
    </section>
</main>

<?= view('partials/footer') ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnToggleJson');
        const pre = document.getElementById('jsonBlock');
        
        if(btn && pre) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const nowHidden = pre.classList.toggle('is-hidden');
                btn.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
            });
        }
    });
</script>
</body>
</html>
