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
        <h1 class="title">Peticiones API</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/api-requests') ?>" method="get" class="grid" style="grid-template-columns: 1fr 1fr auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Endpoint</label>
                <input type="text" name="q" class="input" style="width: 100%;" placeholder="Ej: /api/v1/companies..." value="<?= esc($q) ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                <select name="user_id" class="input" style="width: 100%;">
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
</body>
</html>

