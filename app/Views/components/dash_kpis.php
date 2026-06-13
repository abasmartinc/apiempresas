<div id="kpi-section" class="kpi-grid-4" data-track-section="kpis" style="<?= ($requestsUsedThisMonth == 0 && !$isPaid && !(isset($isBonusUser) && $isBonusUser)) ? 'display: none;' : '' ?>">
    <?php 
        $requestsUsed = (int)$requestsUsedThisMonth;
        $kpiClass = '';
        if (!$isPaid) {
            if ($requestsUsed >= $freeLimit) $kpiClass = 'kpi-card--cta';
            elseif ($requestsUsed >= ($freeLimit * 0.7)) $kpiClass = 'kpi-card--warning';
        }
    ?>
    <div class="kpi-card-pro <?= $kpiClass ?>" <?= (!$isPaid && $requestsUsed >= $freeLimit) ? 'onclick="window.location.href=\''.site_url('billing').'\'"' : '' ?>>
        <div class="kpi-icon-box">
            <?php if (!$isPaid && $requestsUsed >= $freeLimit): ?>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            <?php else: ?>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20M12 2l4.5 4.5M12 22l-4.5-4.5M2 12l4.5 4.5M22 12l-4.5-4.5"/></svg>
            <?php endif; ?>
        </div>
        <div class="kpi-content">
            <span class="label"><?= (!$isPaid && $requestsUsed >= $freeLimit) ? 'Límite alcanzado' : ((isset($isBonusUser) && $isBonusUser) ? 'Consultas Bono' : (!$isPaid ? 'Consultas Totales' : 'Consultas Mes')) ?></span>
            <div class="value">
                <div id="kpi-requests-container">
                    <?php if ($requestsUsed > 0): ?>
                        <span id="kpi-requests"><?= $requestsUsed ?></span>
                    <?php else: ?>
                        <span id="kpi-requests" style="display:none;">0</span>
                        <span id="kpi-waiting-msg" class="waiting-pulse" style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; letter-spacing: 0.02em; white-space: nowrap; display: block; margin: 4px 0;">Esperando actividad...</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="meta"><?= (!$isPaid && $requestsUsed >= $freeLimit) ? '<strong>Activar Pro ahora &rarr;</strong>' : ((isset($isBonusUser) && $isBonusUser) ? 'Pago por Uso' : 'Límite: ' . ($isPaid ? number_format($maxLimit ?? 0, 0, ',', '.') : $freeLimit)) ?></div>
        </div>
    </div>
    
    <div class="kpi-card-pro">
        <div class="kpi-icon-box">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="kpi-content">
            <span class="label">Latencia</span>
            <div class="value">
                <span id="kpi-latency"><?= $requestsUsed > 0 ? '...' : '--' ?></span>
                <span class="value-unit" id="kpi-latency-unit" style="<?= $requestsUsed > 0 ? '' : 'display:none' ?>">ms</span>
            </div>
            <div class="meta">Velocidad real</div>
        </div>
    </div>

    <div class="kpi-card-pro">
        <div class="kpi-icon-box" style="background: #fff1f2; color: #e11d48;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="kpi-content">
            <span class="label">Ratio Error</span>
            <div class="value" id="kpi-error"><?= $requestsUsed > 0 ? '...' : '--' ?></div>
            <div class="meta">Tasa de fallo</div>
        </div>
    </div>

    <?php if (($walletBalance ?? 0) > 0 && !(isset($isBonusUser) && $isBonusUser)): ?>
    <div class="kpi-card-pro">
        <div class="kpi-icon-box" style="background: #f8fafc; color: #475569;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" /><path d="M4 6v12c0 1.1.9 2 2 2h14v-4" /><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z" /></svg>
        </div>
        <div class="kpi-content">
            <span class="label">Monedero Extra</span>
            <div class="value" style="color: #334155;"><?= number_format($walletBalance ?? 0, 0, ',', '.') ?></div>
            <div class="meta">Créditos sin caducidad</div>
        </div>
    </div>
    <?php else: ?>
    <div class="kpi-card-pro">
        <div class="kpi-icon-box" style="background: #ecfdf5; color: #10b981;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="kpi-content">
            <span class="label">Estado</span>
            <div class="value" style="color: #10b981;">Operativo</div>
            <div class="meta">Disponibilidad 99.9%</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('<?= site_url('dashboard/kpis') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if(data.error) return;
            const numFmt = new Intl.NumberFormat('es-ES');
            const reqVal = document.getElementById('kpi-requests');
            const latencyVal = document.getElementById('kpi-latency');
            const latencyUnit = document.getElementById('kpi-latency-unit');
            const errorVal = document.getElementById('kpi-error');

            const totalRequests = data.api_request_total_month || 0;
            const waitingMsg = document.getElementById('kpi-waiting-msg');

            if (totalRequests > 0) {
                if (reqVal) {
                    reqVal.innerText = numFmt.format(totalRequests);
                    reqVal.style.display = 'inline';
                }
                if (waitingMsg) waitingMsg.style.display = 'none';
                const kpiSec = document.getElementById('kpi-section');
                if (kpiSec) kpiSec.style.display = ''; // Reset to default (grid)

                if (latencyVal) latencyVal.innerText = numFmt.format(data.avg_latency || 0);
                if (latencyUnit) latencyUnit.style.display = 'inline';
                if (errorVal) errorVal.innerText = (data.error_rate || 0) + '%';
            } else {
                if (reqVal) reqVal.style.display = 'none';
                if (waitingMsg) {
                    waitingMsg.style.display = 'inline';
                } else if (reqVal) {
                    reqVal.innerText = '--';
                    reqVal.style.display = 'inline';
                }

                if (latencyVal) latencyVal.innerText = '--';
                if (latencyUnit) latencyUnit.style.display = 'none';
                if (errorVal) errorVal.innerText = '--';
            }
        })
        .catch(e => console.error('Error fetching KPIs', e));
    });
</script>
