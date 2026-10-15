<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>
<?= $this->section('styles') ?>
<style>
    .ap-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    .ap-tab{padding:7px 14px;border-radius:999px;border:1px solid #cbd5e1;background:#fff;color:#334155;font-weight:700;font-size:.85rem;text-decoration:none}
    .ap-tab.is-on{background:#0f172a;border-color:#0f172a;color:#fff}
    .ap-table{width:100%;border-collapse:collapse;min-width:1000px;background:#fff}
    .ap-table th{padding:10px 12px;color:#64748b;font-size:.8rem;text-align:left;border-bottom:2px solid #f1f5f9}
    .ap-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;font-size:.87rem;vertical-align:top}
    .ap-tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700;white-space:nowrap}
    .ap-tag--verificado{background:#f0fdf4;color:#15803d}
    .ap-tag--pendiente{background:#fffbeb;color:#92400e}
    .ap-tag--aprobado{background:#eff6ff;color:#1d4ed8}
    .ap-tag--rechazado{background:#f1f5f9;color:#475569}
    .ap-muted{color:#64748b;font-size:.78rem}
    .ap-val{font-weight:800;color:#0f172a;word-break:break-all}
    .ap-actions{display:flex;gap:6px;flex-wrap:wrap}
    .ap-actions button{border:0;border-radius:8px;padding:6px 12px;font-weight:800;font-size:.8rem;cursor:pointer}
    .ap-ok{background:#0f172a;color:#fff}
    .ap-no{background:#f1f5f9;color:#334155}
    .ap-flash{padding:10px 14px;border-radius:10px;font-weight:700;margin-bottom:14px}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="margin-bottom:1.25rem">
    <h1 class="title" style="margin-bottom:4px">Datos aportados</h1>
    <p class="subtitle" style="margin:0">Teléfonos, webs y emails enviados desde «Añadir…» en la ficha. Nada se publica sin aprobarlo aquí
        o sin que <code>php spark datos:verificar-aportaciones --aplicar</code> lo haya encontrado en la web de la empresa (con su CIF).</p>
</div>

<?php if (session('success')): ?><div class="ap-flash" style="background:#f0fdf4;color:#166534"><?= esc(session('success')) ?></div><?php endif; ?>
<?php if (session('error')): ?><div class="ap-flash" style="background:#fef2f2;color:#991b1b"><?= esc(session('error')) ?></div><?php endif; ?>

<div class="ap-tabs">
    <?php foreach (['abiertas' => 'Por revisar (' . (($cuentas['verificado'] ?? 0) + ($cuentas['pendiente'] ?? 0)) . ')',
                    'aprobado' => 'Aprobadas (' . ($cuentas['aprobado'] ?? 0) . ')',
                    'rechazado' => 'Rechazadas (' . ($cuentas['rechazado'] ?? 0) . ')',
                    'todas' => 'Todas'] as $k => $txt): ?>
        <a class="ap-tab<?= $estado === $k ? ' is-on' : '' ?>" href="<?= site_url('admin/aportaciones?estado=' . $k) ?>"><?= esc($txt) ?></a>
    <?php endforeach; ?>
</div>

<div style="overflow-x:auto">
<table class="ap-table">
    <tr>
        <th>Empresa</th><th>Dato</th><th>En la ficha ahora</th><th>Quién</th><th>Comprobación</th><th>Estado</th><th></th>
    </tr>
    <?php if (!$filas): ?>
        <tr><td colspan="7" class="ap-muted">No hay nada aquí.</td></tr>
    <?php endif; ?>
    <?php foreach ($filas as $f):
        $actual = trim((string) ($f['actual_' . $f['campo']] ?? ''));
        $ficha = company_url(['cif' => $f['cif'] ?? '', 'name' => $f['company_name'] ?? '']);
        $abierta = in_array($f['estado'], ['pendiente', 'verificado'], true);
    ?>
    <tr>
        <td>
            <a href="<?= esc($ficha, 'attr') ?>" target="_blank" rel="noopener" style="font-weight:700"><?= esc($f['company_name'] ?? ('#' . $f['company_id'])) ?></a>
            <div class="ap-muted"><?= esc($f['cif'] ?? '') ?> · #<?= (int) $f['id'] ?> · <?= esc(date('d/m/Y H:i', strtotime($f['created_at']))) ?></div>
        </td>
        <td>
            <div class="ap-muted"><?= esc($campos[$f['campo']] ?? $f['campo']) ?></div>
            <?php if ($f['campo'] === 'web'): ?>
                <a class="ap-val" href="<?= esc($f['valor'], 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc($f['valor']) ?></a>
            <?php else: ?>
                <span class="ap-val"><?= esc($f['valor']) ?></span>
            <?php endif; ?>
            <?php if (($f['valor_escrito'] ?? '') !== '' && $f['valor_escrito'] !== $f['valor']): ?>
                <div class="ap-muted">escrito: <?= esc($f['valor_escrito']) ?></div>
            <?php endif; ?>
        </td>
        <td><?= $actual !== '' && $actual !== '-' ? esc($actual) : '<span class="ap-muted">(vacío)</span>' ?>
            <?php if (($f['anterior'] ?? '') !== ''): ?><div class="ap-muted">antes de aprobar: <?= esc($f['anterior']) ?></div><?php endif; ?>
        </td>
        <td>
            <?= esc(['empresa' => 'Dice ser de la empresa', 'cliente' => 'Cliente/proveedor', 'otro' => 'Otro'][$f['relacion']] ?? $f['relacion']) ?>
            <div class="ap-muted"><?= esc($f['email_contacto'] ?? 'sin email') ?><?= !empty($f['email_confirmado_at']) ? ' · <b style="color:#15803d">confirmado</b>' : '' ?></div>
            <div class="ap-muted">IP <?= esc($f['ip'] ?? '') ?></div>
        </td>
        <td style="max-width:280px">
            <?php if (!empty($f['verificacion'])): ?><b><?= esc(['cif_en_web' => 'CIF en la web', 'en_web_empresa' => 'En la web de la empresa', 'email_dominio' => 'Email de la empresa confirmado', 'manual' => 'A mano'][$f['verificacion']] ?? $f['verificacion']) ?></b><br><?php endif; ?>
            <span class="ap-muted"><?= $f['verificado_at'] ? esc($f['verificacion_detalle'] ?? '') : 'Aún sin comprobar (lanza el comando).' ?></span>
            <?php if (($f['nota_admin'] ?? '') !== ''): ?><div class="ap-muted">Nota: <?= esc($f['nota_admin']) ?></div><?php endif; ?>
        </td>
        <td><span class="ap-tag ap-tag--<?= esc($f['estado'], 'attr') ?>"><?= esc(ucfirst($f['estado'])) ?></span></td>
        <td>
            <?php if ($abierta): ?>
            <div class="ap-actions">
                <form method="post" action="<?= site_url('admin/aportaciones/' . (int) $f['id'] . '/aprobar') ?>"
                      onsubmit="return <?= $actual !== '' && $actual !== '-' ? 'confirm(\'La ficha ya tiene un dato: se sustituirá. ¿Seguir?\')' : 'true' ?>;">
                    <?= csrf_field() ?><button class="ap-ok" type="submit">Aprobar</button>
                </form>
                <form method="post" action="<?= site_url('admin/aportaciones/' . (int) $f['id'] . '/rechazar') ?>">
                    <?= csrf_field() ?><button class="ap-no" type="submit">Rechazar</button>
                </form>
            </div>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?= $this->endSection() ?>
