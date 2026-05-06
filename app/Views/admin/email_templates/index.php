<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-top: 30px;
        }
        .template-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
            border-color: #2152ff;
        }
        .template-badge {
            background: #f1f5f9;
            color: #64748b;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            width: fit-content;
        }
        .template-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
        }
        .template-subject {
            font-size: 0.9rem;
            color: #64748b;
            font-style: italic;
        }
        .template-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }
        .btn-template {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-edit {
            background: #2152ff;
            color: white;
        }
        .btn-edit:hover {
            background: #1a41cc;
        }
        .btn-preview {
            background: #f1f5f9;
            color: #64748b;
        }
        .btn-preview:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
    </style>
</head>
<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <h1 class="title" style="font-size: 2.5rem; margin-bottom: 8px;">Gestor de <span class="grad">Emails</span></h1>
                <p style="color: #64748b;">Administra el contenido y diseño de los correos automáticos del sistema.</p>
            </div>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>

        <?php if (session('message')): ?>
            <div class="alert success" style="margin-bottom: 30px;"><?= session('message') ?></div>
        <?php endif; ?>

        <div class="template-grid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card">
                    <div class="template-badge"><?= esc($template->slug) ?></div>
                    <div class="template-name"><?= esc($template->name) ?></div>
                    <div class="template-subject"><b>Asunto:</b> <?= esc($template->subject) ?></div>
                    
                    <?php 
                        $parts = explode('|', $template->description);
                        $trigger = trim($parts[0] ?? '');
                        $vars = trim(str_replace('Variables:', '', $parts[1] ?? ''));
                    ?>

                    <div style="font-size: 0.85rem; color: #475569; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px dashed #e2e8f0;">
                        <b style="color: #1e293b; display: block; margin-bottom: 4px;">Cuándo se envía:</b>
                        <?= esc($trigger) ?>
                    </div>

                    <div style="font-size: 0.8rem; color: #94a3b8;">
                        <b>Variables:</b> <?= esc($vars) ?>
                    </div>
                    <div class="template-actions">
                        <a href="<?= site_url('admin/email-templates/edit/' . $template->id) ?>" class="btn-template btn-edit">Editar Contenido</a>
                        <a href="<?= site_url('admin/email-templates/preview/' . $template->id) ?>" target="_blank" class="btn-template btn-preview">Previsualizar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
