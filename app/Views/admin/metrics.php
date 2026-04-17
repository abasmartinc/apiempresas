<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .metric-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08);
        }

        .metric-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .metric-desc {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .section-separator {
            margin: 60px 0 32px;
            position: relative;
        }

        .section-separator::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .section-separator span {
            position: relative;
            background: #f8fafc;
            /* Match background halo if possible */
            padding-right: 20px;
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-separator span::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #2152ff;
            border-radius: 4px;
        }

        .update-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #f1f5f9;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 32px;
        }

        .update-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>

<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div>
                <h1 class="title" style="font-size: 2.8rem; margin-bottom: 8px;">Métricas <span class="grad">Monetización</span></h1>
                <p style="color: #64748b; font-size: 1.1rem;">Visión interna del funnel, revenue y activación de la API.</p>
            </div>
            <a href="<?= site_url('admin/dashboard') ?>" class="btn ghost" style="margin-top: 10px;">Volver al Dashboard</a>
        </div>

        <div class="update-badge">
            <span class="update-dot"></span>
            Última actualización: <?= date('d/m/Y H:i', strtotime($metrics['updated_at'])) ?> (Caché 10 min)
        </div>

        <!-- BLOQUE IA: CONSULTORÍA ESTRATÉGICA -->
        <?php if (isset($metrics['ai_analysis']) && !empty($metrics['ai_analysis']['summary'])): ?>
            <div class="metric-card" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border: none; margin-bottom: 40px;">
                <div style="display: flex; gap: 20px; align-items: flex-start;">
                    <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem;">
                        ✨
                    </div>
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 4px; font-weight: 800; font-size: 1.25rem;">Consultoría Estratégica IA</h3>
                        <p style="margin: 0 0 20px; opacity: 0.9; font-size: 1rem; line-height: 1.5; font-weight: 500;">
                            <?= esc($metrics['ai_analysis']['summary']) ?>
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                            <div>
                                <h4 style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                    🎯 Conclusiones Clave
                                </h4>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    <?php foreach ($metrics['ai_analysis']['conclusions'] as $conclusion): ?>
                                        <li style="background: rgba(255, 255, 255, 0.1); padding: 10px 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.4;">
                                            <?= esc($conclusion) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h4 style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                    🚀 Plan de Acción
                                </h4>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    <?php foreach ($metrics['ai_analysis']['action_plan'] as $action): ?>
                                        <li style="background: rgba(255, 255, 255, 0.1); padding: 10px 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.4; display: flex; align-items: center; gap: 10px;">
                                            <span style="opacity: 0.7;">✅</span> <?= esc($action) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- BLOQUE 1: FUNNEL -->
        <div class="section-separator">
            <span>FUNNEL DE CONVERSIÓN</span>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <p class="metric-title">Signup → 1ª request</p>
                <div class="metric-value"><?= number_format($metrics['funnel']['signup_to_request_pct'], 2, ',', '.') ?>%</div>
                <p class="metric-desc">Usuarios registrados que han probado la API</p>
            </div>

            <div class="metric-card">
                <p class="metric-title">1ª request → pago</p>
                <div class="metric-value"><?= number_format($metrics['funnel']['request_to_paid_pct'], 2, ',', '.') ?>%</div>
                <p class="metric-desc">Usuarios activos que terminaron contratando un plan</p>
            </div>

            <div class="metric-card">
                <p class="metric-title">Tiempo medio hasta pago</p>
                <div class="metric-value"><?= number_format($metrics['funnel']['avg_time_to_paid'], 1, ',', '.') ?> <small style="font-size: 1.1rem; color: #64748b;">h</small></div>
                <p class="metric-desc">Tiempo medio desde el registro hasta la conversión</p>
            </div>
        </div>

        <!-- BLOQUE 2: REVENUE -->
        <div class="section-separator">
            <span>REVENUE & MONETIZACIÓN</span>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <p class="metric-title">ARPU</p>
                <div class="metric-value"><?= number_format($metrics['revenue']['arpu'], 2, ',', '.') ?>€</div>
                <p class="metric-desc">Ingreso medio por usuario de pago</p>
            </div>

            <div class="metric-card">
                <p class="metric-title">MRR</p>
                <div class="metric-value"><?= number_format($metrics['revenue']['mrr'], 2, ',', '.') ?>€</div>
                <p class="metric-desc">Ingreso mensual recurrente actual</p>
            </div>

            <div class="metric-card">
                <p class="metric-title">Upsells Pro → Business</p>
                <div class="metric-value"><?= $metrics['revenue']['expansion_count'] ?></div>
                <p class="metric-desc">Usuarios que expandieron a un plan superior</p>
            </div>
        </div>

        <!-- BLOQUE 3: ACTIVACIÓN -->
        <div class="section-separator">
            <span>ACTIVACIÓN DE USUARIOS</span>
        </div>

        <div class="metrics-grid">
            <div class="metric-card" style="border-left: 4px solid #2152ff;">
                <p class="metric-title">Usuarios que usan la API</p>
                <div class="metric-value"><?= number_format($metrics['activation']['active_users_pct'], 2, ',', '.') ?>%</div>
                <p class="metric-desc">Usuarios registrados con al menos una request</p>
            </div>

            <div class="metric-card" style="border-left: 4px solid #10b981;">
                <p class="metric-title">Usuarios que llegan al 20% del Free</p>
                <div class="metric-value"><?= number_format($metrics['activation']['threshold_20_pct'], 2, ',', '.') ?>%</div>
                <p class="metric-desc">Usuarios con señales reales de activación</p>
            </div>
        </div>

    </main>

    <?= view('partials/footer') ?>
</body>

</html>
