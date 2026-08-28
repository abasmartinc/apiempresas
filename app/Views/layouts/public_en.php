<!doctype html>
<html lang="en">
<head>
    <?= view('partials/head', ['title' => $title ?? 'SpainCompanyAPI']) ?>
    <?= $this->renderSection('styles') ?>
</head>
<body hx-ext="ajax-header" hx-boost="true" hx-target="#app-content" hx-push-url="true" hx-swap="innerHTML transition:true">

<div class="auth-wrapper" style="display: flex; flex-direction: column; min-height: 100vh; background: var(--bg-dashboard);">
    <?= view('partials/header_en') ?>

    <main class="dash-main" id="app-content" style="flex: 1;">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<?= view('partials/footer_en') ?>
<?= $this->renderSection('scripts') ?>

</body>
</html>
