<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?= site_url() ?>">
                <span class="brand-name">API<span class="grad">Empresas</span> Admin</span>
            </a>
        </div>
    </div>
</header>

<main class="container" style="padding: 40px 0;">
    <div style="max-width: 700px; margin: 0 auto;">
        <a href="<?= site_url('admin/plans') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;"><?= $plan ? 'Editar Plan' : 'Nuevo Plan' ?></h1>
            
            <form action="<?= $plan ? site_url('admin/plans/update') : site_url('admin/plans/store') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                <?php if ($plan): ?>
                    <input type="hidden" name="id" value="<?= $plan->id ?>">
                <?php endif; ?>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Nombre del Plan</label>
                        <input type="text" name="name" class="input" style="width: 100%;" required value="<?= old('name', $plan->name ?? '') ?>" placeholder="Ej: Plan Professional">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Slug (Identificador)</label>
                        <input type="text" name="slug" class="input" style="width: 100%;" required value="<?= old('slug', $plan->slug ?? '') ?>" placeholder="ej-plan-professional">
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Cuota Mensual (Peticiones)</label>
                        <input type="number" name="monthly_quota" class="input" style="width: 100%;" required value="<?= old('monthly_quota', $plan->monthly_quota ?? 1000) ?>">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Rate Limit (Peticiones/min)</label>
                        <input type="number" name="rate_limit_per_min" class="input" style="width: 100%;" required value="<?= old('rate_limit_per_min', $plan->rate_limit_per_min ?? 60) ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Precio Mensual (€)</label>
                        <input type="number" step="0.01" name="price_monthly" class="input" style="width: 100%;" required value="<?= old('price_monthly', $plan->price_monthly ?? 0.00) ?>">
                    </div>
                    <div style="display: flex; align-items: flex-end; padding-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= old('is_active', $plan->is_active ?? 1) ? 'checked' : '' ?>>
                            Plan Activo
                        </label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                    <button type="submit" class="btn"><?= $plan ? 'Actualizar Plan' : 'Crear Plan' ?></button>
                    <a href="<?= site_url('admin/plans') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
