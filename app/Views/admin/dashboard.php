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

        /* Skeleton Loading */
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
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
                <div class="kpi-value kpi-async-value" data-type="total"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #16a34a; font-weight: 700;"><span class="kpi-async-value" data-type="companies_active"><span class="skeleton" style="width: 40px;"></span></span> Activas</span>
                <span style="color: #cbd5e1;">•</span>
                <span style="color: #f59e0b; font-weight: 700;"><span class="kpi-async-value" data-type="sin_cif"><span class="skeleton" style="width: 40px;"></span></span> Pendientes</span>
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
                <div class="kpi-value kpi-async-value" data-type="users_total"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #16a34a; font-weight: 700;"><span class="kpi-async-value" data-type="users_active"><span class="skeleton" style="width: 40px;"></span></span> Activos</span>
                <span style="color: #cbd5e1;">•</span>
                <span style="color: #6366f1; font-weight: 700;"><span class="kpi-async-value" data-type="subs_active"><span class="skeleton" style="width: 40px;"></span></span> Suscripciones</span>
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
                <div class="kpi-value kpi-async-value" data-type="api_today"><span class="skeleton"></span></div>
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
                <div class="kpi-value kpi-async-value" data-type="api_month"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Acumulado mensual</span>
            </div>
        </div>

        <!-- Ingresos Mes -->
        <div class="kpi-card" style="color: #059669;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Ingresos Mes
                </div>
                <div class="kpi-value kpi-async-value" data-type="revenue_month"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Facturación total (Pagado)</span>
            </div>
        </div>

        <!-- Tasa de Error -->
        <div class="kpi-card" style="color: #dc2626;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    Tasa de Error
                </div>
                <div class="kpi-value kpi-async-value" data-type="api_error_rate"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Peticiones con error (4xx/5xx)</span>
            </div>
        </div>

        <!-- Latencia Media -->
        <div class="kpi-card" style="color: #2563eb;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Latencia Media
                </div>
                <div class="kpi-value kpi-async-value" data-type="api_latency_avg"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Tiempo de respuesta medio</span>
            </div>
        </div>

        <!-- IPs Bloqueadas -->
        <div class="kpi-card" style="color: #4b5563;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    IPs Bloqueadas
                </div>
                <div class="kpi-value kpi-async-value" data-type="blocked_ips_count"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #64748b;">Acceso denegado actualmente</span>
            </div>
        </div>

        <!-- Búsquedas Zero -->
        <div class="kpi-card" style="color: #f59e0b;">
            <div>
                <div class="kpi-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Búsquedas Zero
                </div>
                <div class="kpi-value kpi-async-value" data-type="searches_zero_results"><span class="skeleton"></span></div>
            </div>
            <div class="kpi-footer">
                <span style="color: #16a34a; font-weight: 700;"><span class="kpi-async-value" data-type="searches_resolved_count"><span class="skeleton" style="width: 40px;"></span></span> Resueltas</span>
                <span style="color: #64748b;">(Ya en base de datos)</span>
            </div>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="section-header">
        <h2 class="section-title">Gestión y Herramientas</h2>
    </div>

    <div class="admin-grid">
        <a href="<?= site_url('admin/users') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Usuarios</span>
                <p>Gestión de cuentas, perfiles y permisos de acceso.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/logs') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75V16.5m0-4.5V9m0-4.5V3" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Logs Búsqueda</span>
                <p>Historial y depuración de consultas de empresas.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/api-requests') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Peticiones API</span>
                <p>Monitorización de tráfico y endpoints de la API.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/usage-daily') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Consumo Diario</span>
                <p>Análisis de uso por día y picos de tráfico.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/companies') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Empresas</span>
                <p>Base de datos maestra de empresas y su información.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/plans') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75-6.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75v-9ZM12 3v3.375m0 0c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3m-4.5 0h4.5" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Planes API</span>
                <p>Configuración de precios, límites y cuotas.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/api-keys') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>API Keys</span>
                <p>Emisión y revocación de llaves de acceso.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/subscriptions') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008V15Zm0 3h.008v.008h-.008V18Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Suscripciones</span>
                <p>Gestión de ciclos de facturación y pagos activos.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/activity-logs') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Activity Logs</span>
                <p>Registro de acciones administrativas críticas.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/email-logs') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Logs Emails</span>
                <p>Trazabilidad de correos electrónicos enviados.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/invoices') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Facturas</span>
                <p>Consulta y descarga del historial de facturación.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/api-requests') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Monitor de Tráfico</span>
                <p>Análisis en tiempo real de peticiones y endpoints.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/logs?zero=1') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Top Búsquedas</span>
                <p>Lo más buscado por los usuarios y lo que falta.</p>
            </div>
        </a>

        <a href="#" class="admin-card" id="btn-clear-cache">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Gestor de Caché</span>
                <p>Optimiza el rendimiento limpiando datos temporales.</p>
            </div>
        </a>

        <a href="<?= site_url('admin/blocked-ips') ?>" class="admin-card">
            <div class="admin-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <div class="admin-card-content">
                <span>Centro de Seguridad</span>
                <p>Gestión de baneo de IPs y cortafuegos de la API.</p>
            </div>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadKpis() {
                const kpiElements = document.querySelectorAll('.kpi-async-value');
                
                fetch('<?= site_url('admin/kpis-all') ?>', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    kpiElements.forEach(el => {
                        const type = el.getAttribute('data-type');
                        if (data[type] !== undefined) {
                            el.innerHTML = data[type];
                        }
                    });
                })
                .catch(err => {
                    console.error('Error loading KPIs', err);
                    kpiElements.forEach(el => {
                        el.innerHTML = '<span class="text-red">Error</span>';
                    });
                });
            }

            // Iniciar carga de KPIs
            loadKpis();

            // Gestor de Caché
            const clearCacheBtn = document.getElementById('btn-clear-cache');
            if (clearCacheBtn) {
                clearCacheBtn.addEventListener('click', function(e) {
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
                        background: '#ffffff',
                        borderRadius: '20px'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const icon = this.querySelector('.admin-icon-wrapper');
                            const originalHtml = icon.innerHTML;
                            icon.innerHTML = '⌛';
                            
                            fetch('<?= site_url('admin/clear-cache') ?>', {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(response => response.json())
                            .then(data => {
                                icon.innerHTML = originalHtml;
                                Swal.fire({
                                    title: data.status === 'success' ? '¡Limpiado!' : 'Error',
                                    text: data.message,
                                    icon: data.status === 'success' ? 'success' : 'error',
                                    confirmButtonColor: '#2152ff',
                                    borderRadius: '20px'
                                });
                            })
                            .catch(err => {
                                icon.innerHTML = originalHtml;
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

