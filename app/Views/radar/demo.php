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

                <div class="radar-hero__badge demo-badge-green">
                    <span class="radar-hero__badge-dot demo-badge-dot"></span>
                    DEMO COMERCIAL · RADAR B2B
                </div>

                <h1 class="radar-hero__title">
                    Consigue clientes reales en menos de 7 días<br>
                    <span>sin hacer prospección manual</span>
                </h1>

                <p style="font-size:1rem;font-weight:700;color:#15803d;background:#f0fdf4;border:1px solid #bbf7d0;display:inline-block;padding:10px 20px;border-radius:10px;margin:0 0 8px;">
                    💰 Hasta <strong style="font-size:1.4rem;letter-spacing:-.03em;"><?= number_format($metrics['pipeline_max'] / 1000000, 1, ',', '.') ?>M€</strong> en oportunidades detectadas hoy
                </p>
                <p style="font-size:.82rem;color:#64748b;font-weight:600;margin:0 0 20px;">Empresas reales que puedes contactar hoy mismo. Sin búsqueda manual. Sin permanencia · Cancela cuando quieras</p>

                <p class="radar-hero__subtitle">
                    Detectamos empresas recién creadas con alta probabilidad de necesitar tus servicios
                    y te decimos <strong>a quién contactar</strong> y <strong>qué decirle</strong>.
                </p>

                <div class="demo-bullets">
                    <span class="demo-bullet">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Nuevas empresas cada día
                    </span>
                    <span class="demo-bullet">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Alta probabilidad de compra
                    </span>
                    <span class="demo-bullet">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Mensaje listo para enviar
                    </span>
                </div>

                <div class="radar-hero__actions">
                    <a href="#demo-table"
                       class="radar-btn radar-btn--primary"
                       onclick="trackRadarEvent({event_type:'click_cta', source:'hero', cta_label:'Ver clientes disponibles ahora', url:'#demo-table'}); document.getElementById('demo-table').scrollIntoView({behavior:'smooth'}); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Ver clientes disponibles ahora
                    </a>
                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=hero_secondary') ?>" 
                       class="radar-btn radar-btn--ghost"
                       onclick="trackRadarEvent({event_type:'click_cta', source:'hero_secondary', cta_label:'Empezar hoy — 79€/mes', url:'/checkout/radar-export?type=subscription&plan=radar&source=hero_secondary'})">
                        Empezar hoy — 79€/mes
                    </a>
                </div>

                <p class="radar-hero__note">Sin permanencia · Cancela cuando quieras · ⚡ 1 cliente lo paga todo</p>
            </div>
        </div>
    </section>

    <!-- Prueba social ──────────────────────────────────────── -->
    <div style="background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 0;text-align:center;">
        <div class="container">
            <p style="margin:0;font-size:.82rem;font-weight:700;color:#64748b;">Usado por <strong style="color:#0f172a;">agencias, SaaS y asesorías</strong> para generar clientes nuevos cada semana &mdash; sin inversión en publicidad</p>
        </div>
    </div>


    <!-- ② PIPELINE STATS ────────────────────────────── -->
    <section class="pipeline-band">
        <div class="container">

            <!-- Headline -->
            <div class="pipeline-headline">
                <div class="pipeline-live-tag">
                    <span class="pipeline-live-dot"></span>
                    Pipeline activo · Actualizado en tiempo real
                </div>
                <h2>Este es el dinero que hay disponible <em style="font-style:normal;background:linear-gradient(90deg,#1e3a8a,#2563eb);-webkit-background-clip:text;background-clip:text;color:transparent;">hoy</em></h2>
                <p>Empresas recién creadas con alta probabilidad de necesitar servicios B2B. Detectadas automáticamente.</p>
            </div>

            <!-- 3 tarjetas -->
            <div class="pipeline-cards">

                <div class="pipeline-card pipeline-card--blue">
                    <div class="pipeline-card__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label">Leads detectados</div>
                    <div class="pipeline-card__num"><?= $opps_count ?></div>
                    <div class="pipeline-card__desc">Empresas nuevas constituidas hoy con señales de compra activas en su sector</div>
                </div>

                <div class="pipeline-card pipeline-card--green">
                    <div class="pipeline-card__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label">Clientes reales estimados</div>
                    <div class="pipeline-card__num"><?= $metrics['clients_min'] ?> – <?= $metrics['clients_max'] ?></div>
                    <div class="pipeline-card__desc">Con una tasa de cierre conservadora del 8–15%, este es el volumen alcanzable este mes</div>
                </div>

                <div class="pipeline-card pipeline-card--amber">
                    <div class="pipeline-card__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="pipeline-card__label">💰 Pipeline en euros</div>
                    <div class="pipeline-card__num" style="font-size:clamp(2.6rem,5vw,3.8rem);color:#15803d;"><?= number_format($metrics['pipeline_min'] / 1000000, 1, ',', '.') ?>M€</div>
                    <div class="pipeline-card__sub">hasta <strong style="color:#15803d;"><?= number_format($metrics['pipeline_max'] / 1000000, 1, ',', '.') ?>M€</strong></div>
                    <div class="pipeline-card__desc">Estimado con tickets de 5.000€ – 12.000€ por cliente cerrado en primer contrato</div>
                </div>

            </div>

            <!-- ROI callout -->
            <div class="pipeline-roi">
                <div class="pipeline-roi__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span class="pipeline-roi__text">
                    <strong>💰 Estas son oportunidades que puedes convertir en clientes tú mismo.</strong> 👉 Solo necesitas 1 cliente para rentabilizar el Radar — un cliente de 5.000€ cubre más de 5 años.
                </span>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=pipeline') ?>" 
                   class="pipeline-roi__cta"
                   onclick="trackRadarEvent({event_type:'click_cta', source:'pipeline', cta_label:'Empezar hoy — 79€/mes', url:'/checkout/radar-export?type=subscription&plan=radar&source=pipeline'})">
                    Empezar hoy — 79€/mes
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;font-weight:600;">Sin permanencia · Cancela cuando quieras</p>
            </div>

        </div>
    </section>


    <!-- ③ TABLA DEMO ─────────────────────────────────── -->
    <section class="radar-section" id="demo-table">
        <div class="container">

            <div style="margin-bottom:24px;">
                <div class="radar-kicker" style="margin-bottom:12px;">🔥 Oportunidades detectadas hoy · Selección curada</div>
                <h2 class="radar-title" style="margin:0 0 6px;">Clientes disponibles ahora</h2>
                <p class="radar-subtitle" style="margin:0 0 16px;">Empresas recién creadas con alta probabilidad de necesitar tus servicios.</p>

                <!-- Mini wow teaser -->
                <div style="display:inline-flex;align-items:center;gap:16px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 20px;box-shadow:0 2px 12px rgba(15,23,42,.06);margin-bottom:20px;flex-wrap:wrap;">
                    <span style="font-size:.82rem;font-weight:700;color:#1e293b;">🧠 Para cada empresa Radar te dice exactamente:</span>
                    <span style="font-size:.8rem;color:#64748b;">Qué venderle · Cómo enfocarlo · Qué decirle</span>
                    <a href="#wow-moment" onclick="document.getElementById('wow-moment').scrollIntoView({behavior:'smooth'});return false;"
                       style="font-size:.8rem;font-weight:800;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;padding:6px 14px;border-radius:8px;text-decoration:none;white-space:nowrap;">Ver ejemplo →</a>
                </div>

                <p style="font-size:.85rem;font-weight:700;color:#b45309;background:#fffbeb;border:1px solid #fde68a;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;">⚡ Las mejores oportunidades se saturan en los primeros días — actúa antes que tu competencia</p>
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

                <table class="radar-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Ticket Est.</th>
                            <th>Prioridad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $i => $co): ?>
                        <tr>
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

                <div class="radar-preview__cta">
                    <p style="margin:0 0 14px;font-size:.9rem;color:#64748b;font-weight:600;">
                        Con el Radar completo accedes a <strong>todas las oportunidades del día</strong>, filtros avanzados y exportación a CRM.
                    </p>
                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=tabla') ?>" 
                       class="radar-btn radar-btn--primary"
                       onclick="trackRadarEvent({event_type:'click_cta', source:'tabla_bottom', cta_label:'Ver clientes disponibles ahora', url:'/checkout/radar-export?type=subscription&plan=radar&source=tabla'})">
                        Ver clientes disponibles ahora →
                    </a>
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
                    <span style="font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;padding:5px 12px;border-radius:999px;">🧠 Ejemplo real de estrategia</span>
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
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:8px;">🎯 Por qué es oportunidad</div>
                            <p style="font-size:.88rem;color:#334155;margin:0;line-height:1.6;"><?= esc($wow['strategy']['motivo'] ?? 'Empresa recién creada con necesidad inmediata de servicios B2B.') ?></p>
                        </div>
                        <div style="background:#f0f7ff;border-radius:12px;padding:16px;">
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#1e40af;margin-bottom:8px;">🚀 Qué venderle primero</div>
                            <p style="font-size:.88rem;color:#1e3a8a;font-weight:700;margin:0;line-height:1.6;"><?= esc($wow['strategy']['que_vender'] ?? '') ?></p>
                        </div>
                    </div>

                    <div style="padding:0 28px 24px;">
                        <div style="background:#f1f5f9;border-radius:12px;border:2px dashed #2563eb;padding:18px;">
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563eb;margin-bottom:8px;">✉ Mensaje inicial sugerido</div>
                            <p style="font-size:.9rem;line-height:1.7;color:#1e293b;margin:0 0 14px;font-style:italic;">"<?= esc($wow['strategy']['mensaje'] ?? '') ?>"</p>
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                                <span style="font-size:.78rem;color:#94a3b8;font-weight:600;">⚠️ Con el Radar completo recibes nombre, teléfono y correo del administrador</span>
                                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=estrategia') ?>" 
                                   class="radar-btn radar-btn--primary" 
                                   style="font-size:.85rem;padding:10px 20px;"
                                   onclick="trackRadarEvent({event_type:'click_cta', source:'estrategia', cta_label:'Ver clientes disponibles ahora', url:'/checkout/radar-export?type=subscription&plan=radar&source=estrategia'})">
                                    Ver clientes disponibles ahora →
                                </a>
                            </div>
                        </div>
                        <p style="margin:16px 0 0;font-size:.82rem;font-weight:700;color:#2563eb;text-align:center;">👉 Puedes copiar este mensaje y empezar a contactar hoy mismo</p>
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
                <div class="radar-kicker">Cómo funciona Radar</div>
                <h2 class="radar-title" style="margin-top:8px;font-size:1.4rem;">5 pasos, cero trabajo manual</h2>
                <p class="radar-subtitle" style="font-size:.9rem;">El sistema trabaja solo — tú solo cierras ventas</p>
            </div>

            <div class="steps-grid">

                <!-- 1 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--1">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 01</div>
                    <h3>Detecta</h3>
                    <p>Escanea el BORME cada día y captura cada empresa nueva constituida en España</p>
                </div>

                <!-- 2 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 02</div>
                    <h3>Analiza</h3>
                    <p>Evalúa sector, capital y objeto social buscando señales reales de necesidad</p>
                </div>

                <!-- 3 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--3">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 03</div>
                    <h3>Estima</h3>
                    <p>Calcula el ticket potencial y asigna un nivel de prioridad comercial automatizado</p>
                </div>

                <!-- 4 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--4">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 04</div>
                    <h3>Genera</h3>
                    <p>La IA crea una estrategia de venta personalizada: qué decir, cómo y cuándo</p>
                </div>

                <!-- 5 -->
                <div class="step-card">
                    <div class="step-icon-wrap step-icon-wrap--5">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9.81a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <div class="step-label">Paso 05</div>
                    <h3>Entrega</h3>
                    <p>Recibes el mensaje listo para copiar, pegar y cerrar. Sin más pasos</p>
                </div>

            </div>
        </div>
    </section>


    <!-- ⑤ CTA MEGA ───────────────────────────────────── -->
    <section class="cta-dark">
        <div class="container">
            <div class="cta-dark__card">

                <!-- Patrón de puntos decorativo -->
                <div class="cta-dark__dots"></div>

                <!-- Columna izquierda: copy + botones -->
                <div class="cta-dark__left">
                    <div class="cta-dark__kicker">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Activa tu acceso hoy
                    </div>
                    <h2>Consigue clientes mientras<br>duermes por 79€ al mes</h2>
                    <p>Accede al pipeline completo de oportunidades B2B y empieza a contactar empresas nuevas antes que tu competencia. Cada día sin Radar son clientes que se llevan otros.</p>
                    <div class="cta-dark__actions">
                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=pricing') ?>" 
                           class="radar-btn radar-btn--white" 
                           style="font-size:1rem;padding:15px 28px;"
                           onclick="trackEvent({event_type:'click_cta', source:'pricing', cta_label:'Ver clientes disponibles ahora', url:'/checkout/radar-export?type=subscription&plan=radar&source=pricing'})">
                            🚀 Ver clientes disponibles ahora
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <p class="cta-dark__note">Sin permanencia · Cancela cuando quieras · Soporte incluido · 👉 Menos que el coste de un solo lead en Google Ads</p>
                </div>

                <!-- Columna derecha: caja de precio -->
                <div class="cta-dark__right">
                    <div class="cta-price-box">
                        <div class="cta-price-box__label">Acceso completo</div>
                        <div class="cta-price-box__price">79€</div>
                        <div class="cta-price-box__period">al mes, sin permanencia</div>
                        <div class="cta-trust-items">
                            <div class="cta-trust-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Pipeline diario actualizado
                            </div>
                            <div class="cta-trust-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Estrategia de venta con IA
                            </div>
                            <div class="cta-trust-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Datos de contacto del admin.
                            </div>
                            <div class="cta-trust-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Cancela cuando quieras
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ⑥ CIERRE ─────────────────────────────────────── -->
    <section class="closing">
        <div class="closing-dots"></div>
        <div class="closing-bottom-glow"></div>
        <div class="container closing-inner">

            <!-- Badge urgencia -->
            <div class="closing-badge">
                <span class="closing-badge-dot"></span>
                👆 Empieza hoy — Las oportunidades de hoy no estarán mañana
            </div>

            <!-- Cita principal -->
            <p class="closing__quote">
                "Esto no es un listado de empresas.<br>
                Es un sistema para <span class="grad">generar clientes nuevos</span> cada semana."
            </p>

            <p class="closing__sub">Detectamos oportunidades B2B activas a diario para que tú solo te preocupes de cerrar ventas.</p>

            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;margin-bottom:32px;">
                <p style="margin:0;font-size:.9rem;font-weight:700;color:#15803d;">👉 Solo necesitas 1 cliente para recuperar la inversión</p>
                <p style="margin:0;font-size:.82rem;font-weight:600;color:#b45309;">⚠ Las mejores oportunidades se saturan en los primeros días</p>
            </div>
            <div class="closing-stats">
                <div class="closing-stat">
                    <div class="closing-stat__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <span class="closing-stat__num"><?= $opps_count ?>+</span>
                    <span class="closing-stat__lbl">Oportunidades hoy</span>
                </div>
                <div class="closing-stat">
                    <div class="closing-stat__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <span class="closing-stat__num">79€</span>
                    <span class="closing-stat__lbl">Al mes</span>
                </div>
                <div class="closing-stat">
                    <div class="closing-stat__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <span class="closing-stat__num">1</span>
                    <span class="closing-stat__lbl">Cliente lo paga todo</span>
                </div>
                <div class="closing-stat">
                    <div class="closing-stat__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <span class="closing-stat__num">0</span>
                    <span class="closing-stat__lbl">Trabajo manual</span>
                </div>
            </div>

            <!-- CTA -->
            <div class="closing-cta">
                <p style="margin:0 0 16px;font-size:.85rem;font-weight:700;color:#b45309;">⚠ Las mejores oportunidades se saturan en los primeros días</p>
                <a href="<?= site_url('register?source=cta_final') ?>" 
                   class="radar-btn radar-btn--primary" 
                   style="font-size:1.05rem;padding:18px 48px;box-shadow:0 0 40px rgba(37,99,235,.45);"
                   onclick="trackEvent({event_type:'click_cta', source:'cta_final', cta_label:'Ver clientes disponibles ahora', url:'/register?source=cta_final'})">
                    🚀 Ver clientes disponibles ahora
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>

                <div class="closing-trust">
                    <span class="closing-trust-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Sin permanencia
                    </span>
                    <span class="closing-trust-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Cancela cuando quieras
                    </span>
                    <span class="closing-trust-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        1 cliente lo paga todo
                    </span>
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