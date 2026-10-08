<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
<?= view('admin/errores/_styles') ?>
<style>
    .et-head { padding: 22px 24px; margin-bottom: 20px; }
    .et-head h2 { margin: 8px 0 6px; font-size: 1.15rem; color: #0f172a; word-break: break-word; line-height: 1.4; }
    .et-meta { display: flex; gap: 18px; flex-wrap: wrap; margin-top: 14px; font-size: .82rem; color: #64748b; }
    .et-meta strong { color: #0f172a; font-variant-numeric: tabular-nums; }
    .et-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 20px; align-items: start; }
    @media (max-width: 1000px) { .et-grid { grid-template-columns: 1fr; } }
    .et-card { padding: 20px 22px; margin-bottom: 20px; }
    .et-card h3 { margin: 0 0 12px; font-size: .98rem; color: #0f172a; font-weight: 800; }
    .et-card h4 { margin: 18px 0 8px; font-size: .78rem; text-transform: uppercase; letter-spacing: .4px; color: #64748b; font-weight: 800; }
    .et-chart svg { width: 100%; height: auto; display: block; }
    .et-chart-tip { position: fixed; pointer-events: none; background: #0f172a; color: #fff; padding: 6px 10px; border-radius: 6px; font-size: .78rem; display: none; z-index: 1000; white-space: nowrap; }
    .et-kv { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .et-kv th { text-align: left; font-weight: 700; color: #64748b; padding: 5px 12px 5px 0; width: 140px; vertical-align: top; font-size: .82rem; }
    .et-kv td { padding: 5px 0; word-break: break-word; }
    .et-trace { list-style: none; margin: 0; padding: 0; font-family: ui-monospace, Consolas, monospace; font-size: .78rem; }
    .et-trace li { padding: 6px 10px; border-left: 3px solid #e2e8f0; margin-bottom: 4px; background: #f8fafc; border-radius: 0 6px 6px 0; word-break: break-all; }
    .et-trace li.app { border-left-color: #2152ff; }
    .et-trace .fn { color: #0f172a; }
    .et-trace .loc { color: #64748b; }
    pre.et-code { background: #0f172a; color: #e2e8f0; padding: 12px 14px; border-radius: 8px; font-size: .78rem; white-space: pre-wrap; word-break: break-all; margin: 0; }
    .et-evlist tr.sel td { background: #eef2ff; }
    .et-table.et-evlist { min-width: 640px; }
    .et-act-form { display: grid; gap: 8px; margin-bottom: 14px; }
    .et-act-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .et-timeline { list-style: none; margin: 0; padding: 0; }
    .et-timeline li { padding: 10px 0 10px 18px; border-left: 2px solid #e2e8f0; position: relative; font-size: .84rem; }
    .et-timeline li::before { content: ''; position: absolute; left: -6px; top: 14px; width: 10px; height: 10px; border-radius: 50%; background: #cbd5e1; }
    .et-timeline li.a-resolved::before { background: #16a34a; }
    .et-timeline li.a-regressed::before, .et-timeline li.a-created::before { background: #dc2626; }
    .et-timeline li.a-reopened::before { background: #ea580c; }
    .et-timeline .who { color: #64748b; font-size: .78rem; }
    .et-timeline .note { margin-top: 4px; white-space: pre-wrap; word-break: break-word; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    use App\Libraries\ErrorTracking as Tracker;

    $fmt = fn ($dt) => $dt ? date('d/m/Y H:i:s', strtotime($dt)) : '-';
    // Pinta las columnas JSON como clave/valor (valores escapados; lo anidado, como JSON)
    $kvRows = function (array $data) {
        $html = '';
        foreach ($data as $key => $value) {
            $shown = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
            $html .= '<tr><th>' . esc((string) $key) . '</th><td class="et-mono">' . esc($shown === '' ? '(vacío)' : $shown) . '</td></tr>';
        }

        return $html;
    };
    $isApp = fn ($file) => $file !== '' && !str_starts_with((string) $file, 'system/') && !str_starts_with((string) $file, 'vendor/');
    $status = $issue['status'];
    $base = site_url('admin/errores/' . (int) $issue['id']);
    $channelLabel = fn ($c) => Tracker::CHANNEL_LABELS[$c] ?? ($c ?: '-');
?>

<div class="et-head-row">
    <div>
        <h1 class="title" style="margin-bottom:4px">Issue #<?= (int) $issue['id'] ?></h1>
        <p class="subtitle"><?= esc(Tracker::TYPE_LABELS[$issue['type']] ?? (string) $issue['type']) ?> · <?= esc((string) $issue['level']) ?></p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="et-btn" id="etCopyAiPrompt" title="Copia un prompt con el error, la traza y la petición (sin email, IP ni navegador del usuario)">
            <span>Copiar prompt para Claude</span>
        </button>
        <a href="<?= site_url('admin/errores') ?>" class="et-btn">‹ Errores</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="et-alert" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">✓ <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="et-alert et-alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="et-box et-head">
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <?= view('admin/errores/_status', ['status' => $status]) ?>
        <span class="et-type et-type-<?= esc((string) $issue['type'], 'attr') ?>"><?= esc($issue['type'] === 'js' ? 'JS' : (string) $issue['type']) ?></span>
        <?php if ($issue['exception_class']): ?><span class="et-class"><?= esc($issue['exception_class']) ?></span><?php endif; ?>
    </div>
    <h2><?= esc((string) $issue['message']) ?></h2>
    <div class="et-file"><?= esc((string) $issue['file']) ?><?= $issue['line'] ? ':' . (int) $issue['line'] : '' ?></div>
    <div class="et-meta">
        <span><strong><?= number_format((int) $issue['occurrences'], 0, ',', '.') ?></strong> eventos</span>
        <span><strong><?= (int) $issue['users'] ?></strong> usuarios</span>
        <?php if (!empty($issue['channels'])): ?>
            <span>Canales <strong><?= esc(implode(', ', array_map(fn ($c, $n) => $channelLabel($c) . " ({$n})", array_keys($issue['channels']), $issue['channels']))) ?></strong></span>
        <?php endif; ?>
        <span>Primera vez <strong><?= esc($fmt($issue['first_seen'])) ?></strong></span>
        <span>Última vez <strong><?= esc($fmt($issue['last_seen'])) ?></strong></span>
        <?php if ($issue['last_release']): ?><span>Versión <strong class="et-mono"><?= esc($issue['last_release']) ?></strong></span><?php endif; ?>
        <?php if ($status === 'resolved'): ?>
            <span>Resuelto <strong><?= esc($fmt($issue['resolved_at'])) ?></strong> por <?= esc((string) $issue['resolved_by']) ?><?= $issue['resolved_release'] ? ' en <strong class="et-mono">' . esc($issue['resolved_release']) . '</strong>' : '' ?></span>
        <?php endif; ?>
    </div>
    <?php if ($issue['notes']): ?>
        <div style="margin-top: 12px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; font-size: .85rem; white-space: pre-wrap; word-break: break-word;">📝 <?= esc($issue['notes']) ?></div>
    <?php endif; ?>
</div>

<div class="et-grid">
    <div>
        <div class="et-box et-card">
            <h3>Eventos por día · últimos 30 días</h3>
            <div class="et-chart" id="etChartDaily"></div>
            <p style="margin: 8px 0 0; font-size: .75rem; color: #64748b;">
                Con los <?= (int) $issue['stored_events'] ?> eventos guardados: se guardan como mucho 200 por issue y 90 días (<code>php spark errors:prune</code>), así que un issue muy repetido enseña menos que sus <?= number_format((int) $issue['occurrences'], 0, ',', '.') ?> veces.
            </p>
        </div>

        <?php if ($event): ?>
        <div class="et-box et-card" id="event">
            <h3>Evento #<?= (int) $event['id'] ?> · <?= esc($fmt($event['created_at'])) ?></h3>

            <table class="et-kv">
                <tr><th>Canal</th><td><?= esc($channelLabel($event['group_name'])) ?><?= $event['company_id'] ? ' · <span class="et-mono">' . esc((string) $event['company_name']) . '</span>' : '' ?></td></tr>
                <tr><th>Usuario</th><td>
                    <?php if ($event['user_id']): ?>
                        <a href="<?= site_url('admin/users/edit/' . (int) $event['user_id']) ?>"><?= esc($event['user_email'] ?: ('#' . (int) $event['user_id'])) ?></a>
                    <?php else: ?>
                        <?= esc($event['user_email'] ?: '-') ?>
                    <?php endif; ?>
                    <?= $event['user_role'] ? ' · ' . esc($event['user_role']) : '' ?><?= $event['user_id'] ? ' <span style="color: #64748b;">#' . (int) $event['user_id'] . '</span>' : '' ?>
                </td></tr>
                <tr><th>Petición</th><td class="et-mono"><?= $event['is_cli'] ? 'CLI · ' : '' ?><?= esc(trim(($event['method'] ?? '') . ' ' . ($event['path'] ?? ''))) ?: '-' ?></td></tr>
                <tr><th>Ruta</th><td class="et-mono"><?= esc($event['route'] ?: '-') ?></td></tr>
                <tr><th>Código HTTP</th><td><?= esc((string) ($event['status_code'] ?? '-')) ?></td></tr>
                <tr><th>Referer</th><td class="et-mono"><?= esc($event['referer_path'] ?: '-') ?></td></tr>
                <tr><th>IP / navegador</th><td class="et-mono"><?= esc($event['ip'] ?: '-') ?><?= $event['user_agent'] ? '<br>' . esc($event['user_agent']) : '' ?></td></tr>
                <tr><th>Entorno</th><td><?= esc($event['environment'] ?: '-') ?> · <?= esc($event['server'] ?: '-') ?> · <span class="et-mono"><?= esc($event['app_release'] ?: '-') ?></span></td></tr>
                <?php if ($event['message'] && $event['message'] !== $issue['message']): ?>
                    <tr><th>Mensaje</th><td><?= esc($event['message']) ?></td></tr>
                <?php endif; ?>
            </table>

            <?php foreach (['get' => 'Parámetros de la URL', 'post' => 'Campos del formulario'] as $part => $label): ?>
                <?php if (!empty($event['request'][$part]) && is_array($event['request'][$part])): ?>
                    <h4><?= $label ?></h4>
                    <table class="et-kv"><?= $kvRows($event['request'][$part]) ?></table>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($event['trace']): ?>
                <h4>Traza</h4>
                <ol class="et-trace">
                    <?php foreach ($event['trace'] as $frame): ?>
                        <?php $file = (string) ($frame['file'] ?? ''); ?>
                        <li class="<?= $isApp($file) ? 'app' : '' ?>">
                            <span class="fn"><?= esc((string) ($frame['function'] ?? '')) ?></span><br>
                            <span class="loc"><?= esc($file ?: '[internal]') ?><?= !empty($frame['line']) ? ':' . (int) $frame['line'] : '' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <p style="margin: 6px 0 0; font-size: .72rem; color: #64748b;">Las líneas con la barra azul son de nuestro código (no de system/ ni vendor/).</p>
            <?php endif; ?>

            <?php if ($event['previous']): ?>
                <h4>Excepciones anteriores</h4>
                <ol class="et-trace">
                    <?php foreach ($event['previous'] as $prev): ?>
                        <li class="app">
                            <span class="fn"><?= esc((string) ($prev['class'] ?? '')) ?>: <?= esc((string) ($prev['message'] ?? '')) ?></span><br>
                            <span class="loc"><?= esc((string) ($prev['file'] ?? '')) ?><?= !empty($prev['line']) ? ':' . (int) $prev['line'] : '' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if (!empty($event['sql_query'])): ?>
                <h4>SQL</h4>
                <pre class="et-code"><?= esc($event['sql_query']) ?></pre>
            <?php endif; ?>

            <?php if ($event['extra']): ?>
                <?php $extra = $event['extra']; $jsStack = $extra['stack'] ?? null; unset($extra['stack']); ?>
                <h4>Extra</h4>
                <table class="et-kv"><?= $kvRows($extra) ?></table>
                <?php if ($jsStack): ?>
                    <h4>Pila de JavaScript</h4>
                    <pre class="et-code"><?= esc(is_array($jsStack) ? json_encode($jsStack, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $jsStack) ?></pre>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="et-box et-table-wrap" style="margin-bottom: 20px;">
            <h3 style="margin: 0; padding: 18px 22px 8px; font-size: .98rem; color: #0f172a; font-weight: 800;">Eventos</h3>
            <table class="et-table et-evlist">
                <thead><tr><th>Cuándo</th><th>Canal</th><th>Usuario</th><th>Petición</th><th style="text-align: right;">Código</th></tr></thead>
                <tbody>
                <?php foreach ($events as $e): ?>
                    <tr class="<?= $event && (int) $event['id'] === (int) $e['id'] ? 'sel' : '' ?>">
                        <td class="et-date"><a href="<?= esc($base . '?' . http_build_query(['event' => (int) $e['id'], 'page' => $page]), 'attr') ?>#event"><?= esc(date('d/m H:i:s', strtotime($e['created_at']))) ?></a></td>
                        <td><?= esc($channelLabel($e['group_name'])) ?></td>
                        <td><?= esc($e['user_email'] ?: '-') ?><?= $e['user_role'] ? '<div class="et-date">' . esc($e['user_role']) . '</div>' : '' ?></td>
                        <td class="et-mono"><?= $e['is_cli'] ? 'CLI ' : '' ?><?= esc(mb_strimwidth(trim(($e['method'] ?? '') . ' ' . ($e['path'] ?? '')), 0, 70, '…')) ?></td>
                        <td style="text-align: right;"><?= esc((string) ($e['status_code'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$events): ?>
                    <tr><td colspan="5" class="et-empty">No hay eventos guardados (puede que se borraran pasados 90 días).</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if ($pages > 1): ?>
                <div class="et-pager" style="padding: 12px;">
                    <?php if ($page > 1): ?><a class="et-btn et-btn--sm" href="<?= esc($base . '?page=' . ($page - 1), 'attr') ?>">‹</a><?php endif; ?>
                    <span>Página <?= $page ?> de <?= $pages ?></span>
                    <?php if ($page < $pages): ?><a class="et-btn et-btn--sm" href="<?= esc($base . '?page=' . ($page + 1), 'attr') ?>">›</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="et-box et-card">
            <h3>Acciones</h3>
            <?php if ($status !== 'resolved'): ?>
                <form method="post" action="<?= $base ?>/resolve" class="et-act-form">
                    <?= csrf_field() ?>
                    <input type="text" name="release" class="et-input" maxlength="64" placeholder="Arreglado en la versión / commit (opcional)">
                    <input type="text" name="note" class="et-input" placeholder="Nota (opcional)">
                    <button type="submit" class="et-btn et-btn--primary" style="justify-content:center;">✓ Resolver</button>
                </form>
            <?php endif; ?>
            <div class="et-act-row">
                <?php if ($status !== 'ignored'): ?>
                    <form method="post" action="<?= $base ?>/ignore">
                        <?= csrf_field() ?>
                        <button type="submit" class="et-btn et-btn--sm">Ignorar</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($status, ['resolved', 'ignored'], true)): ?>
                    <form method="post" action="<?= $base ?>/reopen">
                        <?= csrf_field() ?>
                        <button type="submit" class="et-btn et-btn--sm">↺ Reabrir</button>
                    </form>
                <?php endif; ?>
            </div>

            <h4>Añadir una nota</h4>
            <form method="post" action="<?= $base ?>/note" class="et-act-form">
                <?= csrf_field() ?>
                <textarea name="note" class="et-input" rows="3" required placeholder="Qué has visto, quién lo está mirando..."></textarea>
                <button type="submit" class="et-btn et-btn--sm" style="justify-self:start;">Añadir nota</button>
            </form>
        </div>

        <div class="et-box et-card">
            <h3>Historial</h3>
            <ul class="et-timeline">
                <?php foreach ($activity as $a): ?>
                    <li class="a-<?= esc((string) $a['action'], 'attr') ?>">
                        <strong><?= esc(Tracker::ACTION_LABELS[$a['action']] ?? ucfirst((string) $a['action'])) ?></strong>
                        <div class="who"><?= esc($fmt($a['created_at'])) ?> · <?= esc($a['actor'] === 'system' ? 'sistema' : (string) $a['actor']) ?></div>
                        <?php if ($a['note']): ?><div class="note"><?= esc($a['note']) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if (!$activity): ?><li>Sin actividad todavía.</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div id="etChartTip" class="et-chart-tip"></div>

<script>
    (function () {
        // Copiar el prompt para Claude
        const PROMPT = <?= json_encode($aiPrompt, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
        const btn = document.getElementById('etCopyAiPrompt');
        if (btn) {
            const label = btn.querySelector('span');
            // La API del portapapeles necesita HTTPS (o localhost); si no, un textarea oculto
            const fallbackCopy = (text) => {
                const area = document.createElement('textarea');
                area.value = text;
                area.setAttribute('readonly', '');
                area.style.position = 'fixed';
                area.style.opacity = '0';
                document.body.appendChild(area);
                area.select();
                const ok = document.execCommand('copy');
                area.remove();
                return ok;
            };
            btn.addEventListener('click', async () => {
                let ok = false;
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(PROMPT);
                        ok = true;
                    } else {
                        ok = fallbackCopy(PROMPT);
                    }
                } catch (e) {
                    ok = fallbackCopy(PROMPT);
                }
                label.textContent = ok ? '¡Copiado! Pégalo en Claude' : 'No se ha podido copiar';
                setTimeout(() => { label.textContent = 'Copiar prompt para Claude'; }, 2500);
            });
        }

        // Grafico de eventos por dia
        const DATA = <?= json_encode($daily, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const host = document.getElementById('etChartDaily');
        if (!host) return;
        const NS = 'http://www.w3.org/2000/svg', W = 600, H = 170, L = 30, R = 8, T = 10, B = 24;
        const el = (tag, attrs, parent) => { const n = document.createElementNS(NS, tag); for (const k in attrs) n.setAttribute(k, attrs[k]); parent && parent.appendChild(n); return n; };
        const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': 'Eventos por día' }, host);
        const tip = document.getElementById('etChartTip');
        const max = Math.max(1, ...DATA.map(d => d.n));
        const top = Math.max(2, Math.ceil(max / 2) * 2);
        const slot = (W - L - R) / DATA.length;
        const y = v => T + (H - T - B) * (1 - v / top);

        [0, top / 2, top].forEach((v, i) => {
            el('line', { x1: L, x2: W - R, y1: y(v), y2: y(v), stroke: i ? '#eef2f6' : '#cbd5e1' }, svg);
            el('text', { x: L - 6, y: y(v) + 4, 'text-anchor': 'end', 'font-size': 10, fill: '#94a3b8' }, svg).textContent = v;
        });
        DATA.forEach((d, i) => {
            const x = L + slot * i;
            if (i % 5 === 0) el('text', { x: x + slot / 2, y: H - 6, 'text-anchor': 'middle', 'font-size': 10, fill: '#94a3b8' }, svg).textContent = d.label;
            if (d.n > 0) {
                const h = Math.max(2, (H - T - B) - (y(d.n) - T));
                el('rect', { x: x + 2, y: H - B - h, width: Math.max(2, slot - 4), height: h, rx: 2, fill: '#2152ff' }, svg);
            }
            const hit = el('rect', { x, y: T, width: slot, height: H - T - B, fill: 'transparent' }, svg);
            hit.addEventListener('mousemove', e => {
                tip.textContent = d.label + ': ' + d.n + ' evento' + (d.n === 1 ? '' : 's');
                tip.style.display = 'block'; tip.style.left = (e.clientX + 12) + 'px'; tip.style.top = (e.clientY - 34) + 'px';
            });
            hit.addEventListener('mouseleave', () => { tip.style.display = 'none'; });
        });
    })();
</script>

<?= $this->endSection() ?>
