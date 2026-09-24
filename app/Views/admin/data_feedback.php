<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>
<?= $this->section('styles') ?>
<style>
    .df-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:1.5rem}
    .df-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px}
    .df-kpi__label{font-size:.75rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em}
    .df-kpi__value{font-size:1.8rem;font-weight:900;color:#0f172a;margin-top:4px}
    .df-kpi__sub{font-size:.8rem;color:#64748b}
    .df-two{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:1.5rem}
    .df-box{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px}
    .df-box h3{margin:0 0 10px;font-size:.95rem;font-weight:800;color:#0f172a}
    .df-bar{display:flex;align-items:center;gap:10px;font-size:.85rem;margin:6px 0}
    .df-bar span:first-child{width:170px;color:#334155}
    .df-bar__track{flex:1;height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden}
    .df-bar__fill{height:100%;background:#2563eb;border-radius:99px}
    .df-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:14px}
    .df-filters label{display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:4px}
    .df-filters select{border:1px solid #cbd5e1;border-radius:8px;padding:7px 10px;font-size:.85rem}
    .df-table{width:100%;border-collapse:collapse;min-width:900px}
    .df-table th{padding:10px 12px;color:#64748b;font-size:.8rem;text-align:left;border-bottom:2px solid #f1f5f9}
    .df-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;font-size:.87rem;vertical-align:top}
    .df-tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700}
    .df-tag--no{background:#fef2f2;color:#b91c1c}
    .df-tag--ok{background:#f0fdf4;color:#15803d}
    .df-tag--old{background:#f1f5f9;color:#475569}
    @media (max-width:800px){.df-two{grid-template-columns:1fr}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:10px">
    <div>
        <h1 class="title" style="margin-bottom:4px">Avisos de datos</h1>
        <p class="subtitle" style="margin:0">Lo que responden los visitantes a «¿Son correctos estos datos?» en la ficha de empresa (últimos <?= (int) $dias ?> días).</p>
    </div>
</div>

<div class="df-kpis">
    <div class="df-kpi">
        <div class="df-kpi__label">Respuestas</div>
        <div class="df-kpi__value"><?= number_format($total, 0, ',', '.') ?></div>
        <div class="df-kpi__sub">Sí + No en el periodo</div>
    </div>
    <div class="df-kpi">
        <div class="df-kpi__label">Datos correctos</div>
        <div class="df-kpi__value" style="color:#15803d"><?= $total > 0 ? round($correctos * 100 / $total) : 0 ?>%</div>
        <div class="df-kpi__sub"><?= number_format($correctos, 0, ',', '.') ?> respuestas «Sí»</div>
    </div>
    <div class="df-kpi">
        <div class="df-kpi__label">Avisos de error</div>
        <div class="df-kpi__value" style="color:#b91c1c"><?= number_format($errores, 0, ',', '.') ?></div>
        <div class="df-kpi__sub">Respuestas «No, hay un error»</div>
    </div>
</div>

<div class="df-two">
    <div class="df-box">
        <h3>Qué dato falla</h3>
        <?php $maxCampo = max(1, max($porCampo ?: [0])); ?>
        <?php foreach ($porCampo as $k => $n): ?>
            <div class="df-bar">
                <span><?= esc($campos[$k] ?? $k) ?></span>
                <span class="df-bar__track"><span class="df-bar__fill" style="display:block;width:<?= round($n * 100 / $maxCampo) ?>%"></span></span>
                <strong><?= (int) $n ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="df-box">
        <h3>Empresas con más avisos de error</h3>
        <?php if (empty($topEmpresas)): ?>
            <p style="color:#64748b;font-size:.85rem;margin:0">Todavía no hay avisos en este periodo.</p>
        <?php else: ?>
            <?php helper('company'); foreach ($topEmpresas as $e): ?>
                <div class="df-bar">
                    <span style="width:auto;flex:1">
                        <a href="<?= esc(company_url(['cif' => $e['cif'] ?? '', 'name' => $e['company_name'] ?? ''])) ?>" target="_blank" rel="noopener"><?= esc($e['company_name'] ?? ('#' . $e['company_id'])) ?></a>
                        <small style="color:#94a3b8"><?= esc($e['cif'] ?? '') ?></small>
                    </span>
                    <strong><?= (int) $e['n'] ?></strong>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="padding:18px">
    <form method="get" action="<?= site_url('admin/avisos-datos') ?>" class="df-filters">
        <div>
            <label>Mostrar</label>
            <select name="vista">
                <option value="errores" <?= $vista === 'errores' ? 'selected' : '' ?>>Solo avisos de error</option>
                <option value="todos" <?= $vista === 'todos' ? 'selected' : '' ?>>Sí y No</option>
                <option value="antiguas" <?= $vista === 'antiguas' ? 'selected' : '' ?>>Valoraciones antiguas (estrellas)</option>
            </select>
        </div>
        <div>
            <label>Dato</label>
            <select name="campo">
                <option value="">Todos</option>
                <?php foreach ($campos as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $campo === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Periodo</label>
            <select name="dias">
                <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => '1 año', 3650 => 'Todo'] as $d => $l): ?>
                    <option value="<?= $d ?>" <?= (int) $dias === $d ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Filtrar</button>
    </form>

    <div style="overflow-x:auto">
        <table class="df-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Empresa</th>
                    <th>Respuesta</th>
                    <th>Dato</th>
                    <th>Valor correcto que indican</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filas)): ?>
                    <tr><td colspan="6" style="color:#64748b">No hay avisos con estos filtros.</td></tr>
                <?php endif; ?>
                <?php helper('company'); foreach ($filas as $f): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= esc(date('d/m/Y H:i', strtotime($f['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <a href="<?= esc(company_url(['cif' => $f['cif'] ?? '', 'name' => $f['company_name'] ?? ''])) ?>" target="_blank" rel="noopener"><?= esc($f['company_name'] ?? ('#' . $f['company_id'])) ?></a>
                            <br><small style="color:#94a3b8"><?= esc($f['cif'] ?? '') ?></small>
                        </td>
                        <td>
                            <?php if (!$f['d_nuevo']): ?>
                                <span class="df-tag df-tag--old"><?= (int) $f['rating'] ?> ★</span>
                            <?php elseif ((int) $f['rating'] === 1): ?>
                                <span class="df-tag df-tag--no">Hay un error</span>
                            <?php else: ?>
                                <span class="df-tag df-tag--ok">Correctos</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($campos[$f['d_campo']] ?? $f['d_campo']) ?></td>
                        <td><?= esc($f['d_nuevo'] ? $f['d_valor'] : ($f['feedback'] ?? '')) ?></td>
                        <td>
                            <?php if ($f['d_email'] !== ''): ?>
                                <a href="mailto:<?= esc($f['d_email'], 'attr') ?>"><?= esc($f['d_email']) ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
