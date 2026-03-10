<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar B2B - Centro de Prospección Inteligente',
        'excerptText' => 'Identifica nuevas oportunidades de negocio en tiempo real con el Radar de APIEmpresas.',
    ]) ?>

    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=5') ?>">
</head>
<body>

<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr)) return 'Reciente';
    $timestamp = strtotime($dateStr);
    if (!$timestamp) return 'Reciente';

    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    return str_replace($mesesEn, $mesesEs, date($format, $timestamp));
};

$isLockedView = true;
$visibleCompanies = array_slice($companies, 0, 10);

$lockedRows = [
    ['Empresa bloqueada · Acceso PRO', '02 Mar 2026', 'Madrid', 'Actividad disponible en Radar PRO'],
    ['Listado premium oculto', '02 Mar 2026', 'Barcelona', 'Segmentación completa disponible'],
    ['Registro bloqueado para usuarios Free', '01 Mar 2026', 'Valencia', 'Desbloquea exportación y detalle'],
    ['Empresa visible solo en PRO', '01 Mar 2026', 'Sevilla', 'Prospección B2B avanzada'],
    ['Lead protegido por suscripción', '28 Feb 2026', 'Málaga', 'Actividad principal bloqueada'],
    ['Nueva constitución bloqueada', '28 Feb 2026', 'A Coruña', 'Disponible con acceso completo'],
    ['Oportunidad premium bloqueada', '27 Feb 2026', 'Bilbao', 'Filtrado por CNAE solo en PRO'],
    ['Empresa detectada en tiempo real', '27 Feb 2026', 'Zaragoza', 'Acceso avanzado requerido'],
    ['Registro oculto por plan Free', '26 Feb 2026', 'Murcia', 'Exportación Excel incluida en PRO'],
    ['Lead empresarial restringido', '26 Feb 2026', 'Alicante', 'Disponible con suscripción activa'],
    ['Vista previa limitada', '25 Feb 2026', 'Granada', 'El detalle completo está bloqueado'],
    ['Empresa incluida en Radar PRO', '25 Feb 2026', 'Valladolid', 'Activa el acceso completo'],
];
?>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">

        <aside class="ae-radar-page__sidebar">
            <div class="ae-radar-page__brand">
                <a href="<?=site_url() ?>" class="ae-radar-page__brand-header">
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
                    <div class="brand-text">
                        <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                        <span class="brand-tag">Verificación empresarial</span>
                    </div>
                </a>

                <div class="ae-radar-page__brand-note">
                    <span class="ae-radar-page__pulse"></span>
                    Inteligencia comercial en tiempo real
                </div>
            </div>

            <div class="ae-radar-page__sidebar-body">
                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Radar</span>

                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__nav-link is-active">
                        <span class="ae-radar-page__nav-icon">📊</span>
                        Dashboard principal
                    </a>
                </div>

                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Sectores destacados</span>

                    <?php foreach (array_slice($topSectors, 0, 10) as $s): ?>
                        <?php if ($isLockedView): ?>
                            <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__nav-link ae-radar-page__nav-link--locked">
                                <span class="ae-radar-page__nav-icon ae-radar-page__nav-muted">🔒</span>
                                <?= esc(mb_strimwidth($s['label'], 0, 28, '...')) ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('radar?cnae=' . $s['code']) ?>" class="ae-radar-page__nav-link <?= ($filters['cnae'] === $s['code']) ? 'is-active' : '' ?>">
                                <span class="ae-radar-page__nav-icon ae-radar-page__nav-muted">#</span>
                                <?= esc(mb_strimwidth($s['label'], 0, 28, '...')) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ae-radar-page__sidebar-footer">
                <a href="<?= site_url('dashboard') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🏠</span>
                    Volver al portal
                </a>

                <a href="<?= site_url('logout') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🚪</span>
                    Cerrar sesión
                </a>
            </div>
        </aside>

        <main class="ae-radar-page__main">
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>APIEmpresas</span>
                    <span>/</span>
                    <strong>Radar B2B</strong>
                </div>

                <div class="ae-radar-page__topbar-actions">
                    <?php if ($isLockedView): ?>
                        <div class="ae-radar-page__pill ae-radar-page__pill--free">
                            Plan Free · Vista limitada
                        </div>

                        <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__cta-top">
                            Activar Radar PRO
                        </a>
                    <?php else: ?>
                        <div class="ae-radar-page__pill ae-radar-page__pill--live">
                            <span class="ae-radar-page__pulse"></span>
                            Suscripción activa
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">

                    <section class="ae-radar-page__hero">
                        <div class="ae-radar-page__hero-grid">
                            <div>
                                <div class="ae-radar-page__eyebrow">
                                    <span class="ae-radar-page__pulse"></span>
                                    Nuevas constituciones · captación B2B
                                </div>

                                <h1 class="ae-radar-page__hero-title">
                                    Radar de constituciones para detectar clientes antes que tu competencia
                                </h1>

                                <p class="ae-radar-page__hero-text">
                                    Descubre nuevas empresas registradas en España y convierte la información societaria en oportunidades comerciales reales. Con Radar PRO desbloqueas el acceso completo al listado, filtros avanzados y exportaciones listas para tu equipo.
                                </p>

                                <div class="ae-radar-page__hero-actions">
                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--primary">
                                        Desbloquear Radar PRO
                                    </a>

                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--secondary">
                                        Ver planes y acceso completo
                                    </a>
                                </div>
                            </div>

                            <div class="ae-radar-page__hero-aside">
                                <div class="ae-radar-page__hero-aside-title">Qué desbloqueas con PRO</div>

                                <ul class="ae-radar-page__hero-list">
                                    <li>
                                        <span class="ae-radar-page__hero-dot"></span>
                                        <span>Acceso completo al listado de empresas registradas en el periodo seleccionado.</span>
                                    </li>
                                    <li>
                                        <span class="ae-radar-page__hero-dot"></span>
                                        <span>Filtros estratégicos para encontrar oportunidades por zona y sector.</span>
                                    </li>
                                    <li>
                                        <span class="ae-radar-page__hero-dot"></span>
                                        <span>Exportación a Excel para campañas comerciales, CRM y prospección.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="ae-radar-page__metrics">
                        <article class="ae-radar-page__metric">
                            <div class="ae-radar-page__metric-label">
                                Registradas hoy
                                <span class="ae-radar-page__pulse"></span>
                            </div>
                            <p class="ae-radar-page__metric-value"><?= number_format($stats['hoy']) ?></p>
                            <div class="ae-radar-page__metric-help">Constituciones detectadas en tiempo real.</div>
                        </article>

                        <article class="ae-radar-page__metric">
                            <div class="ae-radar-page__metric-label">Últimos 7 días</div>
                            <p class="ae-radar-page__metric-value"><?= number_format($stats['semana']) ?></p>
                            <div class="ae-radar-page__metric-help">Volumen reciente de nuevas oportunidades.</div>
                        </article>

                        <article class="ae-radar-page__metric">
                            <div class="ae-radar-page__metric-label">Este mes</div>
                            <p class="ae-radar-page__metric-value"><?= number_format($stats['mes']) ?></p>
                            <div class="ae-radar-page__metric-help">Empresas potencialmente captables este mes.</div>
                        </article>

                        <article class="ae-radar-page__metric ae-radar-page__metric--highlight">
                            <div class="ae-radar-page__metric-label">Estado del acceso</div>
                            <p class="ae-radar-page__metric-value"><?= $isLockedView ? 'Free' : 'PRO' ?></p>
                            <div class="ae-radar-page__metric-help">
                                <?= $isLockedView ? 'Activa tu plan para ver el 100% del radar.' : 'Acceso completo habilitado.' ?>
                            </div>
                        </article>
                    </section>

                    <form action="<?= site_url('radar') ?>" method="GET" class="ae-radar-page__filters <?= $isLockedView ? 'is-locked' : '' ?>">
                        <div class="ae-radar-page__filters-head">
                            <div>
                                <h2 class="ae-radar-page__filters-title">Filtrar oportunidades</h2>
                                <div class="ae-radar-page__filters-sub">
                                    <?= $isLockedView
                                        ? 'Los filtros avanzados se activan con Radar PRO.'
                                        : 'Explora por ubicación, actividad y ventana temporal.' ?>
                                </div>
                            </div>

                            <?php if ($isLockedView): ?>
                                <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__mini-chip ae-radar-page__mini-chip--locked">
                                    🔒 Filtros avanzados solo en PRO
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="ae-radar-page__filters-grid">
                            <div class="ae-radar-page__field">
                                <label>Provincia</label>
                                <select name="provincia" class="ae-radar-page__select" <?= $isLockedView ? 'disabled' : '' ?>>
                                    <option value="">Toda España</option>
                                    <?php foreach ($provinces as $p): ?>
                                        <option value="<?= url_title($p['name'], '-', true) ?>" <?= ($filters['provincia'] === url_title($p['name'], '-', true)) ? 'selected' : '' ?>>
                                            <?= esc($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <label>Sector de actividad</label>
                                <select name="cnae" class="ae-radar-page__select" <?= $isLockedView ? 'disabled' : '' ?>>
                                    <option value="">Cualquier actividad</option>
                                    <?php foreach ($topSectors as $s): ?>
                                        <option value="<?= esc($s['code']) ?>" <?= ($filters['cnae'] === $s['code']) ? 'selected' : '' ?>>
                                            <?= esc($s['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <label>Ventana temporal</label>
                                <select name="rango" class="ae-radar-page__select" <?= $isLockedView ? 'disabled' : '' ?>>
                                    <option value="7" <?= ($filters['rango'] === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                                    <option value="30" <?= ($filters['rango'] === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                                    <option value="90" <?= ($filters['rango'] === '90') ? 'selected' : '' ?>>Últimos 90 días</option>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <label>&nbsp;</label>
                                <?php if ($isLockedView): ?>
                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__filters-cta ae-radar-page__filters-cta--locked">
                                        Activar PRO para filtrar
                                    </a>
                                <?php else: ?>
                                    <button type="submit" class="ae-radar-page__filters-cta">
                                        Aplicar filtros
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <section class="ae-radar-page__lead-wrap <?= $isLockedView ? 'is-paywalled' : '' ?>">
                        <div class="ae-radar-page__lead-top">
                            <div class="ae-radar-page__lead-headings">
                                <h2 class="ae-radar-page__lead-title">Oportunidades detectadas</h2>
                                <div class="ae-radar-page__lead-desc">
                                    <?= $isLockedView
                                        ? 'Has visto una muestra del radar. Desbloquea el resto de empresas registradas para acceder al listado completo.'
                                        : 'Listado completo disponible para análisis y exportación.' ?>
                                </div>
                            </div>

                            <div class="ae-radar-page__lead-actions">
                                <?php if ($isLockedView): ?>
                                    <div class="ae-radar-page__mini-chip">Exportación XLSX incluida en PRO</div>
                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__export-btn">Activar acceso completo</a>
                                <?php else: ?>
                                    <a href="<?= site_url('billing/export-excel?' . http_build_query($filters)) ?>" class="ae-radar-page__export-btn">
                                        Exportar XLSX
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ae-radar-page__table-scroll">
                            <table class="ae-radar-page__table">
                                <thead>
                                    <tr>
                                        <th>Razón social</th>
                                        <th>Fecha</th>
                                        <th>Provincia</th>
                                        <th>Actividad principal</th>
                                        <th style="text-align:right;">Acceso</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($visibleCompanies as $co): ?>
                                        <tr class="ae-radar-page__row-visible">
                                            <td>
                                                <div class="ae-radar-page__company">
                                                    <span class="ae-radar-page__company-name"><?= esc($co['company_name']) ?></span>
                                                    <span class="ae-radar-page__company-cif"><?= esc($co['cif']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ae-radar-page__date"><?= $formatEsDate($co['fecha_constitucion']) ?></span>
                                            </td>
                                            <td>
                                                <span class="ae-radar-page__badge ae-radar-page__badge--province">
                                                    <?= esc($co['registro_mercantil']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="ae-radar-page__badge ae-radar-page__badge--sector">
                                                    <?= esc(mb_strimwidth($co['cnae_label'], 0, 48, '...')) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right;">
                                                <a href="<?= $isLockedView ? site_url('precios-radar') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-radar-page__row-link">
                                                    <?= $isLockedView ? '🔒' : '→' ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($isLockedView): ?>
                            <div class="ae-radar-page__locked-zone">
                                <div class="ae-radar-page__locked-zone-table">
                                    <div class="ae-radar-page__table-scroll ae-radar-page__table-scroll--locked">
                                        <table class="ae-radar-page__table">
                                            <tbody>
                                                <?php foreach ($lockedRows as $row): ?>
                                                    <tr class="ae-radar-page__row-locked">
                                                        <td>
                                                            <div class="ae-radar-page__company">
                                                                <span class="ae-radar-page__company-name"><?= esc($row[0]) ?></span>
                                                                <span class="ae-radar-page__company-cif">B12345678</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__date"><?= esc($row[1]) ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__badge ae-radar-page__badge--province">
                                                                <?= esc($row[2]) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__badge ae-radar-page__badge--sector">
                                                                <?= esc($row[3]) ?>
                                                            </span>
                                                        </td>
                                                        <td style="text-align:right;">
                                                            <span class="ae-radar-page__row-link">🔒</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="ae-radar-page__paywall">
                                    <div class="ae-radar-page__paywall-card">
                                        <div class="ae-radar-page__paywall-band"></div>

                                        <div class="ae-radar-page__paywall-inner">
                                            <div class="ae-radar-page__paywall-icon">🚀</div>

                                            <h3 class="ae-radar-page__paywall-title">
                                                Desbloquea el resto del radar y empieza a prospectar con ventaja
                                            </h3>

                                            <p class="ae-radar-page__paywall-text">
                                                Ya has visto una muestra real del radar. Activa Radar PRO para consultar todas las empresas detectadas, aplicar filtros estratégicos y exportar leads a Excel para tu proceso comercial.
                                            </p>

                                            <div class="ae-radar-page__paywall-kpis">
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong><?= number_format($stats['mes']) ?></strong>
                                                    <span>Empresas este mes</span>
                                                </div>
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong>100%</strong>
                                                    <span>Listado desbloqueado</span>
                                                </div>
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong>XLSX</strong>
                                                    <span>Exportación incluida</span>
                                                </div>
                                            </div>

                                            <div class="ae-radar-page__paywall-benefits">
                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Listado completo</strong>
                                                        <span>Consulta todas las empresas registradas según tus criterios comerciales.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Exportación a Excel</strong>
                                                        <span>Lleva los leads a tu CRM, campañas o equipo de ventas.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Filtros estratégicos</strong>
                                                        <span>Segmenta por provincia, actividad y rango temporal.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Ventaja competitiva</strong>
                                                        <span>Llega antes que otros proveedores a empresas recién constituidas.</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ae-radar-page__paywall-actions">
                                                <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                                    Activar Radar PRO ahora
                                                </a>

                                                <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">
                                                    Ver planes y condiciones
                                                </a>
                                            </div>

                                            <div class="ae-radar-page__paywall-meta">
                                                Sin permanencia · Activación inmediata · Acceso completo al radar
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>

                    <footer class="ae-radar-page__footer">
                        &copy; <?= date('Y') ?> APIEmpresas · Inteligencia comercial para captación B2B
                    </footer>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>