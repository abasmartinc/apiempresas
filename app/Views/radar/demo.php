<?php
/**
 * /radar-demo — Demo Comercial de Alta Conversión
 */
$strategiesJson = json_encode(array_map(function ($co) {
    return [
        'id'         => $co['id'],
        'name'       => $co['company_name'],
        'sector'     => $co['cnae_label'] ?? '',
        'location'   => $co['municipality'] ?? '',
        'motivo'     => $co['strategy']['motivo'],
        'que_vender' => $co['strategy']['que_vender'],
        'objecion'   => $co['strategy']['objecion'],
        'enfoque'    => $co['strategy']['enfoque'],
        'mensaje'    => $co['strategy']['mensaje'],
    ];
}, $companies));
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar Demo — Oportunidades de Negocio B2B en Tiempo Real',
        'excerptText' => 'Detecta empresas recién creadas con alta probabilidad de compra. Ve quién contactar y qué decirle.',
        'canonical'   => site_url('radar-demo'),
        'robots'      => 'index,follow',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/precios_radar.css?v=' . filemtime(FCPATH . 'public/css/precios_radar.css')) ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . filemtime(FCPATH . 'public/css/radar.css')) ?>">

<style>
/* ─── HERO ──────────────────────────────────────────── */
.radar-hero         { border-bottom: none !important; }
.radar-hero__shell  { border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; padding: 60px 0 52px !important; }

.demo-badge-green   { background: #ecfdf5 !important; border-color: #a7f3d0 !important; color: #065f46 !important; }
.demo-badge-dot     { background: #10b981 !important; box-shadow: 0 0 8px rgba(16,185,129,.5) !important; }

.demo-bullets       { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 10px; margin: 0 auto 32px; }
.demo-bullet        { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 999px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: .85rem; font-weight: 700; color: #065f46; }

/* ─── PIPELINE BAND ──────────────────────────────────── */
.pipeline-band {
    background: #f1f5f9;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 64px 0;
}
.pipeline-headline {
    text-align: center;
    margin-bottom: 48px;
}
.pipeline-live-tag {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 999px;
    background: #fff; border: 1px solid #e2e8f0;
    font-size: .68rem; font-weight: 800; letter-spacing: .08em;
    text-transform: uppercase; color: #64748b;
    margin-bottom: 16px;
}
.pipeline-live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2);
    animation: blink 1.8s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
.pipeline-headline h2 {
    font-size: clamp(1.5rem, 2.8vw, 2rem);
    font-weight: 900; letter-spacing: -.025em; color: #0f172a;
    margin: 0 0 8px;
}
.pipeline-headline p {
    font-size: .95rem; color: #64748b; margin: 0; font-weight: 500;
}

/* 3 stat cards */
.pipeline-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
.pipeline-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px 28px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15,23,42,.06);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.pipeline-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 20px 20px 0 0;
}
.pipeline-card--blue::before  { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.pipeline-card--green::before { background: linear-gradient(90deg, #16a34a, #4ade80); }
.pipeline-card--amber::before { background: linear-gradient(90deg, #d97706, #fbbf24); }

.pipeline-card__icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
    flex-shrink: 0;
}
.pipeline-card--blue  .pipeline-card__icon { background: #eff6ff; color: #2563eb; }
.pipeline-card--green .pipeline-card__icon { background: #f0fdf4; color: #16a34a; }
.pipeline-card--amber .pipeline-card__icon { background: #fffbeb; color: #d97706; }

.pipeline-card__label {
    font-size: .72rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; color: #94a3b8; margin-bottom: 8px;
}
.pipeline-card__num {
    font-size: clamp(2.2rem, 4vw, 3rem);
    font-weight: 950; letter-spacing: -.045em; line-height: 1;
    margin-bottom: 4px;
}
.pipeline-card--blue  .pipeline-card__num { color: #1d4ed8; }
.pipeline-card--green .pipeline-card__num { color: #15803d; }
.pipeline-card--amber .pipeline-card__num { color: #b45309; }

.pipeline-card__sub {
    font-size: .88rem; font-weight: 600; color: #94a3b8; margin-bottom: 12px;
}
.pipeline-card__desc {
    font-size: .82rem; color: #64748b; line-height: 1.5;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}

/* ROI callout */
.pipeline-roi {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 24px; border-radius: 14px;
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1px solid #bbf7d0;
}
.pipeline-roi__icon {
    width: 40px; height: 40px; flex-shrink: 0;
    background: #dcfce7; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #16a34a;
}
.pipeline-roi__text {
    font-size: .95rem; font-weight: 600; color: #166534; line-height: 1.5;
}
.pipeline-roi__text strong { font-weight: 900; color: #14532d; }
.pipeline-roi__cta {
    margin-left: auto; flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 18px; border-radius: 10px;
    background: #16a34a; color: #fff;
    font-size: .85rem; font-weight: 800;
    text-decoration: none; white-space: nowrap;
    transition: background .15s;
}
.pipeline-roi__cta:hover { background: #15803d; color: #fff; }

/* ─── TABLE SECTION ──────────────────────────────────── */
.ticket-pill    { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 999px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: .8rem; font-weight: 800; color: #15803d; white-space: nowrap; }
.priority-pill  { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
.priority-pill--alta     { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
.priority-pill--muy_alta { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

.btn-how { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 10px; border: 1.5px solid #2563eb; background: #fff; color: #2563eb; font-size: .83rem; font-weight: 800; cursor: pointer; transition: all .15s ease; white-space: nowrap; }
.btn-how:hover { background: #2563eb; color: #fff; }
.btn-how.loading { opacity: .6; pointer-events: none; }

/* ─── HOW IT WORKS ───────────────────────────────────── */
.steps-section { background: #fff; }
.steps-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
    position: relative;
}
/* Línea conectora horizontal */
.steps-grid::before {
    content: "";
    position: absolute;
    top: 36px;
    left: calc(10% + 20px);
    right: calc(10% + 20px);
    height: 2px;
    background: linear-gradient(90deg, #dbeafe, #bfdbfe, #a5f3fc, #bbf7d0, #d8b4fe);
    z-index: 0;
}
.step-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 16px 32px;
    position: relative;
    z-index: 1;
}
/* Icono principal */
.step-icon-wrap {
    width: 72px; height: 72px;
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
    position: relative;
    border: 4px solid #fff;
    box-shadow: 0 8px 24px -8px rgba(0,0,0,.12);
    transition: transform .2s ease, box-shadow .2s ease;
}
.step-card:hover .step-icon-wrap {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px -8px rgba(0,0,0,.16);
}
.step-icon-wrap--1 { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; }
.step-icon-wrap--2 { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a; }
.step-icon-wrap--3 { background: linear-gradient(135deg, #fff7ed, #fed7aa); color: #d97706; }
.step-icon-wrap--4 { background: linear-gradient(135deg, #fdf4ff, #f3e8ff); color: #9333ea; }
.step-icon-wrap--5 { background: linear-gradient(135deg, #ecfeff, #cffafe); color: #0891b2; }

/* Número en label sutil */
.step-label {
    font-size: .7rem; font-weight: 800; letter-spacing: .12em;
    text-transform: uppercase; color: #94a3b8;
    margin-bottom: 6px;
    font-variant-numeric: tabular-nums;
}
.step-card h3 {
    font-size: .98rem; font-weight: 800; color: #0f172a;
    margin: 0 0 8px;
}
.step-card p {
    font-size: .82rem; color: #64748b; margin: 0; line-height: 1.55;
}

/* ─── CTA DARK CARD ──────────────────────────────────── */
.cta-dark { padding: 72px 0; }
.cta-dark__card {
    background: linear-gradient(135deg, #060d1f 0%, #0d1f42 50%, #112060 100%);
    border-radius: 28px;
    padding: 64px 64px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 40px 80px -24px rgba(10,20,60,.5);
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 56px;
    align-items: center;
}
/* Glow orbs */
.cta-dark__card::before {
    content: "";
    position: absolute; top: -80px; left: 30%;
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(37,99,235,.22) 0%, transparent 70%);
    pointer-events: none;
}
.cta-dark__card::after {
    content: "";
    position: absolute; bottom: -60px; right: 60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(16,185,129,.12) 0%, transparent 70%);
    pointer-events: none;
}
/* Dot pattern */
.cta-dark__dots {
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
    border-radius: 28px;
}
.cta-dark__left  { position: relative; z-index: 1; }
.cta-dark__right { position: relative; z-index: 1; flex-shrink: 0; }

.cta-dark__kicker {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 6px 14px; border-radius: 999px;
    border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.07);
    font-size: .7rem; font-weight: 800; letter-spacing: .07em;
    color: rgba(255,255,255,.8); margin-bottom: 20px;
}
.cta-dark__card h2 {
    font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 900;
    letter-spacing: -.03em; line-height: 1.18; margin: 0 0 14px;
}
.cta-dark__card > .cta-dark__left > p {
    font-size: 1rem; color: rgba(255,255,255,.65);
    margin: 0 0 32px; max-width: 500px; line-height: 1.7;
}
.cta-dark__actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.cta-dark__note { margin-top: 16px; font-size: .82rem; color: rgba(255,255,255,.38); }

/* Price box */
.cta-price-box {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px;
    padding: 28px 32px;
    text-align: center;
    min-width: 200px;
}
.cta-price-box__label {
    font-size: .7rem; font-weight: 800; letter-spacing: .1em;
    text-transform: uppercase; color: rgba(255,255,255,.5);
    margin-bottom: 12px;
}
.cta-price-box__price {
    font-size: 3rem; font-weight: 950; letter-spacing: -.05em;
    line-height: 1; color: #fff; margin-bottom: 4px;
}
.cta-price-box__period {
    font-size: .88rem; color: rgba(255,255,255,.5); font-weight: 600;
    margin-bottom: 20px;
}
.cta-trust-items {
    display: flex; flex-direction: column; gap: 8px;
    text-align: left;
}
.cta-trust-item {
    display: flex; align-items: center; gap: 8px;
    font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.65);
}
.cta-trust-item svg { flex-shrink: 0; color: #34d399; }

@media (max-width: 900px) {
    .cta-dark__card { grid-template-columns: 1fr; padding: 48px 32px; }
    .cta-dark__right { display: none; }
}

/* ─── CLOSING ─────────────────────────────────────────── */

/* ─── MODAL EXTRAS ───────────────────────────────────── */
.demo-unlock { margin-top: 24px; padding: 20px; background: linear-gradient(135deg,#eff6ff,#f0fdf4); border-radius: 14px; border: 1px solid #dbeafe; text-align: center; }
.demo-unlock p { font-size: .87rem; color: #334155; margin: 0 0 14px; font-weight: 600; }
.ae-ai-modal__badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af; font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 6px; }

/* ─── RESPONSIVE ─────────────────────────────────────── */
@media (max-width: 900px) {
    .pipeline-cards { grid-template-columns: 1fr; }
    .pipeline-roi   { flex-direction: column; align-items: flex-start; }
    .pipeline-roi__cta { margin-left: 0; width: 100%; justify-content: center; }
    .steps-grid { grid-template-columns: 1fr 1fr; }
    .cta-dark__card { padding: 48px 28px; }
}
@media (max-width: 640px) {
    .steps-grid { grid-template-columns: 1fr; }
    .radar-table thead th:nth-child(3),
    .radar-table tbody td:nth-child(3),
    .radar-table thead th:nth-child(4),
    .radar-table tbody td:nth-child(4) { display: none; }
}

/* ─── CLOSING (light) ─────────────────────────────────── */
.closing {
    padding: 96px 0 104px;
    text-align: center;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 60%, #f1f5f9 100%);
    position: relative;
    overflow: hidden;
    border-top: 1px solid #e2e8f0;
}
/* Orb glow top-left */
.closing::before {
    content: "";
    position: absolute;
    top: -100px; left: -60px;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(37,99,235,.06) 0%, transparent 65%);
    pointer-events: none;
}
/* Orb glow bottom-right */
.closing::after {
    content: "";
    position: absolute;
    bottom: -80px; right: -40px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(16,185,129,.05) 0%, transparent 65%);
    pointer-events: none;
}
/* Separator glow at bottom edge */
.closing-bottom-glow {
    position: absolute;
    bottom: 0; left: 50%; transform: translateX(-50%);
    width: 60%; height: 1px;
    background: linear-gradient(90deg, transparent, #dbeafe, #d1fae5, transparent);
    pointer-events: none;
}
/* Dot grid */
.closing-dots {
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(15,23,42,.04) 1px, transparent 1px);
    background-size: 30px 30px;
    pointer-events: none;
}
.closing-inner { position: relative; z-index: 1; }

/* Urgency badge */
.closing-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 7px 16px; border-radius: 999px;
    background: #eff6ff; border: 1px solid #bfdbfe;
    font-size: .7rem; font-weight: 800; letter-spacing: .1em;
    text-transform: uppercase; color: #1d4ed8;
    margin-bottom: 36px;
}
.closing-badge-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2);
    animation: blink 1.8s ease-in-out infinite;
}

/* Quote */
.closing__quote {
    font-size: clamp(1.25rem, 2vw, 1.6rem);
    font-weight: 900; letter-spacing: -.025em; color: #0f172a;
    line-height: 1.35; max-width: 900px; margin: 0 auto 20px;
}
.closing__quote .grad {
    background: linear-gradient(90deg, #1e3a8a, #2563eb, #0891b2);
    -webkit-background-clip: text; background-clip: text; color: transparent;
    white-space: nowrap;
}
.closing__sub {
    font-size: 1rem; color: #64748b;
    margin: 0 auto 52px; max-width: 460px; line-height: 1.7;
}

/* Stats row */
.closing-stats {
    display: inline-flex; align-items: stretch;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 18px; overflow: hidden;
    margin-bottom: 44px;
    box-shadow: 0 4px 20px rgba(15,23,42,.06);
}
.closing-stat {
    padding: 20px 32px;
    text-align: center;
    border-right: 1px solid #e2e8f0;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.closing-stat:last-child { border-right: none; }
.closing-stat__icon {
    width: 34px; height: 34px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 8px;
}
.closing-stat:nth-child(1) .closing-stat__icon { background: #eff6ff; color: #2563eb; }
.closing-stat:nth-child(2) .closing-stat__icon { background: #f0fdf4; color: #16a34a; }
.closing-stat:nth-child(3) .closing-stat__icon { background: #fffbeb; color: #d97706; }
.closing-stat:nth-child(4) .closing-stat__icon { background: #fdf4ff; color: #9333ea; }
.closing-stat__num { font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -.04em; line-height: 1; }
.closing-stat__lbl { font-size: .65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; }

/* CTA area */
.closing-cta { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.closing-trust {
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap; justify-content: center;
    margin-top: 4px;
}
.closing-trust-item {
    display: flex; align-items: center; gap: 6px;
    font-size: .78rem; font-weight: 600; color: #94a3b8;
}
.closing-trust-item svg { color: #10b981; flex-shrink: 0; }

@media (max-width: 640px) {
    .closing-stats { flex-direction: column; width: 100%; }
    .closing-stat  { border-right: none; border-bottom: 1px solid #e2e8f0; }
    .closing-stat:last-child { border-bottom: none; }
}
</style>
</head>
<body>
<?= view('partials/header') ?>

<main class="radar-page">

    <!-- ① HERO ──────────────────────────────────────── -->
    <section class="radar-hero">
        <div class="container">
            <div class="radar-hero__shell">

                <div class="radar-hero__badge demo-badge-green" style="background:#f8fafc!important; border-color:#e2e8f0!important; color:#64748b!important; margin-bottom: 24px;">
                    +<?= $opps_count ?> empresas detectadas HOY — varias ya están cerrando proveedor
                </div>

                <h1 class="radar-hero__title">
                    +<?= $opps_count ?> empresas detectadas HOY que necesitan proveedor ahora mismo
                </h1>

                <p class="radar-hero__subtitle" style="max-width: 680px; margin: 0 auto 12px;">
                    Empresas recién creadas que necesitan proveedores ahora mismo. Accede antes que otros equipos comerciales.
                </p>

                <div style="margin-bottom: 32px;">
                    <p style="font-size: 1.1rem; font-weight: 700; color: #b45309; margin-bottom: 4px;">
                        Muchas de estas empresas están cerrando proveedor en los próximos días.
                    </p>
                    <p style="font-size: .9rem; font-weight: 600; color: #d97706; margin-bottom: 0;">
                        Algunas oportunidades desaparecen en menos de 72h.
                    </p>
                </div>

                <div style="margin-bottom: 24px; font-size: 0.85rem; color: #64748b; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="color:#10b981;"><polyline points="20 6 9 17 4 12"/></svg>
                    Datos reales del BORME actualizados diariamente + scoring propio basado en actividad empresarial
                </div>

                <div class="radar-hero__actions">
                    <a href="#demo-table"
                       class="radar-btn radar-btn--primary"
                       style="font-size: 1.1rem; padding: 18px 32px;"
                       onclick="trackRadarEvent({event_type:'click_cta', source:'hero', cta_label:'Acceder a estas empresas antes que tu competencia', url:'#demo-table'}); document.getElementById('demo-table').scrollIntoView({behavior:'smooth'}); return false;">
                        👉 Acceder a estas empresas antes que tu competencia
                    </a>
                </div>

                <div style="margin-top: 24px; text-align: center;">
                    <p style="margin: 0 0 6px; font-size: 1.1rem; font-weight: 800; color: #b45309;">
                        Hoy hay +<?= $opps_count ?> empresas esperando ser contactadas
                    </p>
                    <p style="margin: 0; font-size: .95rem; color: #64748b; font-weight: 600;">
                        Cada día nuevas empresas aparecen y otras desaparecen (ya contactadas)
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Prueba social ──────────────────────────────────────── -->
    <div style="background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 0;text-align:center;">
        <div class="container">
            <p style="margin:0;font-size:.82rem;font-weight:700;color:#64748b;">Usado por <strong style="color:#0f172a;">agencias, SaaS y asesorías</strong> para generar clientes nuevos cada semana &mdash; sin inversión en publicidad</p>
        </div>
    </div>


    <!-- ② VALOR ────────────────────────────── -->
    <section class="pipeline-band">
        <div class="container">
            <div class="pipeline-cards" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 0;">

                <div class="pipeline-card pipeline-card--blue" style="justify-content: center; align-items: center; text-align: center; padding: 32px 24px;">
                    <div class="pipeline-card__icon" style="margin-bottom: 20px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label" style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:0;text-transform:none;letter-spacing:normal;">Detecta empresas en su momento de compra</div>
                </div>

                <div class="pipeline-card pipeline-card--green" style="justify-content: center; align-items: center; text-align: center; padding: 32px 24px;">
                    <div class="pipeline-card__icon" style="margin-bottom: 20px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label" style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:0;text-transform:none;letter-spacing:normal;">Prioriza por probabilidad real de cierre</div>
                </div>

                <div class="pipeline-card pipeline-card--amber" style="justify-content: center; align-items: center; text-align: center; padding: 32px 24px;">
                    <div class="pipeline-card__icon" style="margin-bottom: 20px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label" style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:0;text-transform:none;letter-spacing:normal;">Contacta antes que tu competencia</div>
                </div>

            </div>
        </div>
    </section>


    <!-- ③ TABLA DEMO ─────────────────────────────────── -->
    <section class="radar-section" id="demo-table">
        <div class="container">

            <div style="margin-bottom:24px; text-align: center;">
                <div class="pipeline-live-tag" style="margin-bottom: 12px; display: inline-flex;">
                    <span class="pipeline-live-dot"></span>
                    Vista real del sistema — datos actualizados en tiempo real
                </div>
            </div>

            <div class="radar-preview">
                <div class="radar-preview__toolbar">
                    <div class="radar-preview__dots"><span></span><span></span><span></span></div>
                    <span style="font-size:.8rem;font-weight:700;color:#64748b;">Radar B2B · <?= count($companies) ?> oportunidades premium</span>
                    <div class="radar-preview__filters">
                        <span>Score &gt; 82</span>
                        <span>Sectores B2B</span>
                        <span>Alta prioridad</span>
                    </div>
                </div>

                <div class="radar-table-wrapper" style="position: relative; border-radius: 0 0 20px 20px; overflow: hidden; min-height: 520px; background: #fff;">
                    <table class="radar-table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="position: relative; z-index: 5;">
                                <th>Empresa</th>
                                <th>Ubicación</th>
                                <th class="desktop-only">Ticket Est.</th>
                                <th class="desktop-only">Prioridad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $i => $co): ?>
                            <tr style="<?= $i >= 3 ? 'filter: blur(5px); opacity: 0.4; pointer-events: none;' : '' ?>">
                                <td>
                                <div class="radar-table__company"><?= esc($co['company_name']) ?></div>
                                <div class="radar-table__meta" style="font-size:.8rem;margin-top:3px;"><?= esc($co['cnae_label'] ?? '—') ?></div>
                            </td>
                            <td>
                                <div class="radar-table__meta" style="font-weight:700;"><?= esc($co['municipality'] ?? '—') ?></div>
                                <div class="radar-table__meta" style="font-size:.78rem;"><?= esc($co['registro_mercantil'] ?? '') ?></div>
                            </td>
                            <td><span class="ticket-pill">5.000€ – 12.000€</span></td>
                            <td>
                                <span class="priority-pill priority-pill--<?= esc($co['priority_level'] ?? 'alta') ?>">
                                    <?= $co['priority_level'] === 'muy_alta' ? '🔥 Muy Alta' : '⚡ Alta' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-how" id="btn-<?= $i ?>" onclick="openStrategy(<?= $i ?>, this)">
                                    🎯 Cómo venderle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Overlay de Desbloqueo -->
                    <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 120px, rgba(255,255,255,0.7) 180px, #ffffff 320px); display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 40px 20px 60px; z-index: 10;">
                        <div style="max-width: 550px; text-align: center; transform: translateY(-20px);">
                            <h3 style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0 0 10px; letter-spacing: -0.02em; line-height: 1.2;">
                                Estas empresas ya están siendo contactadas — algunas cerrarán proveedor en días
                            </h3>
                            <p style="font-size: 1.05rem; color: #b45309; font-weight: 700; margin: 0 0 28px; line-height: 1.5;">
                                Accede ahora o llegarás tarde
                            </p>
                            <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=tabla') ?>" 
                               class="radar-btn radar-btn--primary"
                               style="padding: 22px 44px; font-size: 1.15rem; box-shadow: 0 12px 30px -5px rgba(37,99,235,0.5); width: 100%; justify-content: center;"
                               onclick="trackRadarEvent({event_type:'click_cta', source:'tabla_bottom', cta_label:'Acceder a estas empresas antes que tu competencia', url:'/checkout/radar-export?type=subscription&plan=radar&source=tabla'})">
                                👉 "Acceder a estas empresas antes que tu competencia"
                            </a>
                            <div style="margin-top: 24px; display: flex; align-items: center; justify-content: center; gap: 16px; font-size: 0.88rem; color: #94a3b8; font-weight: 700;">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Sin permanencia
                                </span>
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Acceso instantáneo
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

        </div>
    </section>


    <!-- ③b WOW MOMENT ───────────────────────────────── -->
    <?php if (!empty($companies[0])): ?>
    <?php $wow = $companies[0]; ?>
    <section id="wow-moment" style="background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;padding:64px 0;">
        <div class="container">
            <div style="max-width:780px;margin:0 auto;">

                <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                    <span style="font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;padding:5px 12px;border-radius:999px;">🧠 Ejemplo real de oportunidad detectada</span>
                    <span style="font-size:.75rem;color:#94a3b8;font-weight:600;">Así funciona Radar con una empresa de hoy</span>
                </div>

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,.07);">

                    <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:20px 28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                        <div>
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:4px;">Empresa detectada hoy</div>
                            <div style="font-size:1.15rem;font-weight:900;color:#fff;"><?= esc($wow['company_name']) ?></div>
                            <div style="font-size:.82rem;color:rgba(255,255,255,.65);margin-top:2px;"><?= esc($wow['cnae_label'] ?? '') ?> · <?= esc($wow['municipality'] ?? '') ?></div>
                        </div>
                        <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:12px;padding:10px 18px;text-align:center;">
                            <div style="font-size:.65rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.5);">Ticket estimado</div>
                            <div style="font-size:1.1rem;font-weight:900;color:#fff;">5.000€ – 12.000€</div>
                        </div>
                    </div>

                    <div style="padding:24px 28px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="background:#f8fafc;border-radius:12px;padding:16px;">
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:8px;">🎯 Por qué es una oportunidad</div>
                            <p style="font-size:.88rem;color:#334155;margin:0;line-height:1.6;"><?= esc($wow['strategy']['motivo'] ?? 'Empresa recién creada con necesidad inmediata de servicios B2B.') ?></p>
                        </div>
                        <div style="background:#f0f7ff;border-radius:12px;padding:16px;">
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#1e40af;margin-bottom:8px;">🚀 Qué necesita esta empresa</div>
                            <p style="font-size:.88rem;color:#1e3a8a;font-weight:700;margin:0;line-height:1.6;"><?= esc($wow['strategy']['que_vender'] ?? '') ?></p>
                        </div>
                    </div>

                    <div style="padding:0 28px 24px; text-align: center;">
                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=estrategia') ?>" 
                           class="radar-btn radar-btn--primary" 
                           style="font-size:1.1rem;padding:16px 32px;"
                           onclick="trackRadarEvent({event_type:'click_cta', source:'estrategia', cta_label:'Acceder a estas empresas antes que tu competencia', url:'/checkout/radar-export?type=subscription&plan=radar&source=estrategia'})">
                            👉 "Acceder a estas empresas antes que tu competencia"
                        </a>
                        <p style="margin:16px 0 0;font-size:.9rem;font-weight:700;color:#2563eb;text-align:center;">Accede al radar para ver todas las oportunidades activas</p>
                    </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <!-- ④ CÓMO FUNCIONA ──────────────────────────────── -->
    <section class="radar-section radar-section--soft steps-section">
        <div class="container">
            <div class="radar-heading radar-heading--center" style="margin-bottom:40px;">
                <h2 class="radar-title" style="margin-top:8px;font-size:1.4rem;">Proceso</h2>
            </div>

            <style>
                .steps-grid--3 { grid-template-columns: repeat(3, 1fr); }
                .steps-grid--3::before { left: calc(16.6% + 20px) !important; right: calc(16.6% + 20px) !important; }
                @media (max-width: 900px) {
                    .steps-grid--3 { grid-template-columns: 1fr; }
                    .steps-grid--3::before { display: none; }
                }
            </style>
            <div class="steps-grid steps-grid--3">

                <!-- 1 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--1">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 01</div>
                    <h3>Detecta empresas nuevas</h3>
                </div>

                <!-- 2 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 02</div>
                    <h3>Analiza su potencial</h3>
                </div>

                <!-- 3 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--5">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9.81a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 03</div>
                    <h3>Contacta antes que otros</h3>
                </div>

            </div>
        </div>
    </section>


    <!-- ⑤ PRECIO ───────────────────────────────────── -->
    <section class="cta-dark" style="padding: 64px 0;">
        <div class="container">
            <div class="cta-dark__card" style="display: flex; justify-content: center; text-align: center; background: linear-gradient(135deg, #060d1f 0%, #112060 100%);">
                
                <div class="cta-price-box" style="max-width: 500px; width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 48px 32px;">
                    <div class="cta-price-box__label" style="font-size: 1rem; color: #fff; margin-bottom: 24px; text-transform: none; letter-spacing: normal; font-weight: 800;">Acceso completo al Radar</div>
                    <div class="cta-price-box__price" style="font-size: 4rem;">79€</div>
                    <div class="cta-price-box__period" style="margin-bottom: 8px;">/mes</div>
                    <div style="margin-bottom: 32px;">
                        <div style="font-size: 1rem; font-weight: 800; color: #34d399; margin-bottom: 4px;">👉 Con 1 cliente cubres el coste mensual</div>
                        <div style="font-size: .85rem; font-weight: 600; color: rgba(255,255,255,0.5);">La mayoría de usuarios recuperan la inversión en su primera oportunidad cerrada</div>
                    </div>
                    
                    <div class="cta-trust-items" style="align-items: center; gap: 12px; margin-bottom: 40px;">
                        <div class="cta-trust-item" style="font-size: 1rem; color: rgba(255,255,255,0.8);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Acceso completo a oportunidades
                        </div>
                        <div class="cta-trust-item" style="font-size: 1rem; color: rgba(255,255,255,0.8);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Datos de contacto
                        </div>
                        <div class="cta-trust-item" style="font-size: 1rem; color: rgba(255,255,255,0.8);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Actualización diaria
                        </div>
                    </div>

                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=pricing') ?>" 
                       class="radar-btn radar-btn--primary" 
                       style="width: 100%; justify-content: center; font-size: 1.15rem; padding: 20px;"
                       onclick="trackEvent({event_type:'click_cta', source:'pricing', cta_label:'Acceder a estas empresas antes que tu competencia', url:'/checkout/radar-export?type=subscription&plan=radar&source=pricing'})">
                        👉 "Acceder a estas empresas antes que tu competencia"
                    </a>
                    <p style="margin: 16px 0 0; font-size: .85rem; color: rgba(255,255,255,0.4); font-weight: 600;">Sin permanencia · Cancela cuando quieras</p>
                </div>

            </div>
        </div>
    </section>


    <!-- ⑥ CIERRE ─────────────────────────────────────── -->
    <section class="closing" style="padding: 80px 0;">
        <div class="closing-dots"></div>
        <div class="closing-bottom-glow"></div>
        <div class="container closing-inner">

            <div class="closing-cta">
                <p style="margin: 0 0 24px; font-size: 1.15rem; font-weight: 800; color: #0f172a; text-align: center;">
                    "Las empresas que ves hoy pueden no estar mañana."
                </p>
                <a href="<?= site_url('register?source=cta_final') ?>" 
                   class="radar-btn radar-btn--primary" 
                   style="font-size:1.2rem;padding:22px 56px;box-shadow:0 0 50px rgba(37,99,235,0.5);"
                   onclick="trackEvent({event_type:'click_cta', source:'cta_final', cta_label:'Acceder a estas empresas antes que tu competencia', url:'/register?source=cta_final'})">
                    🚀 Acceder a estas empresas antes que tu competencia
                </a>
                
                <div style="margin-top: 32px; text-align: center;">
                    <p style="margin: 0 0 8px; font-size: 1.2rem; font-weight: 800; color: #b45309;">
                        Hoy hay +<?= $opps_count ?> empresas esperando ser contactadas
                    </p>
                    <p style="margin: 0; font-size: 1rem; color: #64748b; font-weight: 600;">
                        Cada día nuevas empresas entran y otras desaparecen (ya contactadas)
                    </p>
                </div>
            </div>

        </div>
    </section>

</main>

<?= view('partials/footer') ?>


<!-- MODAL (mismo diseño que el Radar real) -->
<div id="ae-ai-modal" class="ae-ai-modal" style="display:none;">
    <div class="ae-ai-modal__backdrop" onclick="closeModal()"></div>
    <div class="ae-ai-modal__container">
        <header class="ae-ai-modal__header" style="flex-direction:column;align-items:flex-start;gap:4px;padding:16px 24px;">
            <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                <div>
                    <span class="ae-ai-modal__badge">RADAR B2B</span>
                    <h2 class="ae-ai-modal__title" style="margin:0;">🎯 Estrategia de venta</h2>
                    <p style="margin:4px 0 0;font-size:13px;color:#64748b;font-weight:600;">Alta probabilidad de cierre si contactas en el momento adecuado</p>
                </div>
                <button type="button" class="ae-ai-modal__close" onclick="closeModal()" style="position:static;font-size:28px;">×</button>
            </div>
            <div id="modal-company-tag" style="font-size:13px;font-weight:700;color:#2563eb;background:#eff6ff;padding:4px 12px;border-radius:8px;margin-top:8px;display:none;"></div>
            <div style="background:#fef2f2;color:#dc2626;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:800;margin-top:8px;border:1px solid #fee2e2;display:flex;align-items:center;gap:6px;">
                <span>⚠️</span> Otros proveedores pueden contactar antes
            </div>
        </header>
        <div id="modal-body" class="ae-ai-modal__body"></div>
    </div>
</div>


<script>
const STRATEGIES = <?= $strategiesJson ?>;

function openStrategy(i, btn) {
    const s = STRATEGIES[i];
    if (!s) return;
    
    // TRACKING: Modal opened
    trackRadarEvent({
        event_type: 'radar_modal_opened',
        source: 'demo_table',
        metadata: { company_id: s.id, company_name: s.name }
    });

    const modal = document.getElementById('ae-ai-modal');
    const body  = document.getElementById('modal-body');
    const tag   = document.getElementById('modal-company-tag');

    tag.innerText     = s.name;
    tag.style.display = 'inline-block';

    btn.innerHTML = '⏳ Analizando...';
    btn.classList.add('loading');

    body.innerHTML = `<div style="text-align:center;padding:48px 0;">
        <div class="ae-spinner"></div>
        <p style="margin-top:20px;font-weight:700;color:#1e293b;font-size:17px;">Generando estrategia con IA...</p>
        <p style="color:#64748b;font-size:14px;">Analizando sector y oportunidad comercial.</p>
    </div>`;
    modal.style.display = 'flex';

    // Tracking de visualización de estrategia
    trackEvent({
        event_type: 'view_strategy',
        source: 'tabla',
        cta_label: 'Cómo venderle',
        metadata: { company_name: s.name }
    });

    setTimeout(() => {
        btn.innerHTML = '🎯 Cómo venderle';
        btn.classList.remove('loading');

        body.innerHTML = `<div class="ae-ai-result">

            <div class="ae-ai-card ae-ai-card--summary" style="margin-bottom:20px;padding:20px;">
                <span class="ae-ai-result__label">Empresa analizada</span>
                <div style="font-size:17px;font-weight:800;color:#1e293b;margin-bottom:6px;">${s.name}</div>
                <p style="font-size:14px;line-height:1.5;color:#475569;margin:0;">${s.sector} · ${s.location}</p>
            </div>

            <div class="ai-kpis-grid">
                <div class="ai-kpi-card ai-kpi-card--prob-alta">
                    <span class="ai-kpi-label">Probabilidad</span>
                    <span class="ai-kpi-value">Alta</span>
                    <span class="ai-kpi-desc">Empresa en arranque</span>
                </div>
                <div class="ai-kpi-card">
                    <span class="ai-kpi-label">Ventana óptima</span>
                    <span class="ai-kpi-value">Ahora</span>
                    <span class="ai-kpi-desc">Constitución reciente</span>
                </div>
                <div class="ai-kpi-card">
                    <span class="ai-kpi-label">Ticket estimado</span>
                    <span class="ai-kpi-value">5k – 12k €</span>
                    <span class="ai-kpi-desc">Primer contrato</span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="ae-ai-card" style="padding:16px;">
                    <span class="ae-ai-result__label">🧠 Por qué es oportunidad</span>
                    <p style="font-size:13px;color:#334155;margin:0;line-height:1.6;">${s.motivo}</p>
                </div>
                <div class="ae-ai-card" style="padding:16px;background:#f0f7ff;">
                    <span class="ae-ai-result__label" style="color:#1e40af;">🚀 Qué venderle primero</span>
                    <p style="font-size:13px;color:#1e3a8a;font-weight:700;margin:0;line-height:1.6;">${s.que_vender}</p>
                </div>
            </div>

            <div class="ai-objection-box">
                <div class="ai-box-icon">⚠️</div>
                <div class="ai-box-content">
                    <span class="ai-box-label">Objeción más probable</span>
                    <p class="ai-box-text">"${s.objecion}"</p>
                </div>
            </div>

            <div class="ai-angle-box">
                <div class="ai-box-icon">🎯</div>
                <div class="ai-box-content">
                    <span class="ai-box-label">Enfoque recomendado</span>
                    <p class="ai-box-text">${s.enfoque}</p>
                </div>
            </div>

            <div style="padding:18px;background:#f1f5f9;border-radius:12px;border:2px dashed #2563eb;margin-bottom:16px;">
                <span class="ae-ai-result__label" style="color:#2563eb;">✉ Mensaje inicial sugerido</span>
                <p id="msg-text" style="font-size:14px;line-height:1.6;color:#1e293b;margin:0 0 14px;font-style:italic;">"${s.mensaje}"</p>
                <button type="button" onclick="copyMsg(this)"
                    style="width:100%;height:40px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-weight:700;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;"><rect x="9" y="9" width="13" height="13" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    📋 Copiar mensaje
                </button>
            </div>

            <div class="demo-unlock">
                <p>Con el <strong>Radar completo</strong> obtienes el nombre del administrador, teléfono y correo de contacto directo para esta empresa.</p>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=modal_estrategia') ?>" 
                   class="radar-btn radar-btn--primary" 
                   style="width:100%;display:flex;justify-content:center;"
                   onclick="trackRadarEvent({event_type:'click_cta', source:'estrategia', cta_label:'Activar Radar y obtener datos de contacto', url:'/checkout/radar-export?type=subscription&plan=radar&source=modal_estrategia'})">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Activar Radar y obtener datos de contacto
                </a>
                <p style="margin:10px 0 0;font-size:.78rem;color:#94a3b8;">79€/mes · Sin compromiso</p>
            </div>

        </div>`;
    }, 480);
}

function closeModal() {
    document.getElementById('ae-ai-modal').style.display = 'none';
    document.getElementById('modal-company-tag').style.display = 'none';
}

/**
 * Sistema de Tracking Personalizado
 */
function trackRadarEvent(data) {
    // 1. Call Global Tracking System (if available)
    if (window.trackEvent) {
        window.trackEvent(data.event_type || 'radar_event', {
            source: data.source,
            cta_label: data.cta_label,
            url: data.url,
            ...data.metadata
        });
    }

    const payload = {
        event_type: data.event_type,
        source: data.source,
        cta_label: data.cta_label,
        url: data.url,
        page: 'radar-demo',
        metadata: data.metadata || {}
    };
    
    console.log('[Radar Tracking]', payload);
    
    const endpoint = '<?= site_url("tracking/radar-demo-event") ?>';
    
    try {
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            navigator.sendBeacon(endpoint, blob);
        } else {
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                keepalive: true
            });
        }
    } catch (e) {}
}


function copyMsg(btn) {
    const text = document.getElementById('msg-text').innerText.replace(/^\"|\"$/g, '');
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '✅ Copiado';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>
