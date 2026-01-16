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
            top: 0; right: 0;
            width: 120px; height: 120px;
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .admin-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            text-decoration: none;
            color: #334155;
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        
        .admin-card:hover {
            background: white;
            transform: translateY(-4px);
            border-color: #2152ff;
            box-shadow: 0 10px 20px -5px rgba(33, 82, 255, 0.1);
            color: #2152ff;
        }
        
        .admin-card svg {
            width: 32px;
            height: 32px;
            color: #64748b;
            transition: color 0.3s ease;
        }
        
        .admin-card:hover svg {
            color: #2152ff;
        }
        
        .admin-card span {
            font-weight: 600;
            font-size: 0.95rem;
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
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0 80px;">
    <div style="margin-bottom: 48px;">
        <h1 class="title" style="font-size: 2.8rem; margin-bottom: 8px;">Dashboard <span class="grad">Admin</span></h1>
        <p style="color: #64748b; font-size: 1.1rem;">Bienvenido de nuevo, <?= esc(session('user_name')) ?>. Aquí tienes el resumen de hoy.</p>
    </div>

    <!-- KPIs Section -->
    <div class="kpi-grid">
        <!-- Empresas -->
        <div class="kpi-card" style="color: #6366f1;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                    Empresas Totales
                </div>
                <div class="kpi-value"><?= number_format($stats['companies_total'], 0, ',', '.') ?></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #16a34a; font-weight: 700;"><?= number_format($stats['companies_active'], 0, ',', '.') ?> Activas</span>
                <span style="color: #cbd5e1;">•</span>
                <span style="color: #f59e0b; font-weight: 700;"><?= number_format($stats['companies_no_cif'], 0, ',', '.') ?> Pendientes</span>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="kpi-card" style="color: #10b981;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Usuarios
                </div>
                <div class="kpi-value"><?= number_format($stats['users_total'], 0, ',', '.') ?></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #16a34a; font-weight: 700;"><?= number_format($stats['users_active'], 0, ',', '.') ?> Activos</span>
                <span style="color: #cbd5e1;">•</span>
                <span style="color: #6366f1; font-weight: 700;"><?= number_format($stats['subs_active'], 0, ',', '.') ?> Suscripciones</span>
            </div>
        </div>

        <!-- API Hoy -->
        <div class="kpi-card" style="color: #f59e0b;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    Peticiones Hoy
                </div>
                <div class="kpi-value"><?= number_format($stats['api_today'], 0, ',', '.') ?></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Tráfico en tiempo real</span>
            </div>
        </div>

        <!-- API Mes -->
        <div class="kpi-card" style="color: #8b5cf6;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.5 4.5L21.75 7.5" />
                    </svg>
                    Peticiones Mes
                </div>
                <div class="kpi-value"><?= number_format($stats['api_month'], 0, ',', '.') ?></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Acumulado mensual</span>
            </div>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="section-header">
        <h2 class="section-title">Gestión y Herramientas</h2>
    </div>

    <div class="admin-grid">
        <a href="<?= site_url('admin/users') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span>Usuarios</span>
        </a>

        <a href="<?= site_url('admin/logs') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75V16.5m0-4.5V9m0-4.5V3" />
            </svg>
            <span>Logs Búsqueda</span>
        </a>

        <a href="<?= site_url('admin/api-requests') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
            </svg>
            <span>Peticiones API</span>
        </a>

        <a href="<?= site_url('admin/usage-daily') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            <span>Consumo Diario</span>
        </a>

        <a href="<?= site_url('admin/companies') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
            <span>Empresas</span>
        </a>

        <a href="<?= site_url('admin/plans') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75-6.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75v-9ZM12 3v3.375m0 0c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3m-4.5 0h4.5" />
            </svg>
            <span>Planes API</span>
        </a>

        <a href="<?= site_url('admin/api-keys') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
            </svg>
            <span>API Keys</span>
        </a>

        <a href="<?= site_url('admin/subscriptions') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008V15Zm0 3h.008v.008h-.008V18Z" />
            </svg>
            <span>Suscripciones</span>
        </a>

        <a href="<?= site_url('admin/email-logs') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            <span>Logs Emails</span>
        </a>

        <a href="<?= site_url('admin/invoices') ?>" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <span>Facturas</span>
        </a>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>

