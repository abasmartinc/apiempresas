<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
<?= view('admin/errores/_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    use App\Libraries\ErrorTracking as Tracker;

    // status[]='' mantiene "todos los estados" (ninguno marcado) en vez de volver a abiertos + reabiertos
    $query = fn (array $extra = []) => '?' . http_build_query(array_merge(
        array_filter($filters, fn ($v) => $v !== '' && $v !== []),
        ['status' => $filters['status'] ?: ['']],
        $extra
    ));
    $backQuery = $query(['page' => $page]);
    $fmt = fn ($dt) => $dt ? date('d/m/Y H:i', strtotime($dt)) : '-';
?>

<div class="et-head-row">
    <div>
        <h1 class="title" style="margin-bottom:4px">Errores</h1>
        <p class="subtitle">Errores de la web, la API, el admin y los comandos, agrupados en issues. Resuélvelos, ignóralos o reábrelos.</p>
    </div>
    <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="et-alert" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">✓ <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="et-alert et-alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (!empty($dbError)): ?>
    <div class="et-alert et-alert--error">
        <strong>No se pueden leer las tablas de errores.</strong> Comprueba que existen <code>error_issues</code>, <code>error_events</code> y <code>error_issue_activity</code> en la base de datos.<br>
        <span class="et-mono"><?= esc($dbError) ?></span>
    </div>
<?php endif; ?>

<div class="et-kpis">
    <a class="et-box et-kpi" href="<?= site_url('admin/errores') ?>?status[]=open">
        <div class="et-kpi__label">Abiertos</div>
        <div class="et-kpi__value" style="color:#b91c1c"><?= number_format($counters['open'], 0, ',', '.') ?></div>
    </a>
    <a class="et-box et-kpi" href="<?= site_url('admin/errores') ?>?status[]=regressed">
        <div class="et-kpi__label">Reabiertos (han vuelto)</div>
        <div class="et-kpi__value" style="color:#c2410c"><?= number_format($counters['regressed'], 0, ',', '.') ?></div>
    </a>
    <div class="et-box et-kpi">
        <div class="et-kpi__label">Nuevos en 24 h</div>
        <div class="et-kpi__value"><?= number_format($counters['new24h'], 0, ',', '.') ?></div>
    </div>
    <div class="et-box et-kpi">
        <div class="et-kpi__label">Eventos en 24 h</div>
        <div class="et-kpi__value"><?= number_format($counters['events24h'] ?? 0, 0, ',', '.') ?></div>
    </div>
</div>

<form method="get" action="<?= site_url('admin/errores') ?>" class="et-box et-filters">
    <div class="et-field">
        <label>Estado</label>
        <div class="et-checks">
            <input type="hidden" name="status[]" value="">
            <?php foreach (Tracker::STATUSES as $s): ?>
                <label><input type="checkbox" name="status[]" value="<?= $s ?>" <?= in_array($s, $filters['status'], true) ? 'checked' : '' ?>> <?= esc(Tracker::STATUS_LABELS[$s]) ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="et-field">
        <label>Tipo</label>
        <select name="type" class="et-input">
            <option value="">Todos</option>
            <?php foreach (Tracker::TYPES as $t): ?>
                <option value="<?= $t ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= esc(Tracker::TYPE_LABELS[$t]) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="et-field">
        <label>Canal</label>
        <select name="group" class="et-input">
            <option value="">Todos</option>
            <?php foreach ($channels as $c): ?>
                <option value="<?= esc($c, 'attr') ?>" <?= $filters['group'] === $c ? 'selected' : '' ?>><?= esc(Tracker::CHANNEL_LABELS[$c] ?? $c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="et-field grow">
        <label>Buscar</label>
        <input type="search" name="q" class="et-input" value="<?= esc($filters['q'], 'attr') ?>" placeholder="Mensaje, fichero o clase">
    </div>
    <div class="et-field">
        <label>Última vez desde</label>
        <input type="date" name="from" class="et-input" value="<?= esc($filters['from'], 'attr') ?>">
    </div>
    <div class="et-field">
        <label>hasta</label>
        <input type="date" name="to" class="et-input" value="<?= esc($filters['to'], 'attr') ?>">
    </div>
    <div class="et-field" style="flex-direction: row; gap: 8px;">
        <button type="submit" class="et-btn et-btn--primary">Filtrar</button>
        <a href="<?= site_url('admin/errores') ?>" class="et-btn" title="Volver a abiertos y reabiertos">✕</a>
    </div>
</form>

<form method="post" action="<?= site_url('admin/errores/bulk') ?>" id="etBulkForm">
    <?= csrf_field() ?>
    <input type="hidden" name="back" value="<?= esc($backQuery, 'attr') ?>">

    <div class="et-bulk" id="etBulkBar">
        <span><span id="etSelCount">0</span> marcados</span>
        <input type="text" name="note" class="et-input" placeholder="Nota (opcional)" style="max-width: 320px;">
        <button type="submit" name="action" value="resolve" class="et-btn et-btn--primary et-btn--sm">✓ Resolver</button>
        <button type="submit" name="action" value="ignore" class="et-btn et-btn--sm">Ignorar</button>
    </div>

    <div class="et-box et-table-wrap">
        <table class="et-table">
            <thead>
                <tr>
                    <th style="width: 36px;"><input type="checkbox" id="etSelAll" aria-label="Marcar todos"></th>
                    <th>Estado</th>
                    <th>Issue</th>
                    <th style="text-align: right;">Eventos</th>
                    <th style="text-align: right;">Usuarios</th>
                    <th>Primera vez</th>
                    <th>Última vez</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($issues as $i): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $i['id'] ?>" class="et-sel" aria-label="Marcar issue"></td>
                    <td><?= view('admin/errores/_status', ['status' => $i['status']]) ?></td>
                    <td class="et-issue">
                        <a href="<?= site_url('admin/errores/' . (int) $i['id']) ?>" class="et-msg"><?= esc(mb_strimwidth((string) $i['message'], 0, 160, '…')) ?></a>
                        <div class="et-sub">
                            <span class="et-type et-type-<?= esc($i['type'], 'attr') ?>"><?= esc($i['type'] === 'js' ? 'JS' : $i['type']) ?></span>
                            <?php if ($i['last_group_name']): ?><span class="et-type"><?= esc(Tracker::CHANNEL_LABELS[$i['last_group_name']] ?? $i['last_group_name']) ?></span><?php endif; ?>
                            <?php if ($i['exception_class']): ?><span class="et-class"><?= esc($i['exception_class']) ?></span><?php endif; ?>
                            <span class="et-file"><?= esc((string) $i['file']) ?><?= $i['line'] ? ':' . (int) $i['line'] : '' ?></span>
                        </div>
                    </td>
                    <td class="et-num"><?= number_format((int) $i['occurrences'], 0, ',', '.') ?></td>
                    <td class="et-num"><?= (int) $i['users'] ?></td>
                    <td class="et-date" title="<?= esc($i['first_seen'], 'attr') ?>"><?= esc($fmt($i['first_seen'])) ?></td>
                    <td class="et-date" title="<?= esc($i['last_seen'], 'attr') ?>"><?= esc($fmt($i['last_seen'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$issues): ?>
                <tr><td colspan="7" class="et-empty"><span style="color:#16a34a;">✓</span> No hay issues con estos filtros.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>

<?php if ($pages > 1): ?>
    <div class="et-pager">
        <?php if ($page > 1): ?><a class="et-btn et-btn--sm" href="<?= esc(site_url('admin/errores') . $query(['page' => $page - 1]), 'attr') ?>">‹</a><?php endif; ?>
        <span>Página <?= $page ?> de <?= $pages ?> · <?= number_format($total, 0, ',', '.') ?> issues</span>
        <?php if ($page < $pages): ?><a class="et-btn et-btn--sm" href="<?= esc(site_url('admin/errores') . $query(['page' => $page + 1]), 'attr') ?>">›</a><?php endif; ?>
    </div>
<?php endif; ?>

<script>
    (function () {
        const boxes = Array.from(document.querySelectorAll('.et-sel'));
        const bar = document.getElementById('etBulkBar');
        const all = document.getElementById('etSelAll');
        if (!bar || !all) return;
        const refresh = () => {
            const n = boxes.filter(b => b.checked).length;
            document.getElementById('etSelCount').innerText = n;
            bar.classList.toggle('show', n > 0);
        };
        boxes.forEach(b => b.addEventListener('change', refresh));
        all.addEventListener('change', function () {
            boxes.forEach(b => { b.checked = this.checked; });
            refresh();
        });
    })();
</script>

<?= $this->endSection() ?>
