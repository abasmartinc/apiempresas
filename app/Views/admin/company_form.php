<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="max-width: 900px; margin: 0 auto;">
        <a href="<?= site_url('admin/companies') ?>" class="minor" style="display: inline-block; margin-bottom: 1rem;">← Volver al listado</a>
        
        <div class="card">
            <h1 class="title" style="font-size: 1.8rem;"><?= $company ? 'Editar Empresa' : 'Nueva Empresa' ?></h1>
            
            <form action="<?= $company ? site_url('admin/companies/update') : site_url('admin/companies/store') ?>" method="post" class="grid" style="gap: 2rem;">
                <?= csrf_field() ?>
                <?php if ($company): ?>
                    <input type="hidden" name="id" value="<?= $company->id ?>">
                <?php endif; ?>

                <!-- Sección: Información General -->
                <div>
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #475569;">Información General</h3>
                    <div class="grid-2">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Nombre de la Empresa</label>
                            <input type="text" name="company_name" class="input" style="width: 100%;" required value="<?= old('company_name', $company->company_name ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">CIF</label>
                            <input type="text" name="cif" class="input" style="width: 100%;" required value="<?= old('cif', $company->cif ?? '') ?>">
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Objeto Social</label>
                        <textarea name="objeto_social" class="input" style="width: 100%; min-height: 100px;"><?= old('objeto_social', $company->objeto_social ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Sección: Ubicación y Contacto -->
                <div>
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #475569;">Ubicación y Contacto</h3>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Dirección</label>
                        <input type="text" name="address" class="input" style="width: 100%;" value="<?= old('address', $company->address ?? '') ?>">
                    </div>
                    <div class="grid-2">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Municipio</label>
                            <input type="text" name="municipality" class="input" style="width: 100%;" value="<?= old('municipality', $company->municipality ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Provincia (Registro Mercantil)</label>
                            <input type="text" name="registro_mercantil" class="input" style="width: 100%;" value="<?= old('registro_mercantil', $company->registro_mercantil ?? '') ?>">
                        </div>
                    </div>
                    <div class="grid-2" style="margin-top: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Teléfono</label>
                            <input type="text" name="phone" class="input" style="width: 100%;" value="<?= old('phone', $company->phone ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Móvil</label>
                            <input type="text" name="phone_mobile" class="input" style="width: 100%;" value="<?= old('phone_mobile', $company->phone_mobile ?? '') ?>">
                        </div>
                    </div>
                    <div class="grid-2" style="margin-top: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Latitud</label>
                            <input type="text" name="lat" class="input" style="width: 100%;" value="<?= old('lat', $company->lat ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Longitud</label>
                            <input type="text" name="long" class="input" style="width: 100%;" value="<?= old('long', $company->long ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Sección: Actividad y Estado -->
                <div>
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #475569;">Actividad y Estado</h3>
                    <div class="grid-2">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Código CNAE</label>
                            <input type="text" name="cnae_code" class="input" style="width: 100%;" value="<?= old('cnae_code', $company->cnae_code ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Estado</label>
                            <select name="estado" class="input" style="width: 100%;">
                                <option value="ACTIVA" <?= old('estado', $company->estado ?? '') === 'ACTIVA' ? 'selected' : '' ?>>ACTIVA</option>
                                <option value="EXTINGUIDA" <?= old('estado', $company->estado ?? '') === 'EXTINGUIDA' ? 'selected' : '' ?>>EXTINGUIDA</option>
                                <option value="DISUELTA" <?= old('estado', $company->estado ?? '') === 'DISUELTA' ? 'selected' : '' ?>>DISUELTA</option>
                                <option value="CONCURSO" <?= old('estado', $company->estado ?? '') === 'CONCURSO' ? 'selected' : '' ?>>CONCURSO</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Etiqueta CNAE</label>
                        <input type="text" name="cnae_label" class="input" style="width: 100%;" value="<?= old('cnae_label', $company->cnae_label ?? '') ?>">
                    </div>
                    <div class="grid-2" style="margin-top: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha Constitución</label>
                            <input type="date" name="fecha_constitucion" class="input" style="width: 100%;" value="<?= old('fecha_constitucion', $company->fecha_constitucion ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha Estado</label>
                            <input type="date" name="estado_fecha" class="input" style="width: 100%;" value="<?= old('estado_fecha', $company->estado_fecha ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Sección: Datos Financieros y Otros -->
                <div>
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #475569;">Datos Financieros y Otros</h3>
                    <div class="grid-2">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Ventas (Raw)</label>
                            <input type="text" name="ventas_raw" class="input" style="width: 100%;" value="<?= old('ventas_raw', $company->ventas_raw ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Capital Social (Raw)</label>
                            <input type="text" name="capital_social_raw" class="input" style="width: 100%;" value="<?= old('capital_social_raw', $company->capital_social_raw ?? '') ?>">
                        </div>
                    </div>
                    <div class="grid-2" style="margin-top: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Últimas Cuentas (Año)</label>
                            <input type="number" name="ult_cuentas_anio" class="input" style="width: 100%;" value="<?= old('ult_cuentas_anio', $company->ult_cuentas_anio ?? '') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Slug</label>
                            <input type="text" name="slug" class="input" style="width: 100%;" value="<?= old('slug', $company->slug ?? '') ?>">
                        </div>
                    </div>
                    <div style="margin-top: 1rem; display: flex; gap: 2rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="processed" value="1" <?= old('processed', $company->processed ?? 0) ? 'checked' : '' ?>>
                            Procesada
                        </label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                    <button type="submit" class="btn"><?= $company ? 'Actualizar Empresa' : 'Crear Empresa' ?></button>
                    <a href="<?= site_url('admin/companies') ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>

