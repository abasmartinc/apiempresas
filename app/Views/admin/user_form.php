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
        <a href="<?= site_url('admin/users') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;"><?= $user ? 'Editar Usuario' : 'Crear Usuario' ?></h1>
            
            <form action="<?= $user ? site_url('admin/users/update') : site_url('admin/users/store') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                <?php if ($user): ?>
                    <input type="hidden" name="id" value="<?= $user->id ?>">
                <?php endif; ?>

                <div class="grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Nombre Completo</label>
                        <input type="text" name="name" class="input" style="width: 100%;" required value="<?= old('name', $user->name ?? '') ?>">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Empresa (Opcional)</label>
                        <input type="text" name="company" class="input" style="width: 100%;" value="<?= old('company', $user->company ?? '') ?>">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Correo Electrónico</label>
                    <input type="email" name="email" class="input" style="width: 100%;" required value="<?= old('email', $user->email ?? '') ?>">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Contraseña <?= $user ? '(Dejar en blanco para no cambiar)' : '' ?></label>
                    <input type="password" name="password" class="input" style="width: 100%;" <?= $user ? '' : 'required' ?>>
                </div>

                <div style="display: flex; gap: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= old('is_active', $user->is_active ?? 1) ? 'checked' : '' ?>>
                        Usuario Activo
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_admin" value="1" <?= old('is_admin', $user->is_admin ?? 0) ? 'checked' : '' ?>>
                        Administrador
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                    <button type="submit" class="btn"><?= $user ? 'Actualizar Usuario' : 'Crear Usuario' ?></button>
                    <a href="<?= site_url('admin/users') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
