<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Logs de Búsqueda</h1>
        <div style="display: flex; gap: 10px;">
            <form action="<?= site_url('admin/logs') ?>" method="get" style="display: flex; gap: 10px; align-items: center;">
                <select name="http_status" class="input" style="padding: 8px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="200" <?= $http_status == '200' ? 'selected' : '' ?>>200 OK</option>
                    <option value="404" <?= $http_status == '404' ? 'selected' : '' ?>>404 Not Found</option>
                    <option value="500" <?= $http_status == '500' ? 'selected' : '' ?>>500 Error</option>
                </select>
            </form>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
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

