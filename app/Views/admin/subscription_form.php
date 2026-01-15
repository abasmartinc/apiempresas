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
        <a href="<?= site_url('admin/subscriptions') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;"><?= $subscription ? 'Editar Suscripción' : 'Nueva Suscripción' ?></h1>
            
            <form action="<?= $subscription ? site_url('admin/subscriptions/update') : site_url('admin/subscriptions/store') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                <?php if ($subscription): ?>
                    <input type="hidden" name="id" value="<?= $subscription->id ?>">
                <?php endif; ?>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Usuario</label>
                        <select name="user_id" class="input" style="width: 100%;" required>
                            <option value="">Selecciona un usuario...</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->id ?>" <?= old('user_id', $subscription->user_id ?? '') == $u->id ? 'selected' : '' ?>>
                                    <?= esc($u->name) ?> (<?= esc($u->email) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Plan API</label>
                        <select name="plan_id" class="input" style="width: 100%;" required>
                            <option value="">Selecciona un plan...</option>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= $p->id ?>" <?= old('plan_id', $subscription->plan_id ?? '') == $p->id ? 'selected' : '' ?>>
                                    <?= esc($p->name) ?> (<?= number_format($p->price_monthly, 2) ?> €/mes)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Estado</label>
                        <select name="status" class="input" style="width: 100%;" required>
                            <option value="active" <?= old('status', $subscription->status ?? '') === 'active' ? 'selected' : '' ?>>Activa</option>
                            <option value="trialing" <?= old('status', $subscription->status ?? '') === 'trialing' ? 'selected' : '' ?>>Prueba (Trialing)</option>
                            <option value="past_due" <?= old('status', $subscription->status ?? '') === 'past_due' ? 'selected' : '' ?>>Pago Pendiente (Past Due)</option>
                            <option value="canceled" <?= old('status', $subscription->status ?? '') === 'canceled' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha Cancelación (Opcional)</label>
                        <input type="datetime-local" name="canceled_at" class="input" style="width: 100%;" value="<?= old('canceled_at', isset($subscription->canceled_at) ? date('Y-m-d\TH:i', strtotime($subscription->canceled_at)) : '') ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Inicio Periodo Actual</label>
                        <input type="datetime-local" name="current_period_start" class="input" style="width: 100%;" required value="<?= old('current_period_start', isset($subscription->current_period_start) ? date('Y-m-d\TH:i', strtotime($subscription->current_period_start)) : date('Y-m-d\TH:i')) ?>">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fin Periodo Actual</label>
                        <input type="datetime-local" name="current_period_end" class="input" style="width: 100%;" required value="<?= old('current_period_end', isset($subscription->current_period_end) ? date('Y-m-d\TH:i', strtotime($subscription->current_period_end)) : date('Y-m-d\TH:i', strtotime('+1 month'))) ?>">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                    <button type="submit" class="btn"><?= $subscription ? 'Actualizar Suscripción' : 'Crear Suscripción' ?></button>
                    <a href="<?= site_url('admin/subscriptions') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
