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
            <h1 class="title" style="font-size: 1.8rem;">Redactar Email</h1>
            <p style="color: #64748b; margin-bottom: 2rem;">
                Enviando a: <strong><?= esc($user->name) ?></strong> (<?= esc($user->email) ?>)
            </p>

            <form action="<?= site_url('admin/users/send') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= $user->id ?>">

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 12px; margin-bottom: 0.5rem;">
                    <label style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b;">Cargar Plantilla</label>
                    <select id="template-selector" class="input" style="width: 100%; border-color: #cbd5e1;">
                        <option value="">-- Selecciona una plantilla --</option>
                        <?php foreach ($templates as $tpl): ?>
                            <option value="<?= $tpl['slug'] ?>" data-subject="<?= esc($tpl['subject']) ?>" data-body="<?= esc($tpl['body']) ?>">
                                <?= $tpl['slug'] === 'custom' ? 'Nueva personalizada' : esc($tpl['subject']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="margin-top: 10px; font-size: 0.75rem; color: #64748b; display: flex; gap: 10px;">
                        <span>Variables: <strong>{NOMBRE}</strong>, <strong>{EMPRESA}</strong>, <strong>{SITE_URL}</strong></span>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Asunto</label>
                    <input type="text" id="email-subject" name="subject" class="input" style="width: 100%;" required placeholder="Ej: Novedades en tu cuenta...">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Mensaje</label>
                    <textarea id="email-body" name="message" class="input" style="width: 100%; min-height: 250px; font-family: sans-serif;" placeholder="Escribe tu mensaje aquí..."></textarea>
                    <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">Se enviará con la plantilla corporativa.</p>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" class="btn">Enviar Email</button>
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

    // Template Selection Logic
    document.getElementById('template-selector').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const subject = option.getAttribute('data-subject') || '';
        const body = option.getAttribute('data-body') || '';

        document.getElementById('email-subject').value = subject;
        
        if (tinymce.get('email-body')) {
            // Replace newlines with <br> for TinyMCE
            const htmlBody = body.replace(/\n/g, '<br>');
            tinymce.get('email-body').setContent(htmlBody);
        } else {
            document.getElementById('email-body').value = body;
        }
    });

    // Ensure content is synced on submit
    document.querySelector('form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
</script>
</body>
</html>

