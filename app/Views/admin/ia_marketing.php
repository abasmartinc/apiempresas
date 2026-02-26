<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<!-- Navegación y Header idénticos al resto del Admin -->
<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="title" style="display: flex; align-items: center; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:28px; color:#10b981;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path></svg>
                IA Marketing: Lead Scoring
            </h1>
            <p style="color: #64748b; margin-top: 5px; font-size: 0.95rem;">Análisis inteligente de actividad para identificar usuarios gratuitos con alta probabilidad de suscripción.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #3b82f6;">
            <div style="background: #eff6ff; padding: 12px; border-radius: 12px; color: #3b82f6;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Leads Analizados</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['total_leads']) ?></p>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #f59e0b;">
            <div style="background: #fef3c7; padding: 12px; border-radius: 12px; color: #d97706;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.866 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Hot Leads (Sin Subscripción)</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['total_hot_leads']) ?></p>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #10b981;">
            <div style="background: #d1fae5; padding: 12px; border-radius: 12px; color: #059669;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Score Promedio</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['average_score']) ?>%</p>
            </div>
        </div>
    </div>

    <!-- Filtro visual (Javascript local) para agilizar -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <div>
            <input type="text" id="liveSearch" class="input" placeholder="Buscar usuario o email..." style="min-width: 300px;">
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="font-size: 0.85rem; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">Excluyendo planes de pago activos</span>
        </div>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1100px;" id="leadsTable">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; width: 5%;">Score</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; width: 25%;">Lead</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Días Inactivo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Búsquedas Web</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Llamadas API</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Actividad General</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $l): ?>
                <?php 
                    $scoreColor = '#ef4444'; // Red for low
                    $scoreBg = '#fee2e2';
                    if ($l->score >= 80) {
                        $scoreColor = '#059669'; // Green 
                        $scoreBg = '#d1fae5';
                    } elseif ($l->score >= 50) {
                        $scoreColor = '#d97706'; // Orange
                        $scoreBg = '#fef3c7';
                    }
                    
                    $daysSinceLogin = '-';
                    if ($l->last_login_at) {
                        $daysSinceLogin = round((strtotime(date('Y-m-d')) - strtotime($l->last_login_at)) / (60 * 60 * 24));
                    }
                ?>
                <tr class="lead-row" data-name="<?= esc(strtolower($l->name)) ?>" data-email="<?= esc(strtolower($l->email)) ?>" data-status="<?= $l->sub_status ?>" data-plan="<?= $l->plan_id ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                    <td style="padding: 12px;">
                        <div style="background: <?= $scoreBg ?>; color: <?= $scoreColor ?>; font-weight: 800; text-align: center; border-radius: 8px; padding: 8px; font-size: 1.1rem; border: 1px solid <?= $scoreColor ?>33;">
                            <?= $l->score ?>%
                        </div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                            <?= esc($l->name) ?>
                            <?php if($l->sub_status === 'active' && $l->plan_id == 1): ?>
                                <span class="pill" style="font-size: 0.65rem; background: #f0fdf4; color: #16a34a; border: 1px solid #22c55e40;">Plan Free</span>
                            <?php elseif($l->sub_status === 'active'): ?>
                                <span class="pill" style="font-size: 0.65rem; background: #cffafe; color: #0891b2; border: 1px solid #06b6d440;">Cliente Activo</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: #64748b;"><?= esc($l->email) ?></div>
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 4px;">Registrado: <?= date('d M Y', strtotime($l->created_at)) ?></div>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $daysSinceLogin !== '-' && $daysSinceLogin < 10 ? '#10b981' : '#64748b' ?>;">
                            <?= $daysSinceLogin ?> <?= $daysSinceLogin !== '-' ? 'días' : '' ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $l->total_searches > 20 ? '#10b981' : '#64748b' ?>;">
                            <?= number_format($l->total_searches) ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $l->total_api_requests > 100 ? '#10b981' : '#64748b' ?>;">
                            <?= number_format($l->total_api_requests) ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $l->total_activity > 30 ? '#10b981' : '#64748b' ?>;">
                            <?= number_format($l->total_activity) ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: right;">
                        <a href="<?= site_url('admin/users/email/' . $l->id) ?>" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: <?= ($l->sub_status === 'active' && $l->plan_id > 1) ? '#cbd5e1' : '#2152ff' ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px; margin-right: 4px; display: inline-block; vertical-align: sub;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            Enviar Promo
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($leads)): ?>
                <tr>
                    <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                        No hay usuarios con actividad reciente para puntuar.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const liveSearchVal = document.getElementById('liveSearch');
        const rows = document.querySelectorAll('.lead-row');

        function filterRows() {
            const query = liveSearchVal.value.toLowerCase().trim();

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');

                let show = true;
                
                if (query !== '' && !name.includes(query) && !email.includes(query)) {
                    show = false;
                }

                row.style.display = show ? '' : 'none';
            });
        }

        liveSearchVal.addEventListener('input', filterRows);
    });
</script>

<?= view('partials/footer') ?>
</body>
</html>
