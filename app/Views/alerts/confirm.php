<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => 'Confirmar Alerta - APIEmpresas']) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding" style="max-width: 600px; margin: 0 auto; min-height: 80vh; display: flex; flex-direction: column; justify-content: center;">
    
    <div class="card p-10 text-center">
        <div style="font-size: 3rem; margin-bottom: 2rem;">🔔</div>
        
        <h1 class="title" style="margin-bottom: 1rem;">Recibir alertas de <?= esc($company_name) ?></h1>
        
        <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">
            Estás a punto de activar el seguimiento para la empresa <strong><?= esc($company_name) ?></strong> (<?= esc($cif) ?>).
            <br><br>
            Te enviaremos un correo electrónico automáticamente cada vez que detectemos una nueva publicación en el BORME relacionada con esta empresa.
        </p>

        <form action="<?= site_url('alerts/add') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="cif" value="<?= esc($cif) ?>">
            <input type="hidden" name="company_name" value="<?= esc($company_name) ?>">
            
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="<?= site_url() ?>" class="btn ghost">Cancelar</a>
                <button type="submit" class="btn primary">Confirmar y Activar</button>
            </div>
        </form>
    </div>

</main>

<?= view('partials/footer') ?>
</body>
</html>
