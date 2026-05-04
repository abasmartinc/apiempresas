<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .status-pill { padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .status-generating { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
        .status-generated { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-published { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-failed { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .loader-inline {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        .card-header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <h1 class="title">SEO Auto Posts</h1>
        <div class="flex-gap-10">
            <button id="btn-generate-batch" class="btn primary">Generar posts pendientes</button>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Formulario Nueva Keyword -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: #1e293b;">Añadir Nueva Keyword</h3>
        <form action="<?= site_url('admin/seo-auto-posts/store') ?>" method="post" class="grid" style="grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <?= csrf_field() ?>
            <div>
                <label class="input-label">Keyword Objetivo</label>
                <input type="text" name="keyword" class="input w-full" placeholder="Ej: cómo recibir alertas BORME" required>
            </div>
            <div>
                <label class="input-label">Intención de Búsqueda</label>
                <select name="intent" class="input w-full">
                    <option value="informacional">Informacional</option>
                    <option value="comercial">Comercial</option>
                    <option value="mixta">Mixta</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn">Añadir keyword</button>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fecaca;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Keyword</th>
                    <th style="padding: 12px;">Intención</th>
                    <th style="padding: 12px;">Estado</th>
                    <th style="padding: 12px;">Título WP / Error</th>
                    <th style="padding: 12px;">Publicado en</th>
                    <th style="padding: 12px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keywords as $k): ?>
                <tr id="row-<?= $k['id'] ?>">
                    <td class="font-bold">#<?= $k['id'] ?></td>
                    <td>
                        <div class="font-bold"><?= esc($k['keyword']) ?></div>
                        <div class="text-xs text-slate"><?= esc($k['slug'] ?: '-') ?></div>
                    </td>
                    <td><span class="pill pill-sm"><?= ucfirst($k['intent']) ?></span></td>
                    <td>
                        <span class="status-pill status-<?= $k['status'] ?>" id="status-<?= $k['id'] ?>">
                            <?= ucfirst($k['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($k['status'] === 'published'): ?>
                            <div class="text-sm font-medium"><?= esc($k['title']) ?></div>
                        <?php elseif ($k['status'] === 'failed'): ?>
                            <div class="text-xs text-danger" title="<?= esc($k['error_message']) ?>">
                                <?= substr(esc($k['error_message']), 0, 50) . (strlen($k['error_message']) > 50 ? '...' : '') ?>
                            </div>
                        <?php else: ?>
                            <span class="text-slate text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($k['published_at']): ?>
                            <div class="text-xs"><?= date('d/m/Y H:i', strtotime($k['published_at'])) ?></div>
                            <a href="<?= esc($k['wordpress_url']) ?>" target="_blank" class="text-xs text-blue-600 no-underline hover:underline">Ver post 🔗</a>
                        <?php else: ?>
                            <span class="text-slate text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex-gap-5">
                            <?php if ($k['status'] !== 'published' && $k['status'] !== 'generating'): ?>
                                <button onclick="generateOne(<?= $k['id'] ?>)" class="btn secondary btn-sm" id="btn-gen-<?= $k['id'] ?>">Generar</button>
                            <?php endif; ?>
                            <?php if ($k['status'] === 'failed'): ?>
                                <button onclick="generateOne(<?= $k['id'] ?>)" class="btn ghost btn-sm" id="btn-retry-<?= $k['id'] ?>">Reintentar</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const csrfToken = '<?= csrf_hash() ?>';
    const csrfName = '<?= csrf_token() ?>';

    async function generateOne(id) {
        const btn = document.getElementById('btn-gen-' + id) || document.getElementById('btn-retry-' + id);
        const statusPill = document.getElementById('status-' + id);
        
        if (btn) btn.disabled = true;
        if (statusPill) {
            statusPill.className = 'status-pill status-generating';
            statusPill.innerHTML = '<span class="loader-inline"></span> Generando...';
        }

        try {
            const formData = new FormData();
            formData.append(csrfName, csrfToken);

            const response = await fetch(`<?= site_url('admin/seo-auto-posts/generate-one/') ?>${id}`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: result.message,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Swal.fire('Error', result.message, 'error');
                if (statusPill) {
                    statusPill.className = 'status-pill status-failed';
                    statusPill.innerHTML = 'Failed';
                }
                if (btn) btn.disabled = false;
            }
        } catch (error) {
            Swal.fire('Error', 'Error de red o servidor: ' + error.message, 'error');
            if (btn) btn.disabled = false;
        }
    }

    document.getElementById('btn-generate-batch').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="loader-inline"></span> Procesando lote...';

        try {
            const formData = new FormData();
            formData.append(csrfName, csrfToken);

            const response = await fetch('<?= site_url('admin/seo-auto-posts/generate-batch') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire({
                    title: 'Lote Completado',
                    text: result.message,
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Info', result.message, 'info');
            }
        } catch (error) {
            Swal.fire('Error', 'Error de red o servidor: ' + error.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Generar posts pendientes';
        }
    });
</script>

<?= view('partials/footer') ?>
</body>
</html>
