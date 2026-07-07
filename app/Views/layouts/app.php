<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title ?? 'APIEmpresas Dashboard']) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css?v=' . (file_exists(FCPATH . 'public/css/dashboard.css') ? filemtime(FCPATH . 'public/css/dashboard.css') : time())) ?>" />
    <?= $this->renderSection('styles') ?>
</head>
<body hx-ext="ajax-header" hx-boost="true" hx-target="#app-content" hx-push-url="true" hx-swap="innerHTML transition:true">

<div class="auth-wrapper">
    <?= view('partials/header_inner') ?>

    <main class="dash-main" id="app-content">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<?= view('partials/footer') ?>
<?= $this->renderSection('scripts') ?>

</body>
</html>
