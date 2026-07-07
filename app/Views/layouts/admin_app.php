<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Admin Dashboard']) ?>
    <?= $this->renderSection('styles') ?>
</head>
<body class="admin-body" hx-ext="ajax-header" hx-boost="true" hx-target="#admin-app-content" hx-push-url="true" hx-swap="innerHTML transition:true">
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;" id="admin-app-content">
        <?= $this->renderSection('content') ?>
    </main>

    <?= view('partials/footer') ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
