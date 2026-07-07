<div id="kpi-section" class="kpi-grid-4" data-track-section="kpis" style="<?= ($requestsUsedThisMonth == 0 && !$isPaid && !(isset($isBonusUser) && $isBonusUser)) ? 'display: none;' : '' ?>">
    <?php 
        $requestsUsed = (int)$requestsUsedThisMonth;
        
        $theme1 = 'default';
        if (!$isPaid) {
            if ($requestsUsed >= $freeLimit) $theme1 = 'cta';
            elseif ($requestsUsed >= ($freeLimit * 0.7)) $theme1 = 'warning';
        }

        $icon1 = (!$isPaid && $requestsUsed >= $freeLimit) 
            ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>'
            : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20M12 2l4.5 4.5M12 22l-4.5-4.5M2 12l4.5 4.5M22 12l-4.5-4.5"/></svg>';
        
        $label1 = (!$isPaid && $requestsUsed >= $freeLimit) ? 'Límite alcanzado' : ((isset($isBonusUser) && $isBonusUser) ? 'Consultas Bono' : (!$isPaid ? 'Consultas Totales' : 'Consultas Mes'));
        
        $val1 = '<div id="kpi-requests-container">';
        if ($requestsUsed > 0) {
            $val1 .= '<span id="kpi-requests">'.$requestsUsed.'</span>';
        } else {
            $val1 .= '<span id="kpi-requests" style="display:none;">0</span><span id="kpi-waiting-msg" class="waiting-pulse" style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; letter-spacing: 0.02em; white-space: nowrap; display: block; margin: 4px 0;">Esperando actividad...</span>';
        }
        $val1 .= '</div>';
        
        $meta1 = (!$isPaid && $requestsUsed >= $freeLimit) ? '<strong>Activar Pro ahora &rarr;</strong>' : ((isset($isBonusUser) && $isBonusUser) ? 'Pago por Uso' : 'Límite: ' . ($isPaid ? number_format($maxLimit ?? 0, 0, ',', '.') : $freeLimit));
        
        $attr1 = (!$isPaid && $requestsUsed >= $freeLimit) ? 'onclick="window.location.href=\''.site_url('billing').'\'"' : '';
    ?>
    <?= view('components/ui/kpi', [
        'theme' => $theme1,
        'icon' => $icon1,
        'label' => $label1,
        'value' => $val1,
        'meta' => $meta1,
        'extraAttributes' => $attr1
    ]) ?>
    
    <?= view('components/ui/kpi', [
        'theme' => 'default',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
        'label' => 'Latencia',
        'value' => '<span id="kpi-latency">'.($requestsUsed > 0 ? '...' : '--').'</span> <span class="value-unit" id="kpi-latency-unit" style="'.($requestsUsed > 0 ? '' : 'display:none').'">ms</span>',
        'meta' => 'Velocidad real'
    ]) ?>

    <?= view('components/ui/kpi', [
        'theme' => 'error',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'label' => 'Ratio Error',
        'value' => '<div id="kpi-error">'.($requestsUsed > 0 ? '...' : '--').'</div>',
        'meta' => 'Tasa de fallo'
    ]) ?>

    <?php if (($walletBalance ?? 0) > 0 && !(isset($isBonusUser) && $isBonusUser)): ?>
        <?= view('components/ui/kpi', [
            'theme' => 'default',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" /><path d="M4 6v12c0 1.1.9 2 2 2h14v-4" /><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z" /></svg>',
            'label' => 'Monedero Extra',
            'value' => '<span style="color: #334155;">'.number_format($walletBalance ?? 0, 0, ',', '.').'</span>',
            'meta' => 'Créditos sin caducidad'
        ]) ?>
    <?php else: ?>
        <?= view('components/ui/kpi', [
            'theme' => 'success',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            'label' => 'Estado',
            'value' => '<span style="color: #10b981;">Operativo</span>',
            'meta' => 'Disponibilidad 99.9%'
        ]) ?>
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
