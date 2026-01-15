<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?= site_url() ?>">
                <span class="brand-name">API<span class="grad">Empresas</span> Admin</span>
            </a>
        </div>
        <div class="desktop-only">
            <a class="btn btn_header btn_header--ghost" href="<?= site_url('logout') ?>">Salir</a>
        </div>
    </div>
</header>

<main class="container" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Uso Diario API</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Plan ID</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Peticiones</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Última Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usage as $row): ?>
                <?php $row = (object)$row; ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 600;">
                        <?= date('d/m/Y', strtotime($row->date)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($row->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($row->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                            Plan #<?= $row->plan_id ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <span style="font-size: 1.1rem; font-weight: 700; color: #2152ff;">
                            <?= number_format($row->requests_count, 0, ',', '.') ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem; color: #64748b;">
                        <?= date('d/m/Y H:i', strtotime($row->updated_at)) ?>
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
</body>
</html>
