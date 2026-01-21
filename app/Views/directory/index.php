<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $meta_description]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-primary-soft: rgba(33, 82, 255, 0.08);
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-700: #334155;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-bg: #f8fafc;
        }

        /* 1. Hero Refinado (High Contrast) */
        .dir-hero {
            padding: 80px 0 60px;
            background: var(--dir-slate-900);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dir-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 40%;
            height: 80%;
            background: radial-gradient(circle, rgba(33, 82, 255, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .dir-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dir-hero p {
            font-size: 1.25rem;
            color: var(--dir-slate-400);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* 2. Main Layout & Paddings */
        .dir-main {
            padding: 0px 0;
            background-color: var(--dir-bg);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem; /* Padding lateral para que no toque bordes */
        }

        .dir-section {
            margin-bottom: 100px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 3.5rem;
        }

        .section-header h2 {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            margin: 0;
            white-space: nowrap;
            letter-spacing: -0.02em;
        }

        .section-header .line {
            height: 2px;
            background: #e2e8f0;
            flex-grow: 1;
            border-radius: 99px;
        }

        /* 3. Grid & Cards */
        .dir-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .dir-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            border-radius: 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .dir-card:hover {
            transform: translateY(-8px);
            border-color: var(--dir-primary);
            box-shadow: 0 25px 50px -12px rgba(33, 82, 255, 0.15);
        }

        .dir-card__name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dir-slate-900);
            line-height: 1.4;
            letter-spacing: -0.01em;
        }

        .dir-card__footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }

        .dir-card__count {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--dir-primary);
            background: var(--dir-primary-soft);
            padding: 6px 14px;
            border-radius: 12px;
        }

        .dir-card__arrow {
            color: var(--dir-slate-400);
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .dir-card:hover .dir-card__arrow {
            transform: translateX(6px);
            color: var(--dir-primary);
        }

        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 40px; }
            .dir-hero h1 { font-size: 2.5rem; }
            .dir-main { padding: 60px 0; }
            .container { padding: 0 1.5rem; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .section-header h2 { font-size: 1.75rem; white-space: normal; }
            .dir-grid { gap: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <h1>Directorio de <span class="grad">Empresas Españolas</span></h1>
        <p>Valide información mercantil en tiempo real con acceso a la base de datos más completa de sociedades en España.</p>
    </div>
</header>

<main class="dir-main">
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        
        <section class="dir-section">
            <div class="section-header">
                <h2>Empresas por Provincia</h2>
                <div class="line"></div>
            </div>
            <div class="dir-grid">
                <?php foreach($provinces as $prov): ?>
                    <a href="<?= site_url('directorio/provincia/' . urlencode($prov['name'])) ?>" class="dir-card">
                        <span class="dir-card__name"><?= esc($prov['name']) ?></span>
                        <div class="dir-card__footer">
                            <span class="dir-card__count"><?= number_format($prov['total'], 0, ',', '.') ?> empresas</span>
                            <span class="dir-card__arrow">→</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="dir-section">
            <div class="section-header">
                <h2>Sectores de Actividad (CNAE)</h2>
                <div class="line"></div>
            </div>
            <div class="dir-grid">
                <?php foreach($cnaes as $cnae): ?>
                    <?php 
                        $label = $cnae['name'] ?: "CNAE {$cnae['cnae']}";
                        if (mb_strlen($label) > 60) $label = mb_substr($label, 0, 57) . '...';
                    ?>
                    <a href="<?= site_url('directorio/cnae/' . $cnae['cnae']) ?>" class="dir-card" title="<?= esc($cnae['name']) ?>">
                        <span class="dir-card__name" style="font-size: 1.05rem;"><?= esc($label) ?></span>
                        <div class="dir-card__footer">
                            <span class="dir-card__count"><?= number_format($cnae['total'], 0, ',', '.') ?></span>
                            <span class="dir-card__arrow">→</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
