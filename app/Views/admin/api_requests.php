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
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative;
            overflow: hidden;
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); 
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            background: var(--kpi-color);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.3rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 100px;
            margin-top: 1rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--kpi-color);
            border-radius: 100px;
            transition: width 1s ease-out;
        }
        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Peticiones API</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
            </div>
            <span class="kpi-label">Peticiones (24h)</span>
            <span class="kpi-value"><?= number_format($stats['requests_24h'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Volumen total de llamadas</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <span class="kpi-label">Latencia Media</span>
            <span class="kpi-value"><?= number_format($stats['avg_latency'], 0, ',', '.') ?> <small style="font-size: 1rem; font-weight: 600;">ms</small></span>
            <span class="kpi-sub">Tiempo de respuesta promedio</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-rose);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <span class="kpi-label">Tasa de Error</span>
            <span class="kpi-value"><?= number_format($stats['error_rate'], 1, ',', '.') ?>%</span>
            <span class="kpi-sub">Peticiones con error (4xx/5xx)</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['error_rate'] ?>%;"></div>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <span class="kpi-label">Clientes Activos</span>
            <span class="kpi-value"><?= number_format($stats['active_users'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios únicos hoy</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/api-requests') ?>" method="get" class="grid" style="grid-template-columns: repeat(4, 1fr) auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Endpoint</label>
                <input type="text" name="q" class="input" style="width: 100%;" placeholder="Ej: /api/v1/companies..." value="<?= esc($q) ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Fecha</label>
                <input type="date" name="date" class="input" style="width: 100%;" value="<?= esc($date ?? '') ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                <select name="user_id" id="user-select" style="width: 100%;">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>>
                            <?= esc($u->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Estado</label>
                <select name="status_code" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="200" <?= $status_code == '200' ? 'selected' : '' ?>>200 OK</option>
                    <option value="400" <?= $status_code == '400' ? 'selected' : '' ?>>400 Bad Request</option>
                    <option value="401" <?= $status_code == '401' ? 'selected' : '' ?>>401 Unauthorized</option>
                    <option value="404" <?= $status_code == '404' ? 'selected' : '' ?>>404 Not Found</option>
                    <option value="429" <?= $status_code == '429' ? 'selected' : '' ?>>429 Too Many Requests</option>
                    <option value="500" <?= $status_code == '500' ? 'selected' : '' ?>>500 Server Error</option>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/api-requests') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Método / Endpoint</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Duración</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">IP / Request ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <?php 
                    // Convertir a objeto si es array (por el returnType del modelo)
                    $req = (object)$req; 
                ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-size: 0.85rem; white-space: nowrap;">
                        <?= date('d/m/Y H:i:s', strtotime($req->created_at)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($req->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($req->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <span style="font-weight: 700; color: #2152ff; font-size: 0.75rem;"><?= strtoupper($req->http_method) ?></span>
                        <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; margin-left: 5px;">
                            <?= esc($req->endpoint) ?>
                        </code>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $statusColor = '#16a34a'; // 2xx
                        if ($req->status_code >= 400) $statusColor = '#ca8a04'; // 4xx
                        if ($req->status_code >= 500) $statusColor = '#dc2626'; // 5xx
                        ?>
                        <span class="pill" style="background: <?= $statusColor ?>15; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>30; font-weight: 700;">
                            <?= $req->status_code ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 0.85rem;">
                        <span style="color: <?= $req->duration_ms > 500 ? '#ca8a04' : '#64748b' ?>;">
                            <?= $req->duration_ms ?> ms
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-size: 0.8rem; color: #64748b;"><?= esc($req->ip_address) ?></div>
                        <div style="font-size: 0.65rem; color: #94a3b8; font-family: monospace;"><?= esc($req->request_id) ?></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem;">
        <?= $pager->links('default', 'admin_full') ?>
    </div>
</main>

<?= view('partials/footer') ?>

<!-- Select2 para hacer el select buscable -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes para Select2 encaje con el .input */
    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        height: 42px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a;
        font-size: 0.85rem;
        line-height: normal;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        font-size: 0.85rem;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2152ff;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('user-select')) {
            $('#user-select').select2({
                placeholder: "Buscar usuario...",
                width: '100%'
            });
        }
    });
</script>

</body>
</html>

