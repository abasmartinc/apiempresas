<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
<?= view('admin/partials/panel_estilos') ?>
<style>
    .pr-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .pr-tab { padding: 8px 14px; border-radius: 999px; border: 1px solid #cbd5e1; background: #fff; color: #334155; font-weight: 700; font-size: .85rem; text-decoration: none; white-space: nowrap; }
    .pr-tab:hover { border-color: #0f172a; }
    .pr-tab.is-on { background: #0f172a; border-color: #0f172a; color: #fff; }
    .pr-filtros { display: flex; flex-wrap: wrap; gap: 10px 14px; align-items: flex-end; padding: 14px 16px; margin-bottom: 14px; }
    .pr-filtros label { display: flex; flex-direction: column; gap: 4px; font-size: .72rem; font-weight: 800; color: var(--ink-2); text-transform: uppercase; letter-spacing: .04em; }
    .pr-filtros select, .pr-filtros input[type=text], .pr-filtros input[type=number] { min-width: 150px; max-width: 280px; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: .88rem; background: #fff; color: #0f172a; text-transform: none; letter-spacing: 0; font-weight: 500; }
    .pr-check { flex-direction: row !important; align-items: center; gap: 6px !important; text-transform: none !important; letter-spacing: 0 !important; font-size: .85rem !important; font-weight: 600 !important; color: #0f172a !important; padding-bottom: 8px; }
    .pr-actos { display: flex; flex-wrap: wrap; gap: 4px 12px; max-width: 560px; }
    .pr-btn { border: 0; border-radius: 9px; padding: 8px 14px; font-weight: 800; font-size: .85rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .pr-btn--ok { background: #0f172a; color: #fff; }
    .pr-btn--ghost { background: #f1f5f9; color: #334155; }
    .pr-acciones { display: flex; gap: 8px; margin-left: auto; padding-bottom: 1px; }
    .pr-info { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: .82rem; color: var(--ink-2); margin: 0 0 8px; }
    .pr-table { width: 100%; border-collapse: collapse; min-width: 1100px; background: #fff; }
    .pr-table th { padding: 10px 10px; color: #64748b; font-size: .74rem; text-align: left; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .pr-table td { padding: 10px 10px; border-bottom: 1px solid #f1f5f9; font-size: .85rem; vertical-align: top; }
    .pr-table tr.is-desc td { opacity: .55; }
    .pr-nombre { font-weight: 800; color: #0f172a; text-decoration: none; }
    .pr-nombre:hover { text-decoration: underline; }
    .pr-muted { color: #64748b; font-size: .78rem; line-height: 1.45; }
    .pr-obj { color: #64748b; font-size: .76rem; max-width: 340px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-top: 3px; }
    .pr-score { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 28px; border-radius: 8px; font-weight: 900; font-variant-numeric: tabular-nums; }
    .pr-score--alto { background: #dcfce7; color: #166534; }
    .pr-score--medio { background: #fef3c7; color: #92400e; }
    .pr-score--bajo { background: #f1f5f9; color: #475569; }
    .pr-chips { display: flex; flex-wrap: wrap; gap: 4px; max-width: 260px; }
    .pr-chip { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .pr-chip--nueva { background: #eff6ff; color: #1d4ed8; }
    .pr-chip--web { background: #fdf2f8; color: #be185d; }
    .pr-chip--contacto { background: #f0fdf4; color: #15803d; }
    .pr-chip--subv { background: #fffbeb; color: #b45309; }
    .pr-chip--contratos { background: #f5f3ff; color: #6d28d9; }
    .pr-chip--borme { background: #ecfeff; color: #0e7490; }
    .pr-chip--admin { background: #f0f9ff; color: #0369a1; }
    .pr-contacto { min-width: 250px; max-width: 320px; }
    .pr-contacto .pr-dato { color: #0f172a; text-decoration: none; display: block; white-space: nowrap; font-weight: 700; }
    .pr-contacto .pr-dato:hover { text-decoration: underline; }
    .pr-dir { font-size: .78rem; color: #475569; margin-top: 2px; }
    .pr-dir a { color: #475569; }
    .pr-adm { margin-top: 6px; font-size: .8rem; line-height: 1.4; }
    .pr-adm b { color: #0f172a; }
    .pr-links { display: inline-flex; gap: 4px; flex-wrap: wrap; margin-left: 4px; vertical-align: 1px; }
    .pr-link { display: inline-block; padding: 1px 7px; border-radius: 6px; background: #f1f5f9; color: #334155 !important; font-size: .7rem; font-weight: 800; text-decoration: none !important; white-space: nowrap; }
    .pr-link:hover { background: #e2e8f0; }
    .pr-link--li { background: #e8f1fb; color: #0a66c2 !important; }
    .pr-buscar { margin-top: 7px; display: flex; gap: 4px; flex-wrap: wrap; align-items: center; }
    .pr-buscar button { border: 1px dashed #94a3b8; background: #fff; border-radius: 6px; padding: 1px 8px; font-size: .7rem; font-weight: 800; color: #334155; cursor: pointer; }
    .pr-web-res { margin-top: 5px; font-size: .78rem; }
    .pr-buscar .pr-redactar { border: 0; background: #0f172a; color: #fff; border-radius: 6px; padding: 2px 9px; font-size: .72rem; font-weight: 800; cursor: pointer; }
    .pr-motivos { margin-top: 6px; }
    .pr-motivo { font-size: .72rem; line-height: 1.35; }
    .pr-motivo--mas { color: #15803d; }
    .pr-motivo--menos { color: #b91c1c; }
    .pr-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, .45); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
    .pr-modal.is-on { display: flex; }
    .pr-modal__box { background: #fff; border-radius: 16px; width: 720px; max-width: 100%; max-height: 92vh; overflow: auto; padding: 20px 22px; box-shadow: 0 20px 50px rgba(15, 23, 42, .3); }
    .pr-modal__box h3 { margin: 0 0 4px; font-size: 1.05rem; }
    .pr-modal__box label { display: block; font-size: .72rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: .04em; margin: 12px 0 4px; }
    .pr-modal__box input[type=text], .pr-modal__box textarea { width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 9px; font: inherit; font-size: .9rem; }
    .pr-modal__box textarea { min-height: 300px; line-height: 1.5; }
    .pr-modal__acciones { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; align-items: center; }
    .pr-modal__nota { font-size: .76rem; color: #64748b; margin-top: 8px; }
    .pr-web { margin-top: 3px; }
    .pr-web a { font-weight: 800; color: #0f172a; }
    .pr-web--ok a { color: #15803d; }
    .pr-web--aviso a { color: #b45309; }
    .pr-web--no a { color: #64748b; }
    .pr-seg select { padding: 5px 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: .8rem; font-weight: 700; background: #fff; max-width: 160px; }
    .pr-seg select[data-v=contacted] { background: #eff6ff; border-color: #93c5fd; }
    .pr-seg select[data-v=in_talks] { background: #fef3c7; border-color: #fcd34d; }
    .pr-seg select[data-v=client] { background: #dcfce7; border-color: #86efac; }
    .pr-seg select[data-v=discarded] { background: #f1f5f9; }
    .pr-seg select[data-v=pending], .pr-seg select[data-v=interested], .pr-seg select[data-v=preparing] { background: #f5f3ff; border-color: #c4b5fd; }
    .pr-seg select[data-v=submitted] { background: #eff6ff; border-color: #93c5fd; }
    .pr-seg select[data-v=won] { background: #dcfce7; border-color: #86efac; }
    .pr-seg select[data-v=lost] { background: #fef2f2; border-color: #fecaca; }
    .pr-seg details { margin-top: 6px; }
    .pr-seg summary { cursor: pointer; font-size: .78rem; color: #2563eb; font-weight: 700; list-style: none; }
    .pr-seg summary::-webkit-details-marker { display: none; }
    .pr-seg textarea { width: 230px; min-height: 64px; margin-top: 6px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; font-size: .82rem; }
    .pr-seg input[type=date] { margin-top: 4px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: .8rem; }
    .pr-seg .pr-nota { font-size: .76rem; color: #475569; margin-top: 4px; max-width: 230px; white-space: pre-line; }
    .pr-ok { font-size: .74rem; font-weight: 800; color: #15803d; margin-left: 6px; }
    .pr-err { font-size: .74rem; font-weight: 800; color: #b91c1c; margin-left: 6px; }
    .pr-plazo { display: inline-block; padding: 3px 9px; border-radius: 8px; font-weight: 800; font-size: .78rem; white-space: nowrap; }
    .pr-plazo--rojo { background: #fef2f2; color: #b91c1c; }
    .pr-plazo--ambar { background: #fffbeb; color: #b45309; }
    .pr-plazo--verde { background: #f0fdf4; color: #15803d; }
    .pr-mini { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .pr-mini th, .pr-mini td { padding: 5px 6px; font-size: .78rem; border-bottom: 1px solid #f1f5f9; text-align: left; vertical-align: top; }
    .pr-comp summary { cursor: pointer; font-size: .78rem; color: #2563eb; font-weight: 700; }
    .pr-comp-btn { border: 0; background: none; padding: 0; margin-top: 3px; color: #2563eb; font-weight: 700; font-size: .78rem; cursor: pointer; }
    .pr-comp-btn:hover { text-decoration: underline; }
    .pr-origen { display: inline-block; margin-left: 4px; padding: 1px 6px; border-radius: 6px; background: #f1f5f9; color: #64748b; font-size: .66rem; font-weight: 800; vertical-align: 4px; }
    .pr-origen--ia { background: #ede9fe; color: #6d28d9; }
    .pr-que { margin: 4px 0 2px; font-size: .82rem; color: #334155; background: #f8fafc; border-left: 3px solid #a78bfa; padding: 4px 8px; border-radius: 0 6px 6px 0; }
    .pr-lic td { vertical-align: top; padding-top: 14px; padding-bottom: 14px; }
    .pr-ver { display: inline-block; padding: 5px 11px; border-radius: 999px; font-weight: 900; font-size: .85rem; white-space: nowrap; }
    .pr-ver--si { background: #dcfce7; color: #166534; }
    .pr-ver--quiza { background: #fef3c7; color: #92400e; }
    .pr-ver--no { background: #f1f5f9; color: #64748b; }
    .pr-ver--pendiente { background: #fff; color: #64748b; border: 1px dashed #cbd5e1; }
    .pr-lic-titulo { font-weight: 800; color: #0f172a; text-decoration: none; font-size: .95rem; line-height: 1.35; }
    .pr-lic-titulo:hover { text-decoration: underline; }
    .pr-lic-resumen { margin-top: 5px; font-size: .9rem; color: #334155; line-height: 1.45; }
    .pr-lic-meta { margin-top: 7px; display: flex; flex-wrap: wrap; gap: 4px 14px; align-items: center; font-size: .78rem; color: #64748b; }
    .pr-lic-meta b { color: #0f172a; }
    .pr-plat { padding: 2px 8px; border-radius: 6px; background: #eff6ff; color: #1d4ed8; font-weight: 700; }
    .pr-plat--otra { background: #fff7ed; color: #c2410c; }
    .pr-lic-acciones { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; }
    .pr-lic .pr-seg select { max-width: 200px; }
    .pr-of h4 { margin: 14px 0 4px; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
    .pr-of p, .pr-of li { font-size: .9rem; line-height: 1.5; color: #0f172a; margin: 0; }
    .pr-of ul { margin: 0; padding-left: 18px; }
    .pr-ia-aviso { display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; padding: 10px 14px; margin-bottom: 10px; border-radius: 12px; background: #f5f3ff; border: 1px solid #ddd6fe; font-size: .85rem; color: #4c1d95; }
    #prModalComp .pr-mini td, #prModalComp .pr-mini th { font-size: .84rem; padding: 7px 8px; }
    .pr-bars a { display: grid; grid-template-columns: minmax(120px, 210px) 1fr 54px; gap: 10px; align-items: center; padding: 4px 0; text-decoration: none; color: #0f172a; font-size: .84rem; }
    .pr-bars a:hover .pr-bar span { filter: brightness(.92); }
    .pr-bar { height: 10px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
    .pr-bar span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #2152ff, #5c7cff); }
    .pr-bar--prov span { background: linear-gradient(90deg, #0e9f7e, #34c9a3); }
    .pr-bars .n { text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; }
    .pr-lista { list-style: none; margin: 0; padding: 0; }
    .pr-lista li { padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: .85rem; }
    .pr-lista li:last-child { border-bottom: 0; }
    .pr-callout { padding: 16px 18px; border-radius: 14px; background: #f8fafc; border: 1px dashed #cbd5e1; font-size: .9rem; line-height: 1.55; }
    .pr-callout code { background: #e2e8f0; padding: 1px 6px; border-radius: 6px; }
    .pr-tile-link { text-decoration: none; color: inherit; }
    .pr-legal { font-size: .76rem; color: #64748b; margin-top: 14px; line-height: 1.5; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Libraries\Prospeccion as P;
use App\Libraries\LicitacionesPlacsp as L;
use App\Libraries\Provincias;

$n = fn ($v) => number_format((float) $v, 0, ',', '.');
$eur = fn ($v) => $v === null || $v === '' ? '–' : number_format((float) $v, 0, ',', '.') . ' €';
$fecha = fn ($v) => $v ? date('d/m/Y', strtotime($v)) : '–';
$url = fn (array $extra = []) => site_url('admin/prospeccion') . '?' . http_build_query($extra);
$esEmpresas = in_array($pestana, ['nuevas', 'sin-web', 'subvenciones', 'contratos', 'cambios', 'seguimiento'], true);
$hayFiltros = $f['provincia'] !== '' || $f['sector'] !== '';
?>
<div class="gr-root" id="prosp" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>"
     data-url-seg="<?= site_url('admin/prospeccion/seguimiento') ?>" data-url-lic="<?= site_url('admin/prospeccion/licitacion') ?>"
     data-url-comp="<?= site_url('admin/prospeccion/competencia') ?>" data-url-web="<?= site_url('admin/prospeccion/web') ?>" data-url-correo="<?= site_url('admin/prospeccion/correo') ?>" data-url-evaluar="<?= site_url('admin/prospeccion/evaluar') ?>" data-url-oferta="<?= site_url('admin/prospeccion/oferta') ?>" data-dias-recordatorio="<?= (int) config('Prospeccion')->diasRecordatorio ?>" data-origen="<?= esc($pestana, 'attr') ?>">

<div class="gr-head">
    <div>
        <h1 class="title" style="margin-bottom:4px">Prospección</h1>
        <p class="subtitle">Empresas que pueden necesitar un desarrollador y licitaciones de informática, con tu seguimiento.
            Solo empresas con email. <?= $hayFiltros ? 'Filtrando: ' . esc(implode(' · ', array_filter([$f['provincia'], $f['sector'] !== '' ? P::SECTORES[$f['sector']][0] : '']))) . '.' : 'Toda España, todos los sectores.' ?></p>
    </div>
    <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
</div>

<nav class="pr-tabs">
    <?php foreach (P::PESTANAS as $k => $txt): ?>
        <a class="pr-tab<?= $pestana === $k ? ' is-on' : '' ?>" href="<?= $url(['p' => $k]) ?>"><?= esc($txt) ?></a>
    <?php endforeach; ?>
</nav>

<form class="gr-box pr-filtros" method="get" action="<?= site_url('admin/prospeccion') ?>">
    <input type="hidden" name="p" value="<?= esc($pestana, 'attr') ?>">
    <input type="hidden" name="f" value="1">
    <?php if ($pestana !== 'seguimiento'): ?>
    <label>Provincia
        <select name="provincia">
            <option value="">Toda España</option>
            <?php foreach (Provincias::nombres() as $p): ?>
                <option value="<?= esc($p, 'attr') ?>"<?= $f['provincia'] === $p ? ' selected' : '' ?>><?= esc($p) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana !== 'licitaciones' && $pestana !== 'seguimiento'): ?>
    <label>Sector
        <select name="sector">
            <option value="">Todos</option>
            <?php foreach (P::SECTORES as $k => $s): ?>
                <option value="<?= $k ?>"<?= $f['sector'] === $k ? ' selected' : '' ?>><?= esc($s[0]) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if (in_array($pestana, ['resumen', 'nuevas', 'subvenciones', 'contratos', 'cambios'], true)): ?>
    <label>Periodo
        <select name="dias">
            <?php foreach (P::DIAS as $k => $txt): ?>
                <option value="<?= $k ?>"<?= $f['dias'] === $k ? ' selected' : '' ?>>Últimos <?= esc($txt) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana === 'sin-web'): ?>
    <label>Antigüedad
        <select name="antiguedad">
            <?php foreach (P::ANTIGUEDAD as $k => $txt): ?>
                <option value="<?= $k ?>"<?= $f['antiguedad'] === $k ? ' selected' : '' ?>><?= esc($txt) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana === 'licitaciones'): ?>
    <label>Importe máx. (sin IVA)
        <input type="number" name="importe_max" min="0" step="1000" value="<?= $f['importe_max'] ?: '' ?>" placeholder="Sin límite">
    </label>
    <label>Procedimiento
        <select name="procedimiento">
            <option value="">Todos</option>
            <?php foreach (L::PROCEDIMIENTOS as $k => $txt): ?>
                <option value="<?= $k ?>"<?= $f['procedimiento'] === (string) $k ? ' selected' : '' ?>><?= esc($txt) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana === 'seguimiento'): ?>
    <label>Estado
        <select name="followup_status">
            <option value="">Todos</option>
            <?php foreach (P::SEG_ESTADOS as $k => $txt): ?>
                <option value="<?= $k ?>"<?= $f['followup_status'] === $k ? ' selected' : '' ?>><?= esc($txt) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana === 'licitaciones' || $pestana === 'seguimiento'): ?>
    <label>Buscar
        <input type="text" name="q" value="<?= esc($f['q'], 'attr') ?>" placeholder="<?= $pestana === 'licitaciones' ? 'Título, órgano o expediente' : 'Empresa, CIF o nota' ?>">
    </label>
    <?php endif; ?>
    <?php if (($esEmpresas && $pestana !== 'seguimiento') || $pestana === 'licitaciones'): ?>
    <label>Orden
        <select name="orden">
            <option value="encaje"<?= $f['orden'] === 'encaje' ? ' selected' : '' ?>>Por encaje</option>
            <option value="fecha"<?= $f['orden'] === 'fecha' ? ' selected' : '' ?>><?= $pestana === 'licitaciones' ? 'Por fin de plazo' : 'Más recientes' ?></option>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana !== 'seguimiento' && $pestana !== 'resumen'): ?>
    <label>Ya seguidas
        <select name="ocultar">
            <option value="descartadas"<?= $f['ocultar'] === 'descartadas' ? ' selected' : '' ?>>Ocultar descartadas</option>
            <option value="tocadas"<?= $f['ocultar'] === 'tocadas' ? ' selected' : '' ?>>Ocultar todas las que ya sigo</option>
            <option value="nada"<?= $f['ocultar'] === 'nada' ? ' selected' : '' ?>>Mostrar todas</option>
        </select>
    </label>
    <?php endif; ?>
    <?php if ($pestana !== 'licitaciones' && $pestana !== 'seguimiento'): ?>
    <?php endif; ?>
    <?php if ($pestana === 'nuevas'): ?>
        <label class="pr-check"><input type="checkbox" name="sin_web" value="1"<?= $f['sin_web'] ? ' checked' : '' ?>> Solo sin web</label>
    <?php endif; ?>
    <?php if ($pestana === 'subvenciones'): ?>
        <label class="pr-check"><input type="checkbox" name="digital" value="1"<?= $f['digital'] ? ' checked' : '' ?>> Solo ayudas de digitalización (Kit Digital, Red.es…)</label>
    <?php endif; ?>
    <?php if ($pestana === 'licitaciones'): ?>
        <label class="pr-check"><input type="checkbox" name="encajan" value="1"<?= $f['encajan'] ? ' checked' : '' ?>> Solo las que me encajan</label>
        <label class="pr-check"><input type="checkbox" name="cerradas" value="1"<?= $f['cerradas'] ? ' checked' : '' ?>> Incluir cerradas</label>
    <?php endif; ?>
    <?php if ($pestana === 'cambios' || $pestana === 'resumen'): ?>
        <div class="pr-actos">
            <?php foreach (P::ACTOS as $k => $a): ?>
                <label class="pr-check" style="padding-bottom:0"><input type="checkbox" name="actos[]" value="<?= $k ?>"<?= in_array($k, $f['actos'], true) ? ' checked' : '' ?>> <?= esc($a[0]) ?></label>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="pr-acciones">
        <button class="pr-btn pr-btn--ok" type="submit">Aplicar</button>
        <a class="pr-btn pr-btn--ghost" href="<?= $url(['p' => $pestana, 'limpiar' => 1]) ?>">Limpiar</a>
    </div>
</form>

<?php if (!empty($sinEmail)): ?>
    <div class="gr-alert" style="margin-bottom:14px">Esta base de datos no tiene la columna <code>company_enrichment.email</code>, así que ninguna empresa puede salir en las listas (solo se muestran las que tienen email).</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="gr-alert"><strong>No se han podido cargar los datos.</strong> <?= esc((string) $error) ?></div>
<?php elseif ($pestana === 'resumen'): ?>
<?php
    $d = $d ?? [];
    $acc = [
        'azul'    => '--acc:#2152ff;--acc-2:#5c7cff;--acc-soft:rgba(33,82,255,.08);--acc-line:rgba(33,82,255,.35);--acc-shadow:rgba(33,82,255,.35)',
        'ambar'   => '--acc:#d97706;--acc-2:#f5b13d;--acc-soft:rgba(217,119,6,.09);--acc-line:rgba(217,119,6,.35);--acc-shadow:rgba(217,119,6,.35)',
        'morado'  => '--acc:#6d4fe0;--acc-2:#9b87f5;--acc-soft:rgba(109,79,224,.09);--acc-line:rgba(109,79,224,.35);--acc-shadow:rgba(109,79,224,.35)',
        'cian'    => '--acc:#0e7490;--acc-2:#22b8cf;--acc-soft:rgba(14,116,144,.09);--acc-line:rgba(14,116,144,.35);--acc-shadow:rgba(14,116,144,.35)',
        'verde'   => '--acc:#0a8f3c;--acc-2:#3cc46b;--acc-soft:rgba(10,143,60,.09);--acc-line:rgba(10,143,60,.35);--acc-shadow:rgba(10,143,60,.35)',
        'rosa'    => '--acc:#be185d;--acc-2:#ec4899;--acc-soft:rgba(190,24,93,.08);--acc-line:rgba(190,24,93,.35);--acc-shadow:rgba(190,24,93,.35)',
    ];
    $periodo = 'últimos ' . P::DIAS[$f['dias']];
    $seg = $d['seguimiento'] ?? [];
    $tiles = [
        ['nuevas', 'azul', 'Recién constituidas', $d['nuevas'] ?? 0, $n($d['nuevas_sin_web'] ?? 0) . ' sin web conocida', $periodo],
        ['subvenciones', 'ambar', 'Con subvención', $d['subvenciones'] ?? 0, $n($d['subvenciones_digital'] ?? 0) . ' de digitalización', $periodo],
        ['contratos', 'morado', 'Ganan contratos públicos', $d['contratos'] ?? 0, $eur($d['contratos_importe'] ?? 0) . ' adjudicados', $periodo],
        ['cambios', 'cian', 'Cambios en el BORME', $d['cambios'] ?? 0, 'capital, domicilio, objeto, nombre…', $periodo],
        ['licitaciones', 'rosa', 'Licitaciones TI abiertas', $d['lic_abiertas'] ?? 0, $n($d['lic_7d'] ?? 0) . ' cierran en 7 días', ($d['lic_ultima_carga'] ?? null) ? 'cargadas ' . date('d/m H:i', strtotime($d['lic_ultima_carga'])) : 'aún sin cargar'],
        ['seguimiento', 'verde', 'En seguimiento', array_sum($seg), $n(($seg['contacted'] ?? 0) + ($seg['in_talks'] ?? 0)) . ' en marcha · ' . $n($seg['client'] ?? 0) . ' clientes', $n($seg['pending'] ?? 0) . ' pendientes'],
    ];
?>
<div class="gr-tiles">
    <?php foreach ($tiles as [$k, $color, $label, $valor, $meta, $pie]): ?>
        <a class="gr-tile pr-tile-link" style="<?= $acc[$color] ?>" href="<?= $url(['p' => $k]) ?>">
            <div class="gr-tile__head"><span class="gr-tile__label"><?= esc($label) ?></span></div>
            <div class="gr-tile__value"><?= $n($valor) ?></div>
            <div class="gr-tile__meta"><?= esc($meta) ?></div>
            <div class="gr-tile__foot"><span><?= esc($pie) ?></span><strong>Ver →</strong></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="gr-two">
    <div class="gr-box gr-card">
        <div class="gr-card__top">
            <h3>Empresas nuevas por semana</h3>
            <div class="gr-legend"><span><i class="gr-dot" style="background:#2152ff"></i>Sin web</span><span><i class="gr-dot" style="background:#b7c7ff"></i>Con web</span></div>
        </div>
        <?php
            $sem = $d['semanas'] ?? [];
            $max = max(1, ...array_map(fn ($s) => $s['n'], $sem ?: [['n' => 1]]));
            $W = 640; $H = 210; $base = 180; $alto = 160; $izq = 40; $paso = ($W - $izq) / max(1, count($sem)); $ancho = $paso * 0.62;
        ?>
        <svg class="gr-svg" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Empresas constituidas por semana">
            <?php foreach ([0, .5, 1] as $t): $y = $base - $alto * $t; ?>
                <line x1="<?= $izq ?>" x2="<?= $W ?>" y1="<?= $y ?>" y2="<?= $y ?>" stroke="#ecebe7" />
                <text x="<?= $izq - 6 ?>" y="<?= $y + 3 ?>" font-size="10" fill="#8a8984" text-anchor="end"><?= $n($max * $t) ?></text>
            <?php endforeach; ?>
            <?php foreach ($sem as $i => $s):
                $x = $izq + $i * $paso + ($paso - $ancho) / 2;
                $hT = $alto * $s['n'] / $max;
                $hS = $alto * $s['sin_web'] / $max;
            ?>
                <rect x="<?= round($x, 1) ?>" y="<?= round($base - $hT, 1) ?>" width="<?= round($ancho, 1) ?>" height="<?= round($hT - $hS, 1) ?>" fill="#b7c7ff" rx="3" />
                <rect x="<?= round($x, 1) ?>" y="<?= round($base - $hS, 1) ?>" width="<?= round($ancho, 1) ?>" height="<?= round($hS, 1) ?>" fill="#2152ff" rx="3" />
                <text x="<?= round($x + $ancho / 2, 1) ?>" y="<?= $base + 16 ?>" font-size="10" fill="#8a8984" text-anchor="middle"><?= date('d/m', strtotime($s['lunes'])) ?></text>
                <rect class="gr-hit" x="<?= round($izq + $i * $paso, 1) ?>" y="0" width="<?= round($paso, 1) ?>" height="<?= $base ?>"
                      data-tip="Semana del <?= date('d/m', strtotime($s['lunes'])) ?>: <?= $n($s['n']) ?> nuevas, <?= $n($s['sin_web']) ?> sin web" />
            <?php endforeach; ?>
        </svg>
        <p class="gr-note">Últimas 12 semanas por fecha de constitución, solo las que tienen email, con los filtros de provincia y sector.</p>
    </div>

    <div class="gr-box gr-card">
        <div class="gr-card__top"><h3>Próximos contactos</h3><a class="pr-muted" href="<?= $url(['p' => 'seguimiento']) ?>">Ver todo</a></div>
        <?php if (empty($d['proximos'])): ?>
            <p class="pr-muted">Nada para los próximos 7 días. Pon una fecha de "próximo contacto" en el seguimiento de una empresa y saldrá aquí.</p>
        <?php else: ?>
            <ul class="pr-lista">
                <?php foreach ($d['proximos'] as $x): $vencido = $x['next_contact_date'] < date('Y-m-d'); ?>
                    <li>
                        <a class="pr-nombre" target="_blank" rel="noopener" href="<?= esc(company_url(['cif' => $x['cif'], 'name' => $x['company_name']]), 'attr') ?>"><?= esc($x['company_name']) ?></a>
                        <div class="pr-muted"><span style="font-weight:800;color:<?= $vencido ? '#b91c1c' : '#0f172a' ?>"><?= $fecha($x['next_contact_date']) ?></span>
                            · <?= esc(P::SEG_ESTADOS[$x['status']] ?? $x['status']) ?><?= ($x['note'] ?? '') !== '' ? ' · ' . esc(mb_strimwidth($x['note'], 0, 90, '…')) : '' ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="gr-two" style="grid-template-columns:1fr 1fr">
    <div class="gr-box gr-card">
        <div class="gr-card__top"><h3>Nuevas por sector</h3><span class="pr-muted"><?= esc($periodo) ?></span></div>
        <?php $ps = $d['por_sector'] ?? []; $mx = max(1, ...array_values($ps ?: [1])); ?>
        <div class="pr-bars">
            <?php foreach ($ps as $k => $v): ?>
                <a href="<?= $url(['p' => 'nuevas', 'f' => 1, 'sector' => $k, 'provincia' => $f['provincia'], 'dias' => $f['dias']]) ?>"
                   title="Ver las nuevas de este sector"><span><?= esc(P::SECTORES[$k][0]) ?></span><span class="pr-bar"><span style="width:<?= round($v / $mx * 100, 1) ?>%"></span></span><span class="n"><?= $n($v) ?></span></a>
            <?php endforeach; ?>
        </div>
        <p class="gr-note">Por CNAE y, en las que aún no lo tienen (casi todas las recién constituidas), por su objeto social.<?= !empty($d['sin_sector']) ? ' ' . $n($d['sin_sector']) . ' sin sector reconocible.' : '' ?></p>
    </div>
    <div class="gr-box gr-card">
        <div class="gr-card__top"><h3>Nuevas por provincia</h3><span class="pr-muted">las 12 con más</span></div>
        <?php $pp = $d['por_provincia'] ?? []; $mx = max(1, ...array_values($pp ?: [1])); ?>
        <div class="pr-bars">
            <?php foreach ($pp as $k => $v): ?>
                <a href="<?= $url(['p' => 'nuevas', 'f' => 1, 'provincia' => $k, 'sector' => $f['sector'], 'dias' => $f['dias']]) ?>"
                   title="Ver las nuevas de esta provincia"><span><?= esc($k) ?></span><span class="pr-bar pr-bar--prov"><span style="width:<?= round($v / $mx * 100, 1) ?>%"></span></span><span class="n"><?= $n($v) ?></span></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="gr-box gr-card">
    <div class="gr-card__top"><h3>Licitaciones de informática que cierran antes</h3><a class="pr-muted" href="<?= $url(['p' => 'licitaciones']) ?>">Ver todas</a></div>
    <?php if (empty($d['lic_pronto'])): ?>
        <p class="pr-muted">No hay licitaciones abiertas cargadas<?= $f['provincia'] !== '' ? ' en ' . esc($f['provincia']) : '' ?>. Se cargan con <code>php spark prospeccion:licitaciones</code> (ver la pestaña Licitaciones TI).</p>
    <?php else: ?>
        <ul class="pr-lista">
            <?php foreach ($d['lic_pronto'] as $l): ?>
                <li><a class="pr-nombre" href="<?= esc((string) $l['url'], 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc(mb_strimwidth((string) $l['title'], 0, 140, '…')) ?></a>
                    <div class="pr-muted"><?= $l['deadline'] ? 'Hasta el ' . date('d/m H:i', strtotime($l['deadline'])) : 'Sin fecha límite' ?> · <?= esc((string) $l['contracting_body']) ?> · <?= $eur($l['amount_excl_vat'] ?? $l['estimated_value']) ?></div></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<p class="gr-note">Recuentos con caché de 20 minutos (datos a <?= esc(date('d/m/Y H:i', strtotime($d['ahora'] ?? 'now'))) ?>). El seguimiento y las licitaciones van al momento.</p>

<?php elseif ($pestana === 'licitaciones'): ?>
<?php $filas = $d['filas'] ?? []; ?>
    <?php if (!$filas && !$f['cerradas'] && $f['q'] === '' && $f['provincia'] === ''): ?>
        <div class="pr-callout">
            <strong>Aún no hay licitaciones cargadas.</strong> Se leen de la Plataforma de Contratación (perfiles alojados y plataformas autonómicas agregadas) y se guardan solo las de informática (CPV 72 servicios TI, 48 software, 80533 formación informática y 79342 marketing).<br>
            Primera carga (lo publicado en los últimos 30 días): <code>php spark prospeccion:licitaciones --dias 30</code><br>
            Cada día (tarea programada, por ejemplo a las 7:00): <code>php spark prospeccion:licitaciones</code><br>
            Para probar sin escribir nada: <code>php spark prospeccion:licitaciones --prueba</code>
        </div>
    <?php else: ?>
    <p class="pr-info"><span><?= $n($d['total']) ?> licitaci<?= $d['total'] === 1 ? 'ón' : 'ones' ?><?= $f['cerradas'] ? '' : ' en plazo' ?><?= count($filas) < $d['total'] ? ' (se ven ' . $n(count($filas)) . ')' : '' ?>. <?= $f['orden'] === 'encaje' ? 'Primero las que mejor te encajan.' : 'Ordenadas por fin de plazo.' ?></span>
        <span>«¿Encajo?» lo decide la IA con tu perfil (<code>app/Config/Prospeccion.php</code>). Pulsa el título para ver el anuncio y los pliegos.</span></p>
    <?php if (!empty($d['pendientes_ia'])): ?>
        <div class="pr-ia-aviso" id="prIaAviso">
            <?php if (!empty($d['hay_ia'])): ?>
                <span><strong><?= $n($d['pendientes_ia']) ?></strong> licitaci<?= $d['pendientes_ia'] === 1 ? 'ón en plazo sin evaluar' : 'ones en plazo sin evaluar' ?> con tu perfil (salen como «Sin evaluar»). Las nuevas se evalúan solas cada día al cargar.</span>
                <button type="button" class="pr-btn pr-btn--ok" id="prEvaluar">Evaluar todas con IA</button>
            <?php else: ?>
                <span>No hay <code>OPENAI_API_KEY</code> en el <code>.env</code>: el encaje sale de reglas fijas, no de tu perfil.</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="gr-box" style="overflow-x:auto">
    <table class="pr-table pr-lic">
        <tr><th style="width:130px">¿Encajo?</th><th>Proyecto</th><th style="width:230px"></th></tr>
        <?php foreach ($filas as $l):
            $v = P::veredicto($l);
            $dias = $l['deadline'] ? (int) floor((strtotime($l['deadline']) - time()) / 86400) : null;
            $cls = $dias === null ? 'verde' : ($dias <= 3 ? 'rojo' : ($dias <= 10 ? 'ambar' : 'verde'));
            $resumen = trim((string) ($l['ai_summary'] ?? ''));
            $porque = implode('<br>', array_map(fn ($x) => ($x[1] === '+' ? '✓ ' : '✗ ') . esc($x[0]), $l['motivos']));
            $importe = $l['amount_excl_vat'] !== null ? $eur($l['amount_excl_vat']) . ' sin IVA' : ($l['estimated_value'] !== null ? $eur($l['estimated_value']) . ' (valor est.)' : 'Importe no indicado');
        ?>
        <tr class="<?= ($l['followup_status'] ?? '') === 'discarded' ? 'is-desc' : '' ?>">
            <td>
                <span class="pr-ver pr-ver--<?= $v ?>"><?= ['si' => 'Encajas', 'quiza' => 'Quizá', 'no' => 'No encaja', 'pendiente' => 'Sin evaluar'][$v] ?></span>
                <div class="pr-muted" style="margin-top:4px">
                    <?= $v === 'pendiente' ? '' : (int) $l['encaje'] . '/100' ?>
                    <?php if ($l['motivos']): ?><button type="button" class="gr-help" style="margin-left:<?= $v === 'pendiente' ? '0' : '4px' ?>" data-help="<?= esc(($v === 'pendiente' ? '<b>Aún sin evaluar con IA.</b> Estimación por reglas: ' . (int) $l['encaje'] . '/100<br>' : '') . $porque, 'attr') ?>">?</button><?php endif; ?>
                </div>
            </td>
            <td>
                <a class="pr-lic-titulo" href="<?= esc((string) $l['url'], 'attr') ?>" target="_blank" rel="noopener nofollow" title="Ver el anuncio y los pliegos"><?= esc((string) $l['title']) ?></a>
                <?php if ($resumen !== ''): ?><div class="pr-lic-resumen"><?= esc($resumen) ?></div><?php endif; ?>
                <div class="pr-lic-meta">
                    <?php if ($l['deadline']): ?><span class="pr-plazo pr-plazo--<?= $cls ?>"><?= $dias < 0 ? 'Cerrada' : ($dias === 0 ? 'Cierra hoy' : 'Cierra en ' . $dias . ' día' . ($dias === 1 ? '' : 's')) ?> · <?= date('d/m H:i', strtotime($l['deadline'])) ?></span><?php endif; ?>
                    <span><b><?= $importe ?></b></span>
                    <span><?= esc((string) $l['contracting_body']) ?><?= $l['province'] ? ' (' . esc((string) $l['province']) . ')' : '' ?></span>
                    <span><?= esc(L::PROCEDIMIENTOS[$l['procedure_code']] ?? 'Procedimiento ' . $l['procedure_code']) ?></span>
                    <?php $pl = L::plataforma((string) $l['source'], (string) $l['url']); ?>
                    <span class="pr-plat<?= $pl['estado'] ? '' : ' pr-plat--otra' ?>" title="<?= $pl['estado'] ? 'Se presenta con tu usuario de la Plataforma de Contratación del Estado' : 'Se presenta en la plataforma de esa administración, con su propio registro' ?>">Se presenta en: <?= esc($pl['nombre']) ?></span>
                </div>
            </td>
            <td class="pr-seg" data-tipo="lic" data-id="<?= (int) $l['id'] ?>">
                <div class="pr-lic-acciones">
                    <?php if ($v === 'si' || $v === 'quiza' || $v === 'pendiente'): ?>
                        <button type="button" class="pr-btn pr-btn--ok pr-oferta-btn" data-id="<?= (int) $l['id'] ?>" data-titulo="<?= esc((string) $l['title'], 'attr') ?>" data-url="<?= esc((string) $l['url'], 'attr') ?>"><?= !empty($l['ai_proposal_at']) ? 'Ver mi oferta' : 'Preparar oferta' ?></button>
                    <?php endif; ?>
                    <button type="button" class="pr-comp-btn" data-id="<?= (int) $l['id'] ?>" data-titulo="<?= esc((string) $l['title'], 'attr') ?>" data-organo="<?= esc((string) $l['contracting_body'], 'attr') ?>">Competencia</button>
                </div>
                <select class="pr-seg-estado" data-v="<?= esc((string) $l['followup_status'], 'attr') ?>" title="Mi seguimiento">
                    <option value="">Seguimiento…</option>
                    <?php foreach (P::LIC_ESTADOS as $k => $txt): ?>
                        <option value="<?= $k ?>"<?= ($l['followup_status'] ?? '') === $k ? ' selected' : '' ?>><?= esc($txt) ?></option>
                    <?php endforeach; ?>
                </select><span class="pr-estado-msg"></span>
                <?php if (($l['followup_note'] ?? '') !== ''): ?><div class="pr-nota"><?= esc($l['followup_note']) ?></div><?php endif; ?>
                <details><summary><?= ($l['followup_note'] ?? '') !== '' ? 'Editar nota' : 'Nota' ?></summary>
                    <textarea class="pr-seg-nota"><?= esc((string) $l['followup_note']) ?></textarea><br>
                    <button type="button" class="pr-btn pr-btn--ok pr-guardar" style="margin-top:6px;padding:5px 10px">Guardar</button>
                </details>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$filas): ?><tr><td colspan="3" class="pr-muted">No hay licitaciones con estos filtros.</td></tr><?php endif; ?>
    </table>
    </div>
    <?php endif; ?>

<?php else: /* pestañas de empresas y seguimiento */ ?>
<?php
    $filas = $d['filas'] ?? [];
    $total = (int) ($d['total'] ?? 0);
    $explica = [
        'nuevas'       => 'Constituidas en los ' . P::DIAS[$f['dias']] . ' anteriores, según el BORME. Recién creadas: necesitan web, correo, facturación, reservas…',
        'sin-web'      => 'Activas, sin web conocida y constituidas en ' . mb_strtolower(P::ANTIGUEDAD[$f['antiguedad']]) . '. Marca "Con teléfono o email" para quedarte con las que puedes contactar.',
        'subvenciones' => 'Con alguna concesión de la BDNS en el periodo. Tienen dinero para invertir; las de digitalización ya han dado el paso y suelen necesitar mantenimiento o mejoras.',
        'contratos'    => 'Adjudicatarias de contratos públicos en el periodo (PLACSP y TED). Suelen tener procesos a mano: portales, herramientas internas, automatizaciones.',
        'cambios'      => 'Con actos en el BORME del periodo: ' . implode(', ', array_map(fn ($a) => mb_strtolower(P::ACTOS[$a][0]), $f['actos'])) . '. Crecer, mudarse o cambiar de nombre suele traer cambios de web y sistemas.',
        'seguimiento'  => 'Todas las empresas que has marcado, primero las que están en marcha y por fecha de próximo contacto.',
    ][$pestana] ?? '';
?>
    <p class="pr-info">
        <span><?= esc($explica) ?></span>
        <span><strong><?= $d['tope'] ?? false ? 'Más de 5.000' : $n($total) ?></strong> empresa<?= $total === 1 ? '' : 's' ?><?= count($filas) < $total ? ' · se ven las ' . $n(count($filas)) . ' más recientes' . ($f['orden'] === 'encaje' && $pestana !== 'seguimiento' ? ', ordenadas por encaje' : '') . '. Afina los filtros o exporta.' : '' ?>
            <button type="button" class="gr-help" data-help="<b>Encaje (0-100)</b>: suma de señales. Nueva de ≤30 días +35, ≤90 días +25, &lt;1 año +10 · sin web +25 · con teléfono o email +10 · ayuda de digitalización en 12 meses +15 (otra subvención +8) · contratos públicos en 12 meses +10 (5 o más +15) · cambio en el BORME en 90 días +10.">?</button></span>
    </p>
    <div class="gr-box" style="overflow-x:auto">
    <table class="pr-table">
        <tr><th>Encaje</th><th>Empresa</th><th>Señales</th><th>Contacto y a quién buscar</th><th><?= ['subvenciones' => 'Subvenciones', 'contratos' => 'Contratos', 'cambios' => 'BORME', 'seguimiento' => 'Desde'][$pestana] ?? 'Constitución' ?></th><th>Mi seguimiento</th></tr>
        <?php foreach ($filas as $r):
            $sc = (int) $r['encaje'];
            $ficha = company_url(['cif' => $r['cif'], 'name' => $r['company_name']]);
            $web = trim((string) $r['web']);
            $webUrl = $web !== '' ? (preg_match('#^https?://#i', $web) ? $web : 'https://' . $web) : '';
            $obj = trim((string) $r['objeto_social']);
        ?>
        <tr class="<?= ($r['followup_status'] ?? '') === 'discarded' ? 'is-desc' : '' ?>">
            <td><span class="pr-score pr-score--<?= $sc >= 60 ? 'alto' : ($sc >= 35 ? 'medio' : 'bajo') ?>"><?= $sc ?></span></td>
            <td style="max-width:360px">
                <a class="pr-nombre" href="<?= esc($ficha, 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) $r['company_name']) ?></a>
                <div class="pr-muted"><?= esc(implode(' · ', array_filter([(string) $r['cif'], $r['sector'] !== '' ? $r['sector'] . (!empty($r['sector_deducido']) ? ' (por su objeto social)' : '') : ($r['cnae_label'] ? mb_strimwidth((string) $r['cnae_label'], 0, 50, '…') : 'Actividad sin clasificar'), implode(', ', array_filter([(string) $r['municipality'], $r['provincia']]))]))) ?></div>
                <?php if ($obj !== '' && $obj !== '-' && stripos($obj, 'CNAE') !== 0): ?><div class="pr-obj" title="<?= esc($obj, 'attr') ?>"><?= esc($obj) ?></div><?php endif; ?>
            </td>
            <td><div class="pr-chips"><?php foreach ($r['chips'] as [$t, $c]): ?><span class="pr-chip pr-chip--<?= $c ?>"><?= esc($t) ?></span><?php endforeach; ?></div></td>
            <td class="pr-contacto">
                <?php if (trim((string) $r['email']) !== ''): ?><a class="pr-dato" href="mailto:<?= esc(trim((string) $r['email']), 'attr') ?>"><?= esc(trim((string) $r['email'])) ?></a><?php endif; ?>
                <?php if ($webUrl !== ''): ?><a class="pr-dato" href="<?= esc($webUrl, 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc(preg_replace('#^https?://(www\.)?#i', '', rtrim($web, '/'))) ?></a><?php endif; ?>
                <?php if ($webUrl === '' && ($r['dominio_email'] ?? '') !== ''): ?><div class="pr-muted">Dominio del email: <a href="https://<?= esc($r['dominio_email'], 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc($r['dominio_email']) ?></a></div><?php endif; ?>
                <?php
                    $dir = trim((string) ($r['address'] ?? ''));
                    $dir = ($dir === '-' ) ? '' : $dir;
                    $lugar = trim(implode(' ', array_filter([(string) $r['municipality'], $r['provincia']])));
                    $g = fn (string $q) => 'https://www.google.com/search?q=' . rawurlencode($q);
                    $nombreBusca = '"' . trim(preg_replace('/\s+/', ' ', (string) $r['company_name'])) . '"';
                ?>
                <?php if ($dir !== ''): ?>
                    <div class="pr-dir">📍 <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($dir) ?>" target="_blank" rel="noopener"><?= esc(mb_strimwidth($dir, 0, 90, '…')) ?></a></div>
                <?php endif; ?>
                <?php foreach ($r['admins'] ?? [] as $a): ?>
                    <div class="pr-adm"><span class="pr-muted"><?= esc($a['cargo']) ?>:</span> <b><?= esc($a['nombre']) ?></b>
                        <span class="pr-links">
                            <?php if ($a['persona']): ?>
                                <a class="pr-link pr-link--li" target="_blank" rel="noopener" title="Buscar a esta persona en LinkedIn" href="https://www.linkedin.com/search/results/people/?keywords=<?= rawurlencode($a['nombre']) ?>">in</a>
                                <a class="pr-link" target="_blank" rel="noopener" title="Buscar a esta persona con la empresa en Google" href="<?= esc($g('"' . $a['nombre'] . '" ' . $nombreBusca), 'attr') ?>">G</a>
                            <?php else: ?>
                                <a class="pr-link" target="_blank" rel="noopener" title="Buscar esta sociedad en Google" href="<?= esc($g('"' . $a['nombre'] . '"'), 'attr') ?>">G</a>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <div class="pr-buscar">
                    <a class="pr-link" target="_blank" rel="noopener" title="Buscar la empresa en Google" href="<?= esc($g($nombreBusca . ' ' . $lugar), 'attr') ?>">Google</a>
                    <a class="pr-link pr-link--li" target="_blank" rel="noopener" title="Buscar la empresa en LinkedIn" href="https://www.linkedin.com/search/results/companies/?keywords=<?= rawurlencode(trim((string) $r['company_name'])) ?>">LinkedIn</a>
                    <a class="pr-link" target="_blank" rel="noopener" title="Buscar en Google Maps (ficha del negocio, teléfono, horario)" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode(trim((string) $r['company_name']) . ' ' . $lugar) ?>">Maps</a>
                    <?php if (trim((string) $r['email']) !== ''): ?><button type="button" class="pr-redactar" data-id="<?= (int) $r['id'] ?>">✉ Redactar correo</button><?php endif; ?>
                    <?php if ($webUrl === ''): ?><button type="button" class="pr-buscar-web" data-id="<?= (int) $r['id'] ?>" title="Mira si existen nombre.es / nombre.com y si son suyos">¿Tiene web?</button><?php endif; ?>
                </div>
                <div class="pr-web-res"></div>
            </td>
            <td style="max-width:300px">
                <?php if ($pestana === 'subvenciones'): ?>
                    <strong><?= $eur($r['subv_total']) ?></strong> <span class="pr-muted">· <?= (int) $r['subv_n'] ?> concesión<?= (int) $r['subv_n'] === 1 ? '' : 'es' ?> · <?= $fecha($r['subv_ultima']) ?></span>
                    <div class="pr-obj" title="<?= esc((string) $r['subv_conv'], 'attr') ?>"><?= esc((string) $r['subv_conv']) ?></div>
                <?php elseif ($pestana === 'contratos'): ?>
                    <strong><?= (int) $r['con_n'] ?> contrato<?= (int) $r['con_n'] === 1 ? '' : 's' ?></strong> <span class="pr-muted">· <?= $eur($r['con_total']) ?><?= (int) $r['con_menores'] > 0 ? ' · ' . (int) $r['con_menores'] . ' menores' : '' ?> · último <?= $fecha($r['con_ultima']) ?></span>
                    <div class="pr-muted"><?= esc(str_replace('||', ' · ', (string) $r['con_organos'])) ?></div>
                    <div class="pr-obj" title="<?= esc((string) $r['con_titulo'], 'attr') ?>"><?= esc((string) $r['con_titulo']) ?></div>
                <?php elseif ($pestana === 'cambios'): ?>
                    <strong><?= $fecha($r['borme_fecha']) ?></strong>
                    <div class="pr-obj" title="<?= esc((string) $r['borme_actos'], 'attr') ?>"><?= esc((string) $r['borme_actos']) ?></div>
                <?php elseif ($pestana === 'seguimiento'): ?>
                    <?= $fecha($r['followup_updated'] ?? null) ?><div class="pr-muted"><?= esc(P::PESTANAS[$r['followup_source'] ?? ''] ?? '') ?></div>
                <?php else: ?>
                    <?= $fecha($r['fecha_constitucion']) ?>
                    <?php if (trim((string) $r['capital_social_raw']) !== '' && $r['capital_social_raw'] !== '-'): ?><div class="pr-muted">Capital <?= esc((string) $r['capital_social_raw']) ?></div><?php endif; ?>
                    <?php if ($r['ult_cuentas_anio']): ?><div class="pr-muted">Cuentas <?= (int) $r['ult_cuentas_anio'] ?></div><?php endif; ?>
                <?php endif; ?>
            </td>
            <td class="pr-seg" data-tipo="emp" data-id="<?= (int) $r['id'] ?>">
                <select class="pr-seg-estado" data-v="<?= esc((string) $r['followup_status'], 'attr') ?>">
                    <option value="">—</option>
                    <?php foreach (P::SEG_ESTADOS as $k => $txt): ?>
                        <option value="<?= $k ?>"<?= ($r['followup_status'] ?? '') === $k ? ' selected' : '' ?>><?= esc($txt) ?></option>
                    <?php endforeach; ?>
                </select><span class="pr-estado-msg"></span>
                <?php if (!empty($r['followup_next'])): ?><div class="pr-nota"><strong>Próximo: <?= $fecha($r['followup_next']) ?></strong></div><?php endif; ?>
                <?php if (($r['followup_note'] ?? '') !== ''): ?><div class="pr-nota"><?= esc(mb_strimwidth((string) $r['followup_note'], 0, 160, '…')) ?></div><?php endif; ?>
                <details><summary><?= ($r['followup_note'] ?? '') !== '' ? 'Editar nota' : 'Nota y próximo contacto' ?></summary>
                    <textarea class="pr-seg-nota" placeholder="Llamé el…, interesado en…"><?= esc((string) $r['followup_note']) ?></textarea><br>
                    <span class="pr-muted">Próximo contacto</span><br>
                    <input type="date" class="pr-seg-proximo" value="<?= esc((string) $r['followup_next'], 'attr') ?>"><br>
                    <button type="button" class="pr-btn pr-btn--ok pr-guardar" style="margin-top:6px;padding:5px 10px">Guardar</button>
                </details>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$filas): ?><tr><td colspan="6" class="pr-muted"><?= $pestana === 'seguimiento' ? 'Aún no sigues ninguna empresa. Elige un estado en el desplegable de cualquier lista.' : 'No hay empresas con email con estos filtros.' ?></td></tr><?php endif; ?>
    </table>
    </div>
    <p class="pr-legal">Solo salen empresas con email genérico de la empresa; nada de autónomos ni de personas. Ojo: un correo comercial no pedido, aunque sea a una empresa y uno solo, entra en la LSSI (art. 21) y la AEPD lo ha sancionado. Lo más seguro es escribir algo personal y útil para esa empresa, sin listas ni envíos en bloque, y dejar fácil decir "no me interesa". Los administradores salen del BORME y son datos personales: están para buscar a la persona una a una.</p>
<?php endif; ?>

<div class="pr-modal" id="prModalOferta" role="dialog" aria-modal="true" aria-labelledby="prOfTitulo">
    <div class="pr-modal__box pr-of" style="width:860px">
        <h3 id="prOfTitulo">Preparar oferta</h3>
        <div class="pr-modal__nota pr-of-lic"></div>
        <div class="pr-of-cuerpo"></div>
        <div class="pr-modal__acciones">
            <a class="pr-btn pr-btn--ghost pr-of-anuncio" target="_blank" rel="noopener nofollow" href="#">Ver anuncio y pliegos</a>
            <button type="button" class="pr-btn pr-btn--ghost pr-of-copiar">Copiar texto</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-of-rehacer" title="Nueva llamada a la IA">Volver a generar</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-of-cerrar" style="margin-left:auto">Cerrar</button>
        </div>
        <div class="pr-modal__nota">Hecho con IA solo con los datos del anuncio, sin leer los pliegos: revisa lo que pide el pliego y completa los huecos [ ]. Se guarda: la próxima vez se abre sin gastar otra llamada.</div>
    </div>
</div>

<div class="pr-modal" id="prModalComp" role="dialog" aria-modal="true" aria-labelledby="prCompTitulo">
    <div class="pr-modal__box" style="width:820px">
        <h3 id="prCompTitulo">Competencia</h3>
        <div class="pr-modal__nota pr-comp-lic"></div>
        <div class="pr-comp-cuerpo" style="margin-top:12px"></div>
        <div class="pr-modal__acciones"><button type="button" class="pr-btn pr-btn--ghost pr-comp-cerrar" style="margin-left:auto">Cerrar</button></div>
    </div>
</div>

<div class="pr-modal" id="prModal" role="dialog" aria-modal="true" aria-labelledby="prModalTitulo">
    <div class="pr-modal__box">
        <h3 id="prModalTitulo">Correo para <span class="pr-modal-empresa"></span></h3>
        <div class="pr-modal__nota pr-modal-origen"></div>
        <label>Para</label>
        <input type="text" class="pr-modal-para" readonly>
        <label>Asunto</label>
        <input type="text" class="pr-modal-asunto">
        <label>Mensaje</label>
        <textarea class="pr-modal-cuerpo"></textarea>
        <label class="pr-check" style="text-transform:none;letter-spacing:0;font-size:.85rem;color:#0f172a;display:flex;gap:6px;align-items:center">
            <input type="checkbox" class="pr-modal-marcar" checked> Al abrirlo, marcar la empresa como «Contactada» y recordármelo en <?= (int) config('Prospeccion')->diasRecordatorio ?> días
        </label>
        <div class="pr-modal__acciones">
            <button type="button" class="pr-btn pr-btn--ok pr-modal-gmail">Abrir en Gmail</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-modal-mailto">Abrir en mi programa de correo</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-modal-copiar">Copiar</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-modal-otra">Otra versión</button>
            <button type="button" class="pr-btn pr-btn--ghost pr-modal-cerrar" style="margin-left:auto">Cerrar</button>
        </div>
        <div class="pr-modal__nota">Revísalo antes de enviarlo: la IA solo conoce los datos de la ficha. Tu nombre, web y servicios están en <code>app/Config/Prospeccion.php</code>.</div>
    </div>
</div>

<?= view('admin/partials/panel_js') ?>
<script>
(function () {
    var root = document.getElementById('prosp');
    if (!root) return;

    function enviar(td, datos) {
        var esLic = td.getAttribute('data-tipo') === 'lic';
        var fd = new FormData();
        fd.append(root.getAttribute('data-csrf-name'), root.getAttribute('data-csrf'));
        Object.keys(datos).forEach(function (k) { fd.append(k, datos[k]); });
        var msg = td.querySelector('.pr-estado-msg');
        msg.className = 'pr-estado-msg';
        msg.textContent = '…';
        return fetch(root.getAttribute(esLic ? 'data-url-lic' : 'data-url-seg'), {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': root.getAttribute('data-csrf') }
        }).then(function (r) { return r.json(); }).then(function (j) {
            msg.className = j.ok ? 'pr-estado-msg pr-ok' : 'pr-estado-msg pr-err';
            msg.textContent = j.ok ? 'Guardado' : (j.error || 'Error');
            if (j.ok) setTimeout(function () { msg.textContent = ''; }, 2500);
            var tr = td.closest('tr');
            var sel = td.querySelector('.pr-seg-estado');
            if (tr && sel) tr.classList.toggle('is-desc', sel.value === 'discarded');
        }).catch(function () { msg.className = 'pr-estado-msg pr-err'; msg.textContent = 'Error de red'; });
    }

    function datosDe(td) {
        var sel = td.querySelector('.pr-seg-estado');
        var nota = td.querySelector('.pr-seg-nota');
        var prox = td.querySelector('.pr-seg-proximo');
        var d = { estado: sel ? sel.value : '', nota: nota ? nota.value : '' };
        if (td.getAttribute('data-tipo') === 'lic') {
            d.id = td.getAttribute('data-id');
        } else {
            d.company_id = td.getAttribute('data-id');
            d.proximo = prox ? prox.value : '';
            d.origen = root.getAttribute('data-origen');
        }
        return d;
    }

    root.addEventListener('change', function (e) {
        var sel = e.target.closest('.pr-seg-estado');
        if (!sel) return;
        var td = sel.closest('.pr-seg');
        sel.setAttribute('data-v', sel.value);
        var d = datosDe(td);
        // Quitar el estado de una empresa con nota: se avisa, porque se borra también la nota
        if (td.getAttribute('data-tipo') === 'emp' && d.estado === '' && d.nota.trim() !== '' && !confirm('Se quitará del seguimiento y se borrará la nota. ¿Seguir?')) {
            return;
        }
        enviar(td, d);
    });

    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.pr-guardar');
        if (!btn) return;
        var td = btn.closest('.pr-seg');
        var sel = td.querySelector('.pr-seg-estado');
        // Escribir una nota sin estado deja la empresa como "Pendiente"
        if (sel && sel.value === '' && td.getAttribute('data-tipo') === 'emp') { sel.value = 'pending'; sel.setAttribute('data-v', 'pending'); }
        if (sel && sel.value === '' && td.getAttribute('data-tipo') === 'lic') { sel.value = 'interested'; sel.setAttribute('data-v', 'interested'); }
        enviar(td, datosDe(td));
    });

    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.pr-buscar-web');
        if (!btn) return;
        var caja = btn.closest('td').querySelector('.pr-web-res');
        btn.disabled = true;
        btn.textContent = 'Buscando…';
        fetch(root.getAttribute('data-url-web') + '?id=' + encodeURIComponent(btn.getAttribute('data-id')), {
            credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.text(); }).then(function (h) { caja.innerHTML = h; btn.remove(); })
          .catch(function () { caja.textContent = 'No se ha podido comprobar.'; btn.disabled = false; btn.textContent = '¿Tiene web?'; });
    });

    // ---- Correo personalizado
    var modal = document.getElementById('prModal');
    var actual = null;   // { id, td, variante }
    function q(sel) { return modal.querySelector(sel); }
    function pedirCorreo() {
        var fd = new FormData();
        fd.append(root.getAttribute('data-csrf-name'), root.getAttribute('data-csrf'));
        fd.append('company_id', actual.id);
        fd.append('variante', actual.variante);
        q('.pr-modal-origen').textContent = 'Redactando…';
        q('.pr-modal-cuerpo').value = '';
        q('.pr-modal-asunto').value = '';
        return fetch(root.getAttribute('data-url-correo'), {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': root.getAttribute('data-csrf') }
        }).then(function (r) { return r.json(); }).then(function (j) {
            if (!j.ok) { q('.pr-modal-origen').textContent = j.error || 'No se ha podido redactar.'; return; }
            q('.pr-modal-para').value = j.para;
            q('.pr-modal-asunto').value = j.asunto;
            q('.pr-modal-cuerpo').value = j.cuerpo;
            q('.pr-modal-origen').textContent = j.origen === 'ia' ? 'Redactado con IA a partir de los datos de la empresa.' : 'Plantilla (la IA no está disponible ahora).';
        }).catch(function () { q('.pr-modal-origen').textContent = 'Error de red al redactar.'; });
    }
    function marcarContactada() {
        if (!q('.pr-modal-marcar').checked || !actual || !actual.td) return;
        var td = actual.td;
        var sel = td.querySelector('.pr-seg-estado');
        var nota = td.querySelector('.pr-seg-nota');
        var prox = td.querySelector('.pr-seg-proximo');
        var hoy = new Date();
        var dd = ('0' + hoy.getDate()).slice(-2) + '/' + ('0' + (hoy.getMonth() + 1)).slice(-2);
        var linea = 'Correo enviado el ' + dd + ': ' + q('.pr-modal-asunto').value;
        if (nota) nota.value = nota.value.trim() ? linea + '\n' + nota.value.trim() : linea;
        var r = new Date(hoy.getTime() + parseInt(root.getAttribute('data-dias-recordatorio') || '7', 10) * 86400000);
        if (prox) prox.value = r.getFullYear() + '-' + ('0' + (r.getMonth() + 1)).slice(-2) + '-' + ('0' + r.getDate()).slice(-2);
        if (sel && (sel.value === '' || sel.value === 'pending')) { sel.value = 'contacted'; sel.setAttribute('data-v', 'contacted'); }
        enviar(td, datosDe(td));
    }
    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.pr-redactar');
        if (!btn) return;
        var tr = btn.closest('tr');
        actual = { id: btn.getAttribute('data-id'), td: tr ? tr.querySelector('.pr-seg') : null, variante: 0 };
        q('.pr-modal-empresa').textContent = tr ? (tr.querySelector('.pr-nombre') || {}).textContent || '' : '';
        modal.classList.add('is-on');
        pedirCorreo();
    });
    q('.pr-modal-cerrar').addEventListener('click', function () { modal.classList.remove('is-on'); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('is-on'); });
    q('.pr-modal-otra').addEventListener('click', function () { if (actual) { actual.variante++; pedirCorreo(); } });
    q('.pr-modal-copiar').addEventListener('click', function () {
        var t = 'Asunto: ' + q('.pr-modal-asunto').value + '\n\n' + q('.pr-modal-cuerpo').value;
        (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () {
            q('.pr-modal-copiar').textContent = '¡Copiado!';
            setTimeout(function () { q('.pr-modal-copiar').textContent = 'Copiar'; }, 1500);
        }).catch(function () { q('.pr-modal-cuerpo').select(); document.execCommand('copy'); });
    });
    q('.pr-modal-gmail').addEventListener('click', function () {
        var u = 'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(q('.pr-modal-para').value)
            + '&su=' + encodeURIComponent(q('.pr-modal-asunto').value) + '&body=' + encodeURIComponent(q('.pr-modal-cuerpo').value);
        window.open(u, '_blank', 'noopener');
        marcarContactada();
    });
    q('.pr-modal-mailto').addEventListener('click', function () {
        window.location.href = 'mailto:' + encodeURIComponent(q('.pr-modal-para').value)
            + '?subject=' + encodeURIComponent(q('.pr-modal-asunto').value) + '&body=' + encodeURIComponent(q('.pr-modal-cuerpo').value);
        marcarContactada();
    });

    // ---- Competencia en un modal
    var modalComp = document.getElementById('prModalComp');
    var cacheComp = {};
    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.pr-comp-btn');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        modalComp.querySelector('#prCompTitulo').textContent = 'Competencia: ' + btn.getAttribute('data-organo');
        modalComp.querySelector('.pr-comp-lic').textContent = btn.getAttribute('data-titulo');
        var cuerpo = modalComp.querySelector('.pr-comp-cuerpo');
        modalComp.classList.add('is-on');
        if (cacheComp[id]) { cuerpo.innerHTML = cacheComp[id]; return; }
        cuerpo.innerHTML = '<p class="pr-muted">Revisando los contratos que ha adjudicado este organismo… (la primera vez tarda unos segundos)</p>';
        fetch(root.getAttribute('data-url-comp') + '?id=' + encodeURIComponent(id), {
            credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.text(); }).then(function (h) { cacheComp[id] = h; cuerpo.innerHTML = h; })
          .catch(function () { cuerpo.textContent = 'No se ha podido cargar.'; });
    });
    modalComp.querySelector('.pr-comp-cerrar').addEventListener('click', function () { modalComp.classList.remove('is-on'); });
    modalComp.addEventListener('click', function (e) { if (e.target === modalComp) modalComp.classList.remove('is-on'); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { modalComp.classList.remove('is-on'); modal.classList.remove('is-on'); }
    });

    // ---- Preparar oferta (se guarda en la base: solo la primera vez llama a la IA)
    var modalOf = document.getElementById('prModalOferta');
    var ofActual = null;
    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; }
    function pintarOferta(o) {
        var lista = function (a) { return a && a.length ? '<ul>' + a.map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul>' : '<p class="pr-muted">—</p>'; };
        modalOf.querySelector('.pr-of-cuerpo').innerHTML =
            '<h4>De qué va</h4><p>' + esc(o.de_que_va) + '</p>' +
            '<h4>¿Encajas?</h4><p>' + esc(o.encaje) + '</p>' +
            '<h4>Qué habría que hacer</h4>' + lista(o.tareas) +
            '<h4>Comprueba en el pliego</h4>' + lista(o.revisar_en_pliego) +
            '<h4>Texto para tu oferta</h4><textarea class="pr-of-texto" style="width:100%;min-height:320px;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:9px;font:inherit;font-size:.9rem;line-height:1.5"></textarea>';
        modalOf.querySelector('.pr-of-texto').value = o.texto || '';
    }
    function pedirOferta(rehacer) {
        var fd = new FormData();
        fd.append(root.getAttribute('data-csrf-name'), root.getAttribute('data-csrf'));
        fd.append('id', ofActual.id);
        if (rehacer) fd.append('rehacer', '1');
        modalOf.querySelector('.pr-of-cuerpo').innerHTML = '<p class="pr-muted" style="margin-top:14px">Preparando… (unos segundos)</p>';
        fetch(root.getAttribute('data-url-oferta'), {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': root.getAttribute('data-csrf') }
        }).then(function (r) { return r.json(); }).then(function (j) {
            if (!j.ok) { modalOf.querySelector('.pr-of-cuerpo').innerHTML = '<p class="pr-err" style="margin-top:14px">' + esc(j.error || 'No se ha podido preparar.') + '</p>'; return; }
            pintarOferta(j.oferta);
            if (ofActual.btn) ofActual.btn.textContent = 'Ver mi oferta';
        }).catch(function () { modalOf.querySelector('.pr-of-cuerpo').innerHTML = '<p class="pr-err">Error de red.</p>'; });
    }
    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.pr-oferta-btn');
        if (!btn) return;
        ofActual = { id: btn.getAttribute('data-id'), btn: btn };
        modalOf.querySelector('.pr-of-lic').textContent = btn.getAttribute('data-titulo');
        modalOf.querySelector('.pr-of-anuncio').href = btn.getAttribute('data-url') || '#';
        modalOf.classList.add('is-on');
        pedirOferta(false);
    });
    modalOf.querySelector('.pr-of-cerrar').addEventListener('click', function () { modalOf.classList.remove('is-on'); });
    modalOf.addEventListener('click', function (e) { if (e.target === modalOf) modalOf.classList.remove('is-on'); });
    modalOf.querySelector('.pr-of-rehacer').addEventListener('click', function () { if (ofActual && confirm('¿Generar otra versión? Sustituye a la guardada.')) pedirOferta(true); });
    modalOf.querySelector('.pr-of-copiar').addEventListener('click', function () {
        var t = modalOf.querySelector('.pr-of-texto');
        if (!t) return;
        (navigator.clipboard ? navigator.clipboard.writeText(t.value) : Promise.reject()).then(function () {
            modalOf.querySelector('.pr-of-copiar').textContent = '¡Copiado!';
            setTimeout(function () { modalOf.querySelector('.pr-of-copiar').textContent = 'Copiar texto'; }, 1500);
        }).catch(function () { t.select(); document.execCommand('copy'); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modalOf.classList.remove('is-on'); });

    // ---- Evaluar con IA las licitaciones pendientes
    var btnEval = document.getElementById('prEvaluar');
    if (btnEval) {
        btnEval.addEventListener('click', function () {
            // Va pidiendo tandas de 30 hasta que no quede ninguna pendiente
            var hechas = 0;
            btnEval.disabled = true;
            function tanda() {
                var fd = new FormData();
                fd.append(root.getAttribute('data-csrf-name'), root.getAttribute('data-csrf'));
                fetch(root.getAttribute('data-url-evaluar'), {
                    method: 'POST', body: fd, credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': root.getAttribute('data-csrf') }
                }).then(function (r) { return r.json(); }).then(function (j) {
                    hechas += j.evaluadas || 0;
                    if (j.error) { btnEval.disabled = false; btnEval.textContent = 'Error: ' + j.error + ' (' + hechas + ' evaluadas). Reintentar'; return; }
                    if (j.pendientes > 0 && j.evaluadas > 0) {
                        btnEval.textContent = 'Evaluando… ' + hechas + ' hechas, quedan ' + j.pendientes;
                        tanda();
                    } else {
                        btnEval.textContent = hechas + ' evaluadas. Recargando…';
                        setTimeout(function () { window.location.reload(); }, 600);
                    }
                }).catch(function () { btnEval.disabled = false; btnEval.textContent = 'Error de red (' + hechas + ' evaluadas). Reintentar'; });
            }
            btnEval.textContent = 'Evaluando…';
            tanda();
        });
    }
})();
</script>
</div>
<?= $this->endSection() ?>
