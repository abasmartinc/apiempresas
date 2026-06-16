<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .kpi-card {
            position: relative;
            overflow: hidden;
            background: white;
            border-radius: 24px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 120px;
            background: currentColor;
            opacity: 0.03;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }

        .kpi-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .kpi-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .admin-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            text-decoration: none;
            color: #1e293b;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: flex-start;
            gap: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .admin-card:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: #2152ff;
            box-shadow: 0 20px 25px -5px rgba(33, 82, 255, 0.1), 0 10px 10px -5px rgba(33, 82, 255, 0.04);
        }

        .admin-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #64748b;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .admin-card:hover .admin-icon-wrapper {
            background: #2152ff;
            color: white;
            transform: rotate(-5deg);
        }

        .admin-icon-wrapper svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }

        .admin-card-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: left;
        }

        .admin-card span {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }

        .admin-card p {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            margin-top: 40px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: #2152ff;
            border-radius: 4px;
        }

        /* Subtle colors for icons */
        .icon-sub-blue {
            background: #eff6ff !important;
            color: #2152ff !important;
        }

        .icon-sub-indigo {
            background: #eef2ff !important;
            color: #6366f1 !important;
        }

        .icon-sub-emerald {
            background: #ecfdf5 !important;
            color: #10b981 !important;
        }

        .icon-sub-amber {
            background: #fffbeb !important;
            color: #f59e0b !important;
        }

        .icon-sub-orange {
            background: #fff7ed !important;
            color: #ea580c !important;
        }

        .icon-sub-purple {
            background: #f5f3ff !important;
            color: #8b5cf6 !important;
        }

        .icon-sub-rose {
            background: #fff1f2 !important;
            color: #f43f5e !important;
        }

        .icon-sub-sky {
            background: #f0f9ff !important;
            color: #0ea5e9 !important;
        }

        .icon-sub-violet {
            background: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .icon-sub-pink {
            background: #fdf2f8 !important;
            color: #db2777 !important;
        }

        .icon-sub-red {
            background: #fef2f2 !important;
            color: #dc2626 !important;
        }

        .icon-sub-slate {
            background: #f8fafc !important;
            color: #475569 !important;
        }

        .icon-sub-green {
            background: #f0fdf4 !important;
            color: #16a34a !important;
        }

        .skeleton {
            display: inline-block;
            height: 1em;
            width: 80px;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 4px;
            vertical-align: middle;
        }

        .kpi-value .skeleton {
            height: 2.5rem;
            width: 120px;
            margin-bottom: 4px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
        .group-link {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 24px;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .group-link:hover {
            background: #f8fafc;
            border-left-color: #2152ff;
            padding-left: 30px;
        }

        .link-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .link-icon svg {
            width: 18px;
            height: 18px;
        }

        .link-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .link-text strong {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .link-text p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .group-header {
            padding: 24px;
            background: linear-gradient(to bottom right, #ffffff, #f8fafc);
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .group-icon-premium {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.1);
            color: white;
            flex-shrink: 0;
        }

        .icon-grad-blue { background: linear-gradient(135deg, #2152ff 0%, #3b82f6 100%); }
        .icon-grad-amber { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        .icon-grad-rose { background: linear-gradient(135deg, #db2777 0%, #f472b6 100%); }
        .icon-grad-red { background: linear-gradient(135deg, #dc2626 0%, #f87171 100%); }

        .group-title-wrapper {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .group-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .group-subtitle {
            margin: 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .group-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
</head>

<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;">
        <div style="margin-bottom: 48px;">
            <h1 class="title" style="font-size: 2.8rem; margin-bottom: 8px;">Dashboard <span class="grad">Admin</span>
            </h1>
            <p style="color: #64748b; font-size: 1.1rem;">Bienvenido de nuevo, <?= esc(session('user_name')) ?>. Aquí
                tienes el resumen de hoy. <a href="<?= site_url('dashboard?view=client') ?>" style="color: #2152ff; font-weight: 700; text-decoration: none; margin-left: 10px;">Ver mi Dashboard como Cliente &rarr;</a></p>
        </div>

        <!-- KPIs Section Removed for Performance -->

        <!-- Grid de Grupos de Gestión -->
        <div class="dashboard-groups" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 32px;">
            
            <!-- Grupo: Operaciones de Negocio -->
            <div class="group-card" style="background: #fff; border-radius: 28px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
                <div class="group-header">
                    <div class="group-icon-premium icon-grad-blue">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div class="group-title-wrapper">
                        <p class="group-subtitle">Ecosistema</p>
                        <h2 class="group-title">Operaciones de Negocio</h2>
                    </div>
                </div>
                <div style="padding: 8px 0;">
                    <a href="<?= site_url('admin/users') ?>" class="group-link">
                        <span class="link-icon icon-sub-blue"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg></span>
                        <div class="link-text"><strong>Usuarios</strong><p>Gestión de cuentas y perfiles.</p></div>
                    </a>

                    <a href="<?= site_url('admin/tickets') ?>" class="group-link">
                        <span class="link-icon icon-sub-sky"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.974 0-5.699-1.08-7.843-2.882m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg></span>
                        <div class="link-text">
                            <strong style="display: flex; align-items: center; gap: 8px;">
                                Tickets de Soporte
                                <?php if(isset($pending_tickets_count) && $pending_tickets_count > 0): ?>
                                    <span style="background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 99px; font-weight: 800;"><?= $pending_tickets_count ?></span>
                                <?php endif; ?>
                            </strong>
                            <p>Atención al cliente y reportes.</p>
                        </div>
                    </a>
                    <a href="<?= site_url('admin/subscriptions') ?>" class="group-link">
                        <span class="link-icon icon-sub-emerald"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008V15Zm0 3h.008v.008h-.008V18Z" /></svg></span>
                        <div class="link-text"><strong>Suscripciones</strong><p>Pagos activos y ciclos de facturación.</p></div>
                    </a>
                    <a href="<?= site_url('admin/invoices') ?>" class="group-link">
                        <span class="link-icon icon-sub-violet"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></span>
                        <div class="link-text"><strong>Facturas</strong><p>Historial de facturación y cobros.</p></div>
                    </a>
                    <a href="<?= site_url('admin/plans') ?>" class="group-link">
                        <span class="link-icon icon-sub-rose"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75-6.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75v-9ZM12 3v3.375m0 0c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3m-4.5 0h4.5" /></svg></span>
                        <div class="link-text"><strong>Planes y Tarifas</strong><p>Configuración de precios y límites.</p></div>
                    </a>
                    <a href="<?= site_url('admin/api-keys') ?>" class="group-link">
                        <span class="link-icon icon-sub-sky"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg></span>
                        <div class="link-text"><strong>API Keys</strong><p>Gestión de credenciales de acceso.</p></div>
                    </a>
                </div>
            </div>

            <!-- Grupo: Monitorización de Datos -->
            <div class="group-card" style="background: #fff; border-radius: 28px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
                <div class="group-header">
                    <div class="group-icon-premium icon-grad-amber">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    </div>
                    <div class="group-title-wrapper">
                        <p class="group-subtitle">Auditoría</p>
                        <h2 class="group-title">Monitorización de Datos</h2>
                    </div>
                </div>
                <div style="padding: 8px 0;">

                    <a href="<?= site_url('admin/email-logs') ?>" class="group-link">
                        <span class="link-icon icon-sub-pink"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg></span>
                        <div class="link-text"><strong>Logs de Emails</strong><p>Trazabilidad de correos del sistema.</p></div>
                    </a>
                    <a href="<?= site_url('admin/api-requests') ?>" class="group-link">
                        <span class="link-icon icon-sub-purple"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" /></svg></span>
                        <div class="link-text"><strong>Peticiones API</strong><p>Monitorización técnica en tiempo real.</p></div>
                    </a>
                    <a href="<?= site_url('admin/usage-daily') ?>" class="group-link">
                        <span class="link-icon icon-sub-emerald"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></span>
                        <div class="link-text"><strong>Consumo Diario</strong><p>Análisis agregado por fecha.</p></div>
                    </a>
                    <a href="<?= site_url('admin/event-tracking') ?>" class="group-link">
                        <span class="link-icon icon-sub-amber"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M18.757 14.743l-1.591-1.591m-14.166-8.318 1.591 1.591M3.75 10.5H6m.743 4.243 1.591-1.591" /></svg></span>
                        <div class="link-text"><strong>Event Tracking</strong><p>Seguimiento de comportamiento (Heatmap).</p></div>
                    </a>
                </div>
            </div>

            <!-- Grupo: Crecimiento y Marketing -->
            <div class="group-card" style="background: #fff; border-radius: 28px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
                <div class="group-header">
                    <div class="group-icon-premium icon-grad-rose">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.048 8.287 8.287 0 0 0 9 9.6a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/></svg>
                    </div>
                    <div class="group-title-wrapper">
                        <p class="group-subtitle">Expansión</p>
                        <h2 class="group-title">Crecimiento y Marketing</h2>
                    </div>
                </div>
                <div style="padding: 8px 0;">
                    <a href="<?= site_url('admin/ia-marketing') ?>" class="group-link">
                        <span class="link-icon icon-sub-green"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg></span>
                        <div class="link-text"><strong>IA Marketing</strong><p>Leads inteligentes y análisis de conversión.</p></div>
                    </a>
                    <a href="<?= site_url('admin/seo-auto-posts') ?>" class="group-link">
                        <span class="link-icon icon-sub-purple"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg></span>
                        <div class="link-text"><strong>SEO Auto Posts</strong><p>Generación automatizada de contenidos.</p></div>
                    </a>
                    <a href="<?= site_url('admin/email-templates') ?>" class="group-link">
                        <span class="link-icon icon-sub-blue"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg></span>
                        <div class="link-text"><strong>Gestor de Emails</strong><p>Configuración de plantillas transaccionales.</p></div>
                    </a>
                    <a href="<?= site_url('admin/search-console') ?>" class="group-link">
                        <span class="link-icon icon-sub-sky"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span>
                        <div class="link-text"><strong>Search Console</strong><p>Integración oficial con métricas de Google.</p></div>
                    </a>
                    <a href="<?= site_url('admin/metrics') ?>" class="group-link">
                        <span class="link-icon icon-sub-indigo"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></span>
                        <div class="link-text"><strong>Métricas de Funnel</strong><p>Salud del negocio y KPIs de activación.</p></div>
                    </a>
                </div>
            </div>

            <!-- Grupo: Sistema y Mantenimiento -->
            <div class="group-card" style="background: #fff; border-radius: 28px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
                <div class="group-header">
                    <div class="group-icon-premium icon-grad-red">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.128 1.41-.513M12 3.103V4.5m0 15v1.397M5.077 18.923l.513-1.411m12.82-12.82.513-1.411m-4.736 15.75L12 20.25M6.103 5.077l1.411.513m12.82 12.82 1.411.513"/></svg>
                    </div>
                    <div class="group-title-wrapper">
                        <p class="group-subtitle">Infraestructura</p>
                        <h2 class="group-title">Sistema y Mantenimiento</h2>
                    </div>
                </div>
                <div style="padding: 8px 0;">
                    <a href="<?= site_url('admin/blocked-ips') ?>" class="group-link">
                        <span class="link-icon icon-sub-red"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></span>
                        <div class="link-text"><strong>Centro de Seguridad</strong><p>Gestión de bloqueos e IPs maliciosas.</p></div>
                    </a>
                    <a href="#" class="group-link" id="btn-clear-cache-card">
                        <span class="link-icon icon-sub-blue"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg></span>
                        <div class="link-text"><strong>Limpiar Caché</strong><p>Forzar regeneración de datos estáticos.</p></div>
                    </a>
                    <a href="<?= site_url('admin/logs?zero=1') ?>" class="group-link">
                        <span class="link-icon icon-sub-orange"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg></span>
                        <div class="link-text"><strong>Top Búsquedas</strong><p>Análisis de demanda y términos populares.</p></div>
                    </a>
                </div>
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // KPIs removed for performance as requested.

                // Lógica de cuenta atrás para el refresco (Opcional: podrías querer quitarla si solo refrescaba KPIs)
                let timeLeft = 60;
                const timerEl = document.getElementById('refresh-timer');

                setInterval(function () {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        // loadKpis(); // Removed
                        location.reload(); // Recargar página completa si se desea mantener algún tipo de refresco, o simplemente refrescar nada
                        timeLeft = 60;
                    }
                    if (timerEl) {
                        timerEl.innerText = timeLeft + 's';
                    }
                }, 1000);

                // Gestor de Caché
                const clearCacheBtn = document.getElementById('btn-clear-cache-card');
                if (clearCacheBtn) {
                    clearCacheBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        Swal.fire({
                            title: '¿Limpiar caché?',
                            text: "Esta acción eliminará todos los datos temporales del sistema.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#2152ff',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Sí, limpiar todo',
                            cancelButtonText: 'Cancelar',
                            background: '#ffffff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const icon = clearCacheBtn.querySelector('.admin-icon-wrapper');
                                const originalHtml = icon ? icon.innerHTML : '';
                                if (icon) icon.innerHTML = '⌛';

                                fetch('<?= site_url('admin/clear-cache') ?>', {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (icon) icon.innerHTML = originalHtml;
                                        Swal.fire({
                                            title: data.status === 'success' ? '¡Limpiado!' : 'Error',
                                            text: data.message,
                                            icon: data.status === 'success' ? 'success' : 'error',
                                            confirmButtonColor: '#2152ff'
                                        });
                                    })
                                    .catch(err => {
                                        if (icon) icon.innerHTML = originalHtml;
                                        Swal.fire('Error', 'No se pudo completar la acción', 'error');
                                    });
                            }
                        });
                    });
                }


            });
        </script>
    </main>

    <?= view('partials/footer') ?>
</body>

</html>