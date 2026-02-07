<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="max-width: 700px; margin: 0 auto;">
        <a href="<?= site_url('admin/users') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;">Enviar Email Masivo</h1>
            <p style="color: #64748b; margin-bottom: 2rem;">
                Estás a punto de enviar un correo a: <strong><?= esc($target_description) ?></strong>.
            </p>

            <form action="<?= site_url('admin/users/email/send-bulk') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                
                <?php foreach ($hidden_inputs as $key => $value): ?>
                    <?php if ($value !== null): ?>
                        <input type="hidden" name="<?= $key ?>" value="<?= esc($value) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Asunto</label>
                    <input type="text" name="subject" class="input" style="width: 100%;" required placeholder="Ej: Aviso importante..." autofocus>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Mensaje</label>
                    <textarea name="message" class="input" style="width: 100%; min-height: 250px; font-family: sans-serif;" placeholder="Escribe tu mensaje aquí..."></textarea>
                    <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">Se enviará con la plantilla corporativa.</p>
                </div>

                <div class="alert alert-warning" style="background: #fffbeb; color: #92400e; padding: 1rem; border: 1px solid #fcd34d; border-radius: 8px;">
                    <strong>Confirmación:</strong> Se enviarán <strong><?= $count ?></strong> correos electrónicos. Esta acción puede tardar unos segundos.
                </div>

                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" class="btn" onclick="return confirm('¿Estás seguro de enviar este correo a <?= $count ?> usuarios?');">Enviar a <?= $count ?> usuarios</button>
                    <a href="<?= site_url('admin/users') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.5.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea[name="message"]',
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
        height: 350,
        branding: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    document.querySelector('form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
</script>
</body>
</html>
