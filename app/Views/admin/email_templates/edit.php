<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .edit-container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            display: block;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .form-input {
            width: 100%;
            padding: 14px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f8fafc;
        }
        .form-input:focus {
            border-color: #2152ff;
            background: white;
            outline: none;
            box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1);
        }
        .form-textarea {
            width: 100%;
            height: 500px;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-family: 'Fira Code', 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            transition: all 0.3s;
            background: #1e293b;
            color: #e2e8f0;
            resize: vertical;
        }
        .form-textarea:focus {
            border-color: #2152ff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1);
        }
        .variable-tag {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.85rem;
            margin-right: 5px;
            margin-bottom: 5px;
            cursor: pointer;
        }
        .variable-tag:hover {
            background: #4338ca;
            color: white;
        }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;">
        <div style="margin-bottom: 40px;">
            <a href="<?= site_url('admin/email-templates') ?>" style="color: #64748b; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                &larr; Volver al listado
            </a>
            <h1 class="title" style="font-size: 2.5rem; margin-bottom: 8px;">Editar <span class="grad">Plantilla</span></h1>
            <p style="color: #64748b;">Modificando: <strong><?= esc($template->name) ?></strong> (<?= esc($template->slug) ?>)</p>
        </div>

        <form action="<?= site_url('admin/email-templates/update/' . $template->id) ?>" method="POST">
            <?= csrf_field() ?>
            <div class="edit-container">
                <div class="form-group">
                    <label class="form-label">Asunto del Correo</label>
                    <input type="text" name="subject" class="form-input" value="<?= esc($template->subject) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción / Variables Disponibles</label>
                    <input type="text" name="description" class="form-input" value="<?= esc($template->description) ?>">
                    <div style="margin-top: 10px;">
                        <span style="font-size: 0.85rem; color: #64748b; margin-right: 10px;">Variables:</span>
                        <?php 
                            // Intentar extraer variables de la descripción para mostrarlas como tags
                            preg_match_all('/\{([a-z_]+)\}/', $template->description . $template->body, $matches);
                            $vars = array_unique($matches[0] ?? []);
                            foreach($vars as $v):
                        ?>
                            <span class="variable-tag" onclick="copyToClipboard('<?= $v ?>')"><?= $v ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cuerpo del Correo (HTML)</label>
                    <textarea name="body" class="form-textarea" spellcheck="false"><?= esc($template->body) ?></textarea>
                </div>

                <div class="actions-bar">
                    <button type="submit" class="btn btn-full" style="padding: 15px 40px;">Guardar Cambios</button>
                    <a href="<?= site_url('admin/email-templates/preview/' . $template->id) ?>" target="_blank" class="btn ghost">Previsualizar ahora</a>
                </div>
            </div>
        </form>
    </main>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Opcional: mostrar un toast o aviso
                console.log('Copiado: ' + text);
            });
        }
    </script>

    <?= view('partials/footer') ?>
</body>
</html>
