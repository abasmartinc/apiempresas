<?php
  $title = "API Autocompletado de Empresas por CIF y Nombre | API Empresas";
  $excerptText = "API profesional de autocompletado de empresas en España. Busca por CIF o Nombre y obtén sugerencias en tiempo real con datos oficiales (BOE, BORME, AEAT). Ideal para onboarding B2B.";
?>
<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head', ['title' => $title, 'excerptText' => $excerptText]) ?>
    <style>
        :root{
            --bg: #ffffff;
            --surface: rgba(255,255,255,.78);
            --surface-solid: #ffffff;
            --border: rgba(15, 23, 42, .08);
            --border-strong: rgba(15, 23, 42, .14);

            --text: #0f172a;
            --muted: #64748b;

            /* Usa tu primary existente (var(--primary)) */
            --ring: rgba(33, 82, 255, .24);
            --shadow: 0 30px 80px rgba(2, 6, 23, .10);
            --shadow-soft: 0 18px 50px rgba(2, 6, 23, .08);

            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 18px;

            --glass: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.38);
            --accent-soft: rgba(33, 82, 255, 0.06);
        }

        body{ background: var(--bg); color: var(--text); }

        /* Background halo + grid */
        .bg-halo{
            position: fixed;
            inset: -40vh -20vw auto -20vw;
            height: 70vh;
            pointer-events: none;
            background:
                radial-gradient(closest-side, rgba(33,82,255,.18), transparent 70%),
                radial-gradient(closest-side, rgba(18,180,138,.16), transparent 72%);
            filter: blur(10px);
            opacity: .85;
            z-index: 0;
        }
        .bg-grid{
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(to right, rgba(15,23,42,.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15,23,42,.05) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(closest-side, rgba(0,0,0,.75), transparent 85%);
            opacity: .28;
        }

        /* Unified Animations (clean + consistent) */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .animate-up{ opacity: 0; animation: fadeInUp .75s cubic-bezier(.16,1,.3,1) forwards; }
        .delay-1{ animation-delay: .08s; }
        .delay-2{ animation-delay: .16s; }
        .delay-3{ animation-delay: .24s; }

        @media (prefers-reduced-motion: reduce){
            .animate-up{ animation: none; opacity: 1; transform: none; }
        }

        /* Layout helpers */
        .page-header-offset{ height: 92px; }
        .section-padding{ padding: 110px 0; }
        .bg-light{ background: #f8fafc; }
        .bg-gradient-soft{ background: linear-gradient(180deg, #ffffff 0%, #f2f7ff 52%, #ffffff 100%); }

        /* Hero */
        .hero-wrap{
            position: relative;
            z-index: 2;
            padding: 78px 0 56px;
            overflow: hidden;
        }
        .hero-card{
            max-width: 1120px;
            margin: 0 auto;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.62));
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-soft);
            padding: clamp(28px, 4vw, 52px);
            position: relative;
        }
        .hero-card:before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: linear-gradient(90deg, rgba(33,82,255,.22), rgba(18,180,138,.16), rgba(33,82,255,.18));
            filter: blur(18px);
            opacity: .45;
            z-index: -1;
        }
        .hero-title{
            font-size: clamp(40px, 6.6vw, 58px);
            letter-spacing: -0.035em;
            line-height: 1.02;
            font-weight: 950;
            margin: 16px 0 18px;
        }
        .hero-subtitle{
            font-size: clamp(18px, 2.2vw, 22px);
            max-width: 860px;
            color: var(--muted);
            line-height: 1.55;
            margin: 0 auto 26px;
        }

        /* Pill / Eyebrow polish */
        .pill{
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 13px;
            border: 1px solid rgba(33,82,255,.16);
            background: rgba(33, 82, 255, 0.06);
            color: var(--primary);
        }
        .pill .dot{
            width: 8px; height: 8px; border-radius: 99px;
            background: linear-gradient(135deg, rgba(33,82,255,1), rgba(18,180,138,1));
            box-shadow: 0 0 0 5px rgba(33,82,255,.10);
        }
        .eyebrow{
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #334155;
            opacity: .85;
        }
        .eyebrow:before{
            content:"";
            width: 18px; height: 2px;
            border-radius: 99px;
            background: linear-gradient(90deg, rgba(33,82,255,1), rgba(18,180,138,1));
        }

        /* Feature tags refined */
        .feature-row{
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap: 10px;
            margin: 20px 0 0;
        }
        .feature-tag{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            background: rgba(15,23,42,.04);
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 850;
            font-size: 13px;
            color: #334155;
            border: 1px solid rgba(15,23,42,.06);
        }
        .feature-tag svg{ color: #10b981; }

        /* Live demo: search "command" box */
        .demo-shell{
            max-width: 980px;
            margin: 0 auto;
        }
        .search-outer{
            margin: 0 auto;
            background: #ffffff;
            border-radius: 26px;
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.06);
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .search-outer:before{
            content:"";
            position:absolute;
            inset: 0;
            background: radial-gradient(600px 220px at 20% -20%, rgba(33,82,255,.05), transparent 70%),
                        radial-gradient(520px 220px at 90% 10%, rgba(18,180,138,.04), transparent 60%);
            pointer-events:none;
            border-radius: inherit;
        }
        .search-inner{
            position: relative;
            display:flex;
            align-items:center;
            gap: 12px;
            padding: 12px;
        }
        .search-icon{
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display:grid;
            place-items:center;
            background: rgba(33,82,255,.08);
            border: 1px solid rgba(33,82,255,.12);
            flex: 0 0 auto;
        }
        .search-icon svg{ width: 20px; height: 20px; color: var(--primary); }

        #company-search{
            width: 100%;
            padding: 18px 14px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 18px;
            border: none;
            outline: none;
            background: transparent;
            color: var(--text);
        }
        #company-search::placeholder{ color: rgba(100,116,139,.9); font-weight: 650; }

        .search-hints{
            display:flex;
            gap: 8px;
            align-items:center;
            flex: 0 0 auto;
            padding-right: 6px;
        }
        .kbd{
            font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            background: rgba(15,23,42,.04);
            border: 1px solid rgba(15,23,42,.10);
            border-bottom-width: 2px;
            padding: 6px 10px;
            border-radius: 10px;
            user-select: none;
        }
        .hint-text{
            font-size: 12px;
            font-weight: 750;
            color: #64748b;
            white-space: nowrap;
        }

        .search-outer:focus-within{
            border-color: rgba(33,82,255,.28);
            box-shadow: 0 44px 120px rgba(2,6,23,.12), 0 0 0 8px var(--ring);
        }

        /* Dropdown */
        .suggestions-dropdown{
            position:absolute;
            top: 100%;
            left: 12px;
            right: 12px;
            background: rgba(255,255,255,.95);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(2,6,23,.14);
            z-index: 999;
            max-height: 420px;
            overflow-y: auto;
            display:none;
            border: 1px solid rgba(15,23,42,.08);
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }
        .suggestions-dropdown.active{ display:block; }

        .suggestion-item{
            padding: 16px 16px;
            cursor:pointer;
            border-bottom: 1px solid rgba(15,23,42,.06);
            transition: transform .18s cubic-bezier(.16,1,.3,1), background .18s ease;
            display:flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }
        .suggestion-item:last-child{ border-bottom: none; }
        .suggestion-item:hover{
            background: rgba(33,82,255,.06);
            transform: translateY(-1px);
        }
        .company-name{
            font-weight: 900;
            color: var(--text);
            font-size: 14px;
            letter-spacing: -0.01em;
        }
        .company-addr{
            display:block;
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.35;
        }
        .company-cif{
            font-family: "JetBrains Mono", monospace;
            background: rgba(33,82,255,.08);
            color: #1e40af;
            padding: 7px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 900;
            border: 1px solid rgba(33,82,255,.14);
            flex: 0 0 auto;
        }

        /* Loader */
        @keyframes spin{ to{ transform: rotate(360deg); } }
        .loader{
            position:absolute;
            right: 18px;
            top: 50%;
            margin-top: -13px;
            width: 26px;
            height: 26px;
            border: 3px solid rgba(15,23,42,.10);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .75s linear infinite;
            display:none;
            z-index: 2;
        }

        /* Tech window polish - LIGHT PREMIUM */
        .tech-window{
            background: #ffffff;
            border-radius: 24px;
            overflow:hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .tech-top{
            background: #f8fafc;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            display:flex;
            align-items:center;
            gap: 12px;
        }
        .dots{
            display:flex; gap: 7px; align-items:center;
        }
        .dots i{
            width: 10px; height: 10px; border-radius: 99px; display:inline-block;
            background: rgba(255,255,255,.18);
        }
        .dots i:nth-child(1){ background: rgba(239,68,68,.75); }
        .dots i:nth-child(2){ background: rgba(245,158,11,.75); }
        .dots i:nth-child(3){ background: rgba(34,197,94,.75); }

        /* Use cases */
        .use-case-card{
            background: #ffffff;
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow:hidden;
        }
        .use-case-card:before{
            content:"";
            position:absolute;
            inset: -2px;
            background: radial-gradient(420px 140px at 20% 0%, rgba(33,82,255,.14), transparent 60%),
                        radial-gradient(420px 140px at 90% 10%, rgba(18,180,138,.12), transparent 60%);
            opacity: 0;
            transition: opacity .25s ease;
            pointer-events:none;
        }
        .use-case-card:hover{
            transform: translateY(-8px);
            box-shadow: 0 26px 60px rgba(2,6,23,.10);
            border-color: rgba(33,82,255,.20);
        }
        .use-case-card:hover:before{ opacity: 1; }

        /* Developer section cards */
        .mini-card{
            background: rgba(255,255,255,.9);
            padding: 22px;
            border-radius: 18px;
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: 0 10px 24px rgba(2,6,23,.05);
        }

        /* CTA section */
        .cta{
            position: relative;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 70%);
            border-top: 1px solid rgba(15,23,42,.08);
            padding: 120px 0;
            overflow: hidden;
        }
        .cta:before{
            content:"";
            position:absolute;
            inset: -40% -10% auto -10%;
            height: 70%;
            background: radial-gradient(closest-side, rgba(33,82,255,.18), transparent 70%),
                        radial-gradient(closest-side, rgba(18,180,138,.14), transparent 72%);
            filter: blur(18px);
            opacity: .75;
            pointer-events:none;
        }

        /* Utilities */
        .muted{ color: var(--muted); }
        .is-hidden{ display:none !important; }

        /* HERO ADMIN (sin card) */
.hero-admin{
  position: relative;
  padding: 70px 0 40px;
  z-index: 2;
}

.hero-admin-grid{
  display: grid;
  grid-template-columns: 1.05fr .95fr;
  gap: 52px;
  align-items: center;
  position: relative;
}

@media (max-width: 980px){
  .hero-admin-grid{ grid-template-columns: 1fr; gap: 24px; }
}

.hero-admin-grid:before{
  content:"";
  position:absolute;
  inset: -60px -20px -60px -20px;
  background:
    radial-gradient(700px 260px at 15% 20%, rgba(33,82,255,.18), transparent 60%),
    radial-gradient(640px 260px at 85% 0%, rgba(18,180,138,.14), transparent 58%);
  filter: blur(16px);
  opacity: .75;
  pointer-events:none;
  z-index: -1;
}

.hero-copy{
  max-width: 720px;
}

.hero-admin-title{
  font-size: clamp(40px, 6vw, 58px);
  letter-spacing: -0.04em;
  line-height: 1.02;
  font-weight: 950;
  margin: 14px 0 14px;
}

.hero-admin-subtitle{
  font-size: clamp(18px, 2.2vw, 20px);
  color: var(--muted);
  line-height: 1.6;
  margin: 0 0 18px;
}

.hero-bullets{
  display: grid;
  gap: 12px;
  margin-top: 18px;
}

.hero-bullet{
  display:flex;
  gap: 12px;
  align-items:flex-start;
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid rgba(15,23,42,.08);
  background: rgba(255,255,255,.65);
  backdrop-filter: blur(10px);
}

.bullet-dot{
  width: 10px;
  height: 10px;
  border-radius: 99px;
  margin-top: 6px;
  background: linear-gradient(135deg, rgba(33,82,255,1), rgba(18,180,138,1));
  box-shadow: 0 0 0 6px rgba(33,82,255,.10);
  flex: 0 0 auto;
}

.hero-bullet strong{
  display:block;
  font-weight: 950;
  letter-spacing: -0.01em;
}

.hero-bullet .muted{
  display:block;
  font-size: 13px;
  margin-top: 2px;
}

/* Demo panel (derecha) */
.hero-demo{
  border-radius: 22px;
  border: 1px solid rgba(15,23,42,.10);
  background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.60));
  backdrop-filter: blur(12px);
  box-shadow: 0 28px 80px rgba(2,6,23,.10);
}

.hero-demo-top{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(15,23,42,.08);
  background: rgba(15,23,42,.02);
}

.hero-demo-label{
  font-size: 12px;
  font-weight: 950;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: #0f172a;
  opacity: .85;
}

.hero-demo-meta{
  margin-left:auto;
  font-size: 12px;
  font-weight: 850;
  color: #64748b;
}

.hero-demo-body{
  padding: 14px 14px 12px;
}

.hero-demo-footer{
  display:flex;
  flex-wrap:wrap;
  gap: 14px;
  padding: 12px 2px 2px;
  opacity: .9;
}

/* Ajuste visual: tu search-outer aquí no necesita el mega shadow */
.hero-demo .search-outer:focus-within{
  box-shadow: 0 0 0 7px var(--ring);
}

/* HERO TOP (sin demo) */
.hero-admin--top{
  padding: 72px 0 34px;
}

.hero-top{
  display:grid;
  grid-template-columns: 1.1fr .9fr;
  gap: 44px;
  align-items:center;
  position: relative;
}

@media (max-width: 980px){
  .hero-top{ grid-template-columns: 1fr; gap: 22px; }
}

.hero-top:before{
  content:"";
  position:absolute;
  inset: -70px -20px -60px -20px;
  background:
    radial-gradient(760px 280px at 18% 20%, rgba(33,82,255,.18), transparent 60%),
    radial-gradient(680px 260px at 86% 0%, rgba(18,180,138,.14), transparent 58%);
  filter: blur(16px);
  opacity: .75;
  pointer-events:none;
  z-index:-1;
}

.hero-top-title{
  font-size: clamp(40px, 6vw, 60px);
  letter-spacing: -0.04em;
  line-height: 1.02;
  font-weight: 950;
  margin: 14px 0 14px;
}

.hero-top-subtitle{
  font-size: clamp(18px, 2.1vw, 20px);
  color: var(--muted);
  line-height: 1.6;
  margin: 0 0 18px;
  max-width: 720px;
}

.hero-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-top: 24px;
    max-width: 650px;
}

.kpi {
    padding: 16px 8px;
    border-radius: 20px;
    border: 1px solid rgba(33, 82, 255, 0.08); /* Subtle blue border */
    background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%); /* Subtle blueish tint at bottom */
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 0;
}

.kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(33, 82, 255, 0.08);
    border-color: rgba(33, 82, 255, 0.25);
    background: #ffffff;
}

.kpi-value {
    font-weight: 950;
    letter-spacing: -0.03em;
    font-size: 26px;
    background: linear-gradient(135deg, #2152ff 0%, #1d4ed8 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
    line-height: 1;
    margin-bottom: 2px;
}

.kpi-label {
    font-size: 10px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.1;
}

        .kpi:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2152ff, #12b48a);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .kpi:hover:before {
            opacity: 1;
        }

.hero-cta-row{
  display:flex;
  flex-wrap:wrap;
  gap: 12px;
  margin-top: 18px;
  align-items:center;
}

.hero-cta-primary{
  padding: 16px 26px !important;
  border-radius: 16px !important;
  font-weight: 950 !important;
  box-shadow: 0 18px 42px rgba(33,82,255,.22);
}

.hero-cta-ghost{
  padding: 16px 22px !important;
  border-radius: 16px !important;
  font-weight: 950 !important;
  background: rgba(255,255,255,.9) !important;
  border: 2px solid rgba(19,58,130,.12) !important;
}

.hero-trust{
  margin-top: 14px;
  display:flex;
  flex-wrap:wrap;
  gap: 14px;
  color:#475569;
  font-weight: 850;
  opacity: .85;
  font-size: 13px;
}

/* Right preview surface */
.hero-surface{
  border-radius: 24px;
  border: 1px solid rgba(15,23,42,.08);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
  padding: 16px;
  overflow:hidden;
  position: relative;
}

.hero-surface:before{
  content:"";
  position:absolute;
  inset:-2px;
  background:
    radial-gradient(520px 220px at 10% 0%, rgba(33,82,255,.16), transparent 60%),
    radial-gradient(520px 220px at 92% 10%, rgba(18,180,138,.12), transparent 60%);
  opacity:.9;
  pointer-events:none;
}

.hero-surface > *{ position: relative; z-index: 1; }

.hero-surface-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding: 6px 6px 10px;
}

.hero-surface-chip{
  font-size: 11px;
  font-weight: 950;
  letter-spacing: .12em;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(15,23,42,.04);
  color:#0f172a;
}

.hero-flow{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 12px;
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,.08);
  background: rgba(255,255,255,.70);
}

@media (max-width: 980px){
  .hero-flow{ flex-direction: column; align-items: stretch; }
  .flow-arrow{ display:none; }
}

.flow-step{
  display:flex;
  gap: 10px;
  align-items:center;
  flex: 1;
}

.flow-icon{
  width: 36px;
  height: 36px;
  border-radius: 14px;
  display:grid;
  place-items:center;
  background: rgba(33,82,255,.08);
  border: 1px solid rgba(33,82,255,.12);
  font-size: 16px;
}

.flow-step strong{
  display:block;
  font-weight: 950;
  letter-spacing: -0.01em;
}

.flow-step .muted{
  display:block;
  font-size: 12px;
  margin-top: 2px;
}

.flow-arrow{
  font-weight: 950;
  color: #64748b;
  opacity: .8;
}

.hero-mini-code{
  margin-top: 12px;
  border-radius: 18px;
  overflow:hidden;
  border: 1px solid rgba(15,23,42,.10);
  background: #0b1220;
}

.hero-mini-code-top{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 10px 12px;
  border-bottom: 1px solid rgba(255,255,255,.06);
  background: rgba(255,255,255,.06);
}

.hero-mini-code-label{
  font-size: 11px;
  font-weight: 900;
  color: rgba(226,232,240,.8);
  margin-left: 6px;
}

.hero-surface-note{
  margin-top: 10px;
  text-align:center;
  font-weight: 900;
  color: #334155;
  opacity: .85;
  font-size: 13px;
}

.search-outer::before{
    border-radius: 25px;;
}

/* Mobile Optimizations */
@media (max-width: 980px) {
    /* Better spacing for hero */
    .hero-wrap { padding: 40px 0 20px; }
    
    /* Search Bar Fixes */
    .search-hints { display: none !important; }
    .search-inner { padding: 8px 10px !important; gap: 8px; }
    .search-icon { width: 36px; height: 36px; }
    .search-icon svg { width: 18px; height: 18px; }
    
    #company-search {
        font-size: 16px !important; /* Prevents auto-zoom on iOS */
        padding: 10px 4px !important;
        height: auto;
    }
    
    /* KPI adjustments */
    .hero-kpis { gap: 8px; }
    .kpi { padding: 12px 6px; }
    .kpi-value { font-size: 20px; }
    .kpi-label { font-size: 9px; }
    
    /* Grid fixes */
    .hero-top-title { font-size: 32px; }
    .hero-top-subtitle { font-size: 16px; }
    
    /* Ensure dropdown fits */
    .suggestions-dropdown {
        left: 0; right: 0;
        border-radius: 12px;
    }
    
    /* Adjust feature tags */
    .feature-row { gap: 8px; }
    .feature-tag { font-size: 11px; padding: 8px 10px; }
    
    .dev-grid { 
        display: block !important; /* Switch to block to avoid grid spacing issues completely on mobile */
    }
    
    .dev-grid > * {
        margin-bottom: 40px;
        min-width: 0; /* Important for flex/grid children shrinking */
    }
    
    .dev-grid > *:last-child {
        margin-bottom: 0;
    }

    .dev-wrapper {
        padding: 40px 20px !important;
        border-radius: 20px !important;
        overflow: hidden; /* Prevent spill */
        width: 100% !important;
        box-sizing: border-box; /* Ensure padding doesn't add to width */
    }

    .mini-cards-grid {
        grid-template-columns: 1fr !important;
    }
    
    /* Ensure code block doesn't break layout */
    .tech-window {
        max-width: 100% !important;
        width: 100% !important;
        overflow-x: hidden; /* Let the pre handle scroll */
    }
    
    .tech-window pre {
        max-width: 100%;
        overflow-x: auto;
    }

    /* Force text wrapping */
    h2 {
        word-break: break-word;
        hyphens: auto;
    }
}

.dev-grid {
    display: grid;
    grid-template-columns: 1fr 1.25fr;
    gap: 64px;
    align-items: center;
}

.dev-wrapper {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%); 
    border: 1px solid #e2e8f0; 
    border-radius: 40px; 
    padding: 60px; 
    box-shadow: 0 40px 100px -20px rgba(15, 23, 42, 0.05);
}

.mini-cards-grid {
    display:grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 14px;
}


    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?=view('partials/header') ?>

<main style="position: relative; z-index: 1;">

<!-- HERO -->
<section class="hero-wrap hero-admin hero-admin--top">
  <div class="container">
    <div class="hero-top animate-up">

      <div class="hero-top-left">
        <span class="pill">
          <span class="dot"></span>
          Integración en &lt; 10 minutos
        </span>

        <h1 class="hero-top-title">
          Autocompleta empresas por CIF
          <span class="grad">con datos oficiales</span>.
        </h1>

        <p class="hero-top-subtitle">
          Evita registros erróneos, facturas fallidas y duplicados. Conecta tu formulario o CRM y obtén datos fiscales listos para usar en segundos.
        </p>

        <div class="hero-kpis">
          <div class="kpi">
            <div class="kpi-value">1</div>
            <div class="kpi-label">Llamada API</div>
          </div>
          <div class="kpi">
            <div class="kpi-value">250ms</div>
            <div class="kpi-label">Respuesta</div>
          </div>
          <div class="kpi">
            <div class="kpi-value">Auto</div>
            <div class="kpi-label">Formularios</div>
          </div>
        </div>

        <div class="feature-row" style="justify-content:flex-start; margin-top: 18px;">
          <div class="feature-tag">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            Razón Social
          </div>
          <div class="feature-tag">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            Dirección Fiscal
          </div>
          <div class="feature-tag">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            Estado / VIES / CNAE
          </div>
        </div>

        <div class="hero-cta-row">
          <a href="#demo" class="btn primary hero-cta-primary">👇 Ver demo</a>
          <a href="<?=site_url('documentation') ?>" class="btn ghost hero-cta-ghost">Ver documentación</a>
        </div>

        <div class="hero-trust">
          <span>✔ Sin llamadas comerciales</span>
          <span>✔ Sin contratos</span>
          <span>✔ Sin demos obligatorias</span>
        </div>
      </div>

      <div class="hero-top-right">
        <div class="hero-surface">
          <div class="hero-surface-top">
            <span class="eyebrow">Vista previa</span>
            <span class="hero-surface-chip">ADMIN</span>
          </div>

          <div class="hero-flow">
            <div class="flow-step">
              <div class="flow-icon">⌨️</div>
              <div>
                <strong>Input</strong>
                <span class="muted">CIF / Razón social</span>
              </div>
            </div>
            <div class="flow-arrow">→</div>
            <div class="flow-step">
              <div class="flow-icon" style="position: relative;">
                ✨
                <div style="position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulse-green 2s infinite;"></div>
              </div>
              <div>
                <strong>Sugerencias</strong>
                <span class="muted">Matching oficial</span>
              </div>
            </div>
            <div class="flow-arrow">→</div>
            <div class="flow-step">
              <div class="flow-icon">📦</div>
              <div>
                <strong>JSON</strong>
                <span class="muted">Listo para tu CRM</span>
              </div>
            </div>
          </div>

          <div class="hero-mini-code" style="background: #ffffff; border: 1px solid #e2e8f0;">
            <div class="hero-mini-code-top" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
              <div class="dots" aria-hidden="true"><i></i><i></i><i></i></div>
              <span class="hero-mini-code-label" style="color: #64748b; font-weight: 700;">GET /v1/company?cif=B12345678</span>
            </div>
            <pre style="margin:0; padding: 18px; font-size: 13px; line-height: 1.6; color:#334155; font-family: \'JetBrains Mono\', monospace; background: transparent; overflow-x:auto;"><code>{
  "success": <span style="color: #12b48a;">true</span>,
  "data": { 
    "name": "<span style="color: #2152ff;">EMPRESA EJEMPLO SL</span>", 
    "cif": "<span style="color: #2152ff;">B12345678</span>" 
  }
}</code></pre>
          </div>

          <div class="hero-surface-note">
            Baja para probar el autocompletado en vivo 👇
          </div>
        </div>
      </div>

    </div>
  </div>
</section>



<!-- LIVE DEMO -->
<section id="demo" class="section-padding" style="position: relative; z-index: 10; background: #ffffff;">
    <!-- Centered Radial Glow -->
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 1000px; height: 600px; background: radial-gradient(circle, rgba(33, 82, 255, 0.04) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align:center; margin-bottom: 46px;" class="animate-up delay-1">
            <span class="eyebrow">Demo en vivo</span>
            <h2 style="font-size: clamp(30px, 4.2vw, 46px); font-weight: 950; margin: 12px 0 10px; letter-spacing: -0.03em;">
                Siéntelo en tus <span class="grad">propias manos</span>.
            </h2>
            <p class="muted" style="font-size: clamp(16px, 2vw, 19px); margin-top: 10px;">
                Escribe un CIF o nombre: autocompleta, elige, y mira la respuesta JSON.
            </p>
        </div>

        <div class="demo-shell animate-up delay-1">
            <div class="search-outer">
                <div class="search-inner">
                    <div class="search-icon" aria-hidden="true" style="position: relative;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6">
                            <path d="M21 21l-4.3-4.3"/>
                            <circle cx="11" cy="11" r="7"/>
                        </svg>
                        <div style="position: absolute; top: 10px; right: 10px; width: 6px; height: 6px; background: #10b981; border-radius: 50%; box-shadow: 0 0 6px #10b981; animation: pulse-green 2s infinite;"></div>
                    </div>

                    <input type="text" id="company-search" placeholder="Escribe el nombre de una empresa o un CIF…" autocomplete="off">

                    <div class="search-hints" aria-hidden="true">
                        <span class="kbd">↵</span><span class="hint-text">Selecciona</span>
                        <span class="kbd">Esc</span><span class="hint-text">Cerrar</span>
                    </div>
                </div>

                <div class="loader" id="loading-spinner"></div>
                <div class="suggestions-dropdown" id="suggestions"></div>
            </div>

            <!-- RESULT PREVIEW -->
            <div id="json-result" class="animate-up is-hidden" style="margin-top: 36px;">
                <div class="tech-window" style="background: #ffffff; border-radius: 24px; box-shadow: 0 30px 60px -12px rgba(15, 23, 42, 0.12); border: 1px solid #e2e8f0;">
                    <div class="tech-top" style="background: #f8fafc; padding: 18px 26px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="padding: 6px 12px; background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulse-green 2s infinite;"></div>
                                LIVE: 200 OK
                            </div>
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Respuesta Verificada</span>
                        </div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; font-family: 'JetBrains Mono', monospace;">application/json</span>
                    </div>
                    <div style="position: relative;">
                        <!-- Subtle watermark -->
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-15deg); font-size: 100px; font-weight: 900; color: rgba(15, 23, 42, 0.02); pointer-events: none; user-select: none; white-space: nowrap;">API OFICIAL</div>
                        <pre style="padding: 34px; margin: 0; font-size: 15px; color: #334155; font-family: 'JetBrains Mono', monospace; overflow-x:auto; line-height: 1.8; background: transparent; position: relative; z-index: 1;"><code id="json-code"></code></pre>
                    </div>
                </div>
                <div style="margin-top: 20px; display: flex; justify-content: center; gap: 16px;">
                    <span style="font-size: 13px; font-weight: 800; color: #12b48a; display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Datos Directos de Fuente Oficial
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- USE CASES -->
<section class="section-padding" style="background-color: #f8fafc; background-image: radial-gradient(#e2e8f0 0.8px, transparent 0.8px); background-size: 24px 24px; position: relative; z-index: 5;">
    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, #ffffff 0%, transparent 15%, transparent 85%, #ffffff 100%); pointer-events: none;"></div>
    <div class="container" style="position: relative; z-index: 1;">
    <div style="text-align:center; margin-bottom: 64px;" class="animate-up">
        <span class="eyebrow">Aplica la automatización</span>
        <h3 style="font-size: clamp(28px, 4vw, 46px); font-weight: 950; margin-top: 12px; letter-spacing: -0.03em;">
            Casos reales <span class="grad">de uso</span>
        </h3>
        <p class="muted" style="max-width: 820px; margin: 14px auto 0; font-size: 18px; line-height: 1.6;">
            Diseñado para flujos B2B donde los datos fiscales correctos no son “nice to have”, sino requisito.
        </p>
    </div>

    <div class="grid animate-up delay-1" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 22px;">
        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Autocompletar formularios B2B</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">Reduce abandono: con el CIF rellenamos razón social, dirección y datos fiscales.</p>
                </div>
            </div>
        </div>

        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Evitar altas falsas</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">Verifica existencia mercantil antes de permitir acceso o activar funcionalidades.</p>
                </div>
            </div>
        </div>

        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Dedupe automático</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">El CIF como ID único: evita duplicados aunque el nombre llegue distinto.</p>
                </div>
            </div>
        </div>

        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Limpieza de CRM por CSV</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">Sube listados de CIF y obtén datos actualizados para normalizar registros.</p>
                </div>
            </div>
        </div>

        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Validación KYB</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">Trazabilidad y señales oficiales para procesos de onboarding B2B.</p>
                </div>
            </div>
        </div>

        <div class="use-case-card">
            <div style="display:flex; gap: 12px; align-items:flex-start;">
                <span style="font-size: 26px; line-height: 1;">✅</span>
                <div>
                    <h3 style="font-weight: 950; margin: 0 0 8px; font-size: 18px;">Prevención pre-factura</h3>
                    <p class="muted" style="line-height: 1.6; margin:0;">Valida datos fiscales antes de emitir: menos rectificativas y menos soporte.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DEVELOPER SECTION -->
<section class="section-padding" style="background: #ffffff; position: relative;">
    <div class="container">
        <div class="dev-wrapper">
            <div class="grid dev-grid">
            <div class="animate-up">
                <span class="eyebrow" style="color: var(--primary);">Developer First</span>
                <h2 style="font-size: clamp(28px, 4vw, 46px); font-weight: 950; margin: 14px 0 18px; letter-spacing: -0.03em;">
                    Pensado para <span class="grad">ingenieros</span>.
                </h2>
                <p class="muted" style="font-size: 18px; line-height: 1.7; margin-bottom: 26px;">
                    Integra la búsqueda de empresas en tu producto con una llamada REST. Sin burocracia, sin fricción.
                </p>

                <div class="mini-cards-grid">
                    <div class="mini-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <strong style="display:block; font-size: 16px; margin-bottom: 6px; color: #0f172a;">Sin contratos</strong>
                        <span class="muted" style="font-size: 14px; color: #64748b;">Paga por uso, cancela cuando quieras.</span>
                    </div>
                    <div class="mini-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <strong style="display:block; font-size: 16px; margin-bottom: 6px; color: #0f172a;">Sin demos</strong>
                        <span class="muted" style="font-size: 14px; color: #64748b;">Empieza a probarlo en 2 minutos.</span>
                    </div>
                </div>
            </div>

                <div class="animate-up delay-2">
                    <div class="tech-window">
                        <div class="tech-top">
                            <div class="dots" aria-hidden="true"><i></i><i></i><i></i></div>
                            <span style="font-size: 13px; color: #64748b; font-weight: 850; margin-left: 10px;">
                                GET /v1/company?cif=B12345678
                            </span>
                        </div>
                        <pre style="padding: 28px; margin:0; line-height: 1.8; font-size: 14px; color: #334155; font-family: 'JetBrains Mono', monospace; background: transparent; overflow-x:auto;"><code><span style="color: #94a3b8; font-style: italic;">// Los datos se devuelven en JSON estándar</span>
<span style="color: #94a3b8; font-style: italic;">// Compatible con cualquier CRM o lenguaje</span>

{
  <span style="color: #0f172a; font-weight: 700;">"success"</span>: <span style="color: #12b48a; font-weight: 700;">true</span>,
  <span style="color: #0f172a; font-weight: 700;">"data"</span>: {
    <span style="color: #0f172a; font-weight: 700;">"name"</span>: <span style="color: #2152ff; font-weight: 700;">"EMPRESA EJEMPLO SL"</span>,
    <span style="color: #0f172a; font-weight: 700;">"cif"</span>: <span style="color: #2152ff; font-weight: 700;">"B12345678"</span>,
    <span style="color: #0f172a; font-weight: 700;">"address"</span>: <span style="color: #2152ff; font-weight: 700;">"CALLE MAYOR 1, MADRID"</span>,
    <span style="color: #0f172a; font-weight: 700;">"status"</span>: <span style="color: #2152ff; font-weight: 700;">"ACTIVA"</span>
  }
}</code></pre>
                    </div>
                </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="cta" style="background: #ffffff; padding: 120px 0; position: relative; overflow: hidden;">
    <!-- Background Accents -->
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 600px; background: radial-gradient(circle, rgba(33, 82, 255, 0.05) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>
    
    <div class="container animate-up" style="text-align:center; position:relative; z-index:2;">
        <h2 style="color: #0f172a; font-size: clamp(34px, 5.5vw, 48px); font-weight: 950; letter-spacing: -0.04em; line-height: 1.1; margin-bottom: 22px;">
            ¿Listo para <span class="grad">automatizar</span> tu negocio?
        </h2>
        <p style="color: #475569; font-size: clamp(18px, 2.2vw, 21px); margin-bottom: 40px; max-width: 800px; margin-inline:auto; line-height: 1.6; font-weight: 500;">
            Únete a SaaS B2B, ERPs y Fintech que ya confían en una API de autocompletado en tiempo real. Configuración instantánea.
        </p>

        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap: 16px;">
            <a href="<?=site_url('register') ?>" class="btn primary"
                style="padding: 22px 50px; font-size: 18px; font-weight: 950; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(33, 82, 255, 0.35); display: flex; align-items: center; gap: 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 1 9 22 2"></polygon></svg>
                <span>Empieza gratis ahora</span>
            </a>
            <a href="<?=site_url('documentation') ?>" class="btn ghost"
                style="padding: 22px 50px; font-size: 18px; font-weight: 950; border-radius: 20px; color: var(--primary); background: #ffffff; border: 2px solid rgba(33, 82, 255, 0.1); display: flex; align-items: center; gap: 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M2 3h6a4 4 0 0 1 4 4v14a4 4 0 0 0-4-4H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a4 4 0 0 1 4-4h6z"></path></svg>
                <span>Ver documentación</span>
            </a>
        </div>

        <div style="margin-top: 40px; display:flex; flex-wrap:wrap; justify-content:center; gap: 30px;">
            <span style="font-weight: 850; color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Sin contratos
            </span>
            <span style="font-weight: 850; color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Sin demos
            </span>
            <span style="font-weight: 850; color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Sandbox Gratis
            </span>
        </div>
    </div>
</section>

</main>

<?=view('partials/footer') ?>

<script>
    const input = document.getElementById('company-search');
    const dropdown = document.getElementById('suggestions');
    const spinner = document.getElementById('loading-spinner');
    const jsonResult = document.getElementById('json-result');
    const jsonCode = document.getElementById('json-code');
    let debounceTimer;

    const baseUrl = '<?= site_url('autocompletado-cif-empresas') ?>';

    const renderItem = (company) => {
        const jsonString = encodeURIComponent(JSON.stringify(company));
        return `
            <div class="suggestion-item" data-json="${jsonString}">
                <div style="min-width:0;">
                    <span class="company-name">${company.name}</span>
                    <span class="company-addr">${company.address || 'Ubicación no facilitada'}</span>
                </div>
                <span class="company-cif">${company.cif}</span>
            </div>
        `;
    };

    const fetchSuggestions = async (query) => {
        if (!query || query.length < 3) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        spinner.style.display = 'block';

        try {
            const response = await fetch(`${baseUrl}/get?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            spinner.style.display = 'none';
            dropdown.innerHTML = '';

            if (result.success && result.data && result.data.length > 0) {
                dropdown.innerHTML = result.data.map(renderItem).join('');
                dropdown.classList.add('active');
            } else {
                dropdown.innerHTML = '<div style="padding:22px; text-align:center; color:#64748b; font-style:italic; font-size:14px;">No hemos encontrado empresas oficiales para esta búsqueda.</div>';
                dropdown.classList.add('active');
            }
        } catch (error) {
            spinner.style.display = 'none';
        }
    };

    input.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        jsonResult.classList.add('is-hidden');

        clearTimeout(debounceTimer);
        if (value.length < 3) {
            dropdown.classList.remove('active');
            return;
        }
        debounceTimer = setTimeout(() => fetchSuggestions(value), 260);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Extra UX: ESC cierra
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') dropdown.classList.remove('active');
    });

    dropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const jsonString = decodeURIComponent(item.dataset.json);
            const data = JSON.parse(jsonString);
            input.value = data.name;
            dropdown.classList.remove('active');

            jsonCode.innerHTML = syntaxHighlight(data);
            jsonResult.classList.remove('is-hidden');
            jsonResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    function syntaxHighlight(json) {
        if (typeof json != 'string') json = JSON.stringify(json, undefined, 2);
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
                var color = '#12b48a'; // Values: Green
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) color = '#0f172a'; // Keys: Dark
                    else color = '#2152ff'; // Strings: Blue
                }
                return '<span style="color:' + color + '; font-weight: 700;">' + match + '</span>';
            });
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-up').forEach(el => observer.observe(el));
</script>

</body>
</html>
