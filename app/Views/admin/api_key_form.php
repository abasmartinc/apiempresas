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
        <a href="<?= site_url('admin/api-keys') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;"><?= $key ? 'Editar API Key' : 'Nueva API Key' ?></h1>
            
            <form action="<?= $key ? site_url('admin/api-keys/update') : site_url('admin/api-keys/store') ?>" method="post" class="grid" style="gap: 1.5rem;">
                <?= csrf_field() ?>
                <?php if ($key): ?>
                    <input type="hidden" name="id" value="<?= $key->id ?>">
                <?php endif; ?>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Usuario Propietario</label>
                    <select name="user_id" class="input" style="width: 100%;" required>
                        <option value="">Selecciona un usuario...</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u->id ?>" <?= old('user_id', $key->user_id ?? '') == $u->id ? 'selected' : '' ?>>
                                <?= esc($u->name) ?> (<?= esc($u->email) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Nombre de la Key (Identificador)</label>
                    <input type="text" name="name" class="input" style="width: 100%;" required value="<?= old('name', $key->name ?? '') ?>" placeholder="Ej: Producción, Testing...">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">API Key</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="api_key" id="api_key_input" class="input" style="width: 100%; font-family: monospace;" required value="<?= old('api_key', $key->api_key ?? $generated_key ?? '') ?>">
                        <?php if (!$key): ?>
                            <button type="button" class="btn ghost" onclick="generateNewKey()" style="white-space: nowrap;">Generar otra</button>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">Esta es la clave privada que el usuario usará para autenticarse en la API.</p>
                </div>

                <div style="display: flex; gap: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= old('is_active', $key->is_active ?? 1) ? 'checked' : '' ?>>
                        API Key Activa
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                    <button type="submit" class="btn"><?= $key ? 'Actualizar API Key' : 'Crear API Key' ?></button>
                    <a href="<?= site_url('admin/api-keys') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function generateNewKey() {
    const chars = 'abcdef0123456789';
    let result = '';
    for (let i = 0; i < 64; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('api_key_input').value = result;
}
</script>

<?= view('partials/footer') ?>
</body>
</html>

