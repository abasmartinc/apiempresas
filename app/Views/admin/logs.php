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
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
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
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
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
        .pill { padding: 2px 8px; border-radius: 6px; font-weight: 600; }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Logs de Búsqueda</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <span class="kpi-label">Búsquedas Totales</span>
            <span class="kpi-value"><?= number_format($stats['total_searches'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Histórico total acumulado</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="15" y2="17"></line><line x1="9" y1="9" x2="10" y2="9"></line></svg>
            </div>
            <span class="kpi-label">Sin Resultados</span>
            <span class="kpi-value"><?= number_format($stats['no_results'], 0, ',', '.') ?></span>
            <span class="kpi-sub">CIFs no encontrados en DB</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <span class="kpi-label">Tasa de Éxito</span>
            <span class="kpi-value"><?= number_format($stats['success_rate'], 1, ',', '.') ?>%</span>
            <span class="kpi-sub">Cifras de efectividad de búsqueda</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['success_rate'] ?>%;"></div>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <span class="kpi-label">Búsquedas Hoy</span>
            <span class="kpi-value"><?= number_format($stats['searches_today'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Actividad en las últimas 24h</span>
        </div>
    </div>

    <!-- Buscador / Filtros -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <form action="<?= site_url('admin/logs') ?>" method="get" class="grid" style="grid-template-columns: 1fr 1fr auto auto auto auto 1fr; gap: 1rem; align-items: end;">
            <div style="grid-column: span 2;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Búsqueda</label>
                <input type="text" name="q" class="input" style="width: 100%;" placeholder="Término de búsqueda, CIF, IP..." value="<?= esc($q ?? '') ?>">
            </div>
            
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Desde</label>
                <input type="date" name="from_date" class="input" value="<?= esc($from_date ?? '') ?>">
            </div>
            
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Hasta</label>
                <input type="date" name="to_date" class="input" value="<?= esc($to_date ?? '') ?>">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Canal</label>
                <select name="channel" class="input" style="width: 100%; min-width: 120px;">
                    <option value="">Todos</option>
                    <option value="api" <?= ($channel ?? '') === 'api' ? 'selected' : '' ?>>API</option>
                    <option value="web" <?= ($channel ?? '') === 'web' ? 'selected' : '' ?>>Web</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">HTTP Status</label>
                <select name="http_status" class="input" style="width: 100%; min-width: 140px;">
                    <option value="">Todos</option>
                    <option value="200" <?= ($http_status ?? '') == '200' ? 'selected' : '' ?>>200 OK</option>
                    <option value="404" <?= ($http_status ?? '') == '404' ? 'selected' : '' ?>>404 Not Found</option>
                    <option value="500" <?= ($http_status ?? '') == '500' ? 'selected' : '' ?>>500 Error</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 5px; height: 42px;">
                <button type="submit" class="btn" style="padding: 0 20px;">Filtrar</button>
                <a href="<?= site_url('admin/logs') ?>" class="btn ghost" title="Limpiar filtros" style="padding: 0 15px; display: flex; align-items: center;">🔄</a>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1100px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Canal</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Búsqueda</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Tipo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Resultado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Empresa Encontrada</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Verificar</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Incluido</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-size: 0.85rem; white-space: nowrap;">
                        <?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="font-size: 0.7rem; background: <?= $log->channel === 'api' ? '#fef3c7; color: #92400e;' : '#e0f2fe; color: #075985;' ?>">
                            <?= strtoupper($log->channel) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-weight: 600;">
                        <?= esc($log->query_raw) ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                        <?= strtoupper($log->query_type) ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $statusColor = '#64748b';
                        if ($log->result_status === 'ok') $statusColor = '#16a34a';
                        if ($log->result_status === 'not_found') $statusColor = '#ca8a04';
                        if ($log->result_status === 'error') $statusColor = '#dc2626';
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: 600; font-size: 0.85rem;">
                            <?= strtoupper($log->result_status) ?> (<?= $log->http_status ?>)
                        </span>
                        <div style="font-size: 0.75rem; color: #94a3b8;"><?= $log->result_count ?> resultados</div>
                        
                        <?php if ($log->result_count == 0 && $log->query_type == 'cif' && in_array($log->query_raw, $resolved_cifs)): ?>
                            <div style="margin-top: 5px;">
                                <span class="pill" style="background: #ecfdf5; color: #059669; font-size: 0.65rem; padding: 2px 8px; border: 1px solid #10b98140;">
                                    ✅ YA DISPONIBLE
                                </span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log->company_cif): ?>
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($log->company_name) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= esc($log->company_cif) ?></div>
                        <?php else: ?>
                            <span style="color: #cbd5e1;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log->company_cif): ?>
                            <button class="btn ghost btn-verify" data-cif="<?= esc($log->company_cif) ?>" style="padding: 4px 10px; font-size: 0.75rem;">
                                🔍 Verificar
                            </button>
                            <div class="verify-result" style="font-size: 0.7rem; margin-top: 4px;"></div>
                        <?php else: ?>
                            <span style="color: #cbd5e1;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <a href="<?= site_url('admin/logs/toggle-included/' . $log->id) ?>" class="btn ghost" style="padding: 4px 10px; font-size: 0.75rem; border-color: <?= $log->included ? '#16a34a' : '#e2e8f0' ?>; color: <?= $log->included ? '#16a34a' : '#64748b' ?>;">
                            <?= $log->included ? '✅ Sí' : '❌ No' ?>
                        </a>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem; color: #64748b;">
                        <?= esc($log->ip) ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const verifyButtons = document.querySelectorAll('.btn-verify');
    
    verifyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const cif = this.getAttribute('data-cif');
            const resultDiv = this.nextElementSibling;
            
            this.disabled = true;
            this.innerText = '⌛...';
            resultDiv.innerHTML = '';

            fetch('<?= site_url('admin/logs/check-cif') ?>?cif=' + cif)
                .then(response => response.json())
                .then(data => {
                    this.disabled = false;
                    this.innerText = '🔍 Verificar';
                    
                    if (data.exists) {
                        resultDiv.innerHTML = '<span style="color: #16a34a; font-weight: 700;">✅ Existe en DB</span>';
                        this.style.borderColor = '#16a34a';
                        this.style.color = '#16a34a';
                    } else {
                        resultDiv.innerHTML = '<span style="color: #dc2626; font-weight: 700;">❌ No existe</span>';
                        this.style.borderColor = '#dc2626';
                        this.style.color = '#dc2626';
                    }
                })
                .catch(error => {
                    this.disabled = false;
                    this.innerText = '🔍 Verificar';
                    resultDiv.innerHTML = '<span style="color: #dc2626;">Error</span>';
                });
        });
    });
});
</script>

<?= view('partials/footer') ?>
</body>
</html>

