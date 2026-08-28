<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title ?? 'APIEmpresas']) ?>
    <?= $this->renderSection('styles') ?>
</head>
<body hx-ext="ajax-header" hx-boost="true" hx-target="#app-content" hx-push-url="true" hx-swap="innerHTML transition:true">

<div class="auth-wrapper">
    <?= view('partials/header') ?>

    <main class="dash-main" id="app-content">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<?php if (service('request')->getLocale() === 'en'): ?>
    <?= view('partials/footer_en') ?>
<?php else: ?>
    <?= view('partials/footer') ?>
<?php endif; ?>
<?= $this->renderSection('scripts') ?>

</body>
</html>
