<?php
$isBonusUser = (($walletBalance ?? 0) > 0);
if ($isBonusUser && !($isPaid ?? false)) {
    $isPaid = true; // Tratar como usuario de pago para quitar bloqueos
    $planNameRaw = 'Bono API Prepago';
    $currentPlanSlug = 'custom_bonus';
    $requestsUsed = 0; // Quitar limitación del buscador
}
?>
<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <div class="container">
            <?= view('partials/usage_trigger_banner') ?>
            <?= view('components/onboarding_modal') ?>

            <?php 
                $userName = 'Cliente';
                if (is_object($user)) $userName = $user->name ?? 'Cliente';
                elseif (is_array($user)) $userName = $user['name'] ?? 'Cliente';

                $get = function($src, $key, $default = null) {
                    if (is_array($src)) return $src[$key] ?? $default;
                    if (is_object($src)) return $src->$key ?? $default;
                    return $default;
                };
                $planNameRaw = $get($plan, 'plan_name', 'Free');
                $currentPlanSlug = strtolower(trim($planNameRaw));
                $isPaid = ($currentPlanSlug !== 'free' && !empty($currentPlanSlug));

                $planClass = '';
                if (strpos($currentPlanSlug, 'business') !== false) $planClass = 'plan-card--business';
                elseif (strpos($currentPlanSlug, 'pro') !== false) $planClass = 'plan-card--pro';

                $reqCount = 0;
                $hasRadar = false;
                if (isset($user)) {
                    if (is_array($user)) {
                        $reqCount = $user['requests_count'] ?? 0;
                        $hasRadar = $user['has_radar'] ?? false;
                    } else if (is_object($user)) {
                        $reqCount = $user->requests_count ?? 0;
                        $hasRadar = (method_exists($user, 'hasRadar')) ? $user->hasRadar() : ($user->has_radar ?? false);
                    }
                }

                $usagePercent = 0;
                if (isset($plan) && $get($plan, 'monthly_quota', 0) > 0) {
                    $usagePercent = round(($reqCount / $get($plan, 'monthly_quota', 0)) * 100);
                }
            ?>

            <div class="dash-header">
                <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
                
                <?php if (!empty($usageMessage)): ?>
                    <?= view('components/ui/alert', [
                        'type' => 'info',
                        'title' => $usageMessage['title'],
                        'text' => $usageMessage['text']
                    ]) ?>
                <?php endif; ?>

                <?php if (!empty($answeredTickets)): ?>
                    <?php foreach($answeredTickets as $t): ?>
                        <?= view('components/ui/alert', [
                            'type' => 'success',
                            'title' => 'Ticket Respondido: ' . $t['subject'],
                            'text' => 'Nuestro equipo de soporte ha respondido a tu ticket.',
                            'actionUrl' => site_url('tickets/view/'.$t['id']),
                            'actionText' => 'Ver respuesta &rarr;'
                        ]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?= view('components/dash_kpis', get_defined_vars()) ?>

            <div class="dash-grid">
                <div>
                    <?= view('components/dash_search_card', get_defined_vars()) ?>
                    <?= view('components/dash_api_key', get_defined_vars()) ?>
                    <?= view('components/dash_integration', get_defined_vars()) ?>
                </div>

                <aside>
                    <?= view('components/dash_sidebar', get_defined_vars()) ?>
                </aside>
            </div>


            <!-- HELP SECTION -->
            <?= view('components/help_section') ?>
        </div>
    <?= $this->endSection() ?>

<?= $this->section('scripts') ?>
</div>

<script>
    (function(){
        const box = document.getElementById('apiKeyBox');
        if(!box) return;
        const realKey = box.getAttribute('data-api-key') || '';
        const masked = '•'.repeat(Math.max(realKey.length - 8, 12));
        const valueEl = document.getElementById('apiKeyMasked');
        const btnToggle = document.getElementById('btnToggleKey');
        const btnCopy = document.getElementById('btnCopyKey');
        let visible = false;
        valueEl.textContent = masked;
        btnToggle.addEventListener('click', () => {
            visible = !visible;
            valueEl.textContent = visible ? realKey : masked;
            btnToggle.textContent = visible ? 'Ocultar' : 'Mostrar';
        });
        btnCopy.addEventListener('click', async () => {
            try{
                await navigator.clipboard.writeText(realKey);
                btnCopy.textContent = 'Copiado ✓';
                
                // Tracking: API Key Copied
                if (window.trackEvent) {
                    window.trackEvent('api_key_copied', {
                        source: 'dashboard',
                        page_type: 'dashboard'
                    });
                }
                
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){ alert('Error al copiar.'); }
        });
    })();

    // === NEW: Dashboard Validation Logic ===
    (function(){
        const btnValidate = document.getElementById('btnDashValidate');
        const inputQ = document.getElementById('dash_q');
        const ahaCard = document.getElementById('aha-moment-card');
        const realKey = document.getElementById('apiKeyBox')?.getAttribute('data-api-key') || '';

        if(!btnValidate) return;

        async function validateCompany() {
            const q = inputQ.value.trim();
            if(!q) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo vacío',
                    text: 'Por favor, introduce un CIF para buscar.',
                    confirmButtonColor: '#2152ff',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (window.trackEvent) trackEvent('dashboard_search_executed', { query: q });

            btnValidate.disabled = true;
            btnValidate.textContent = 'Validando...';

            try {
                const res = await fetch(`<?= site_url('api/v1/companies') ?>?cif=${encodeURIComponent(q)}`, {
                    headers: { 'X-API-KEY': realKey, 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if(data.success && data.data) {
                    const comp = data.data;
                    document.getElementById('aha-name').textContent = comp.name || '-';
                    document.getElementById('aha-cif').textContent = comp.cif || '-';
                    document.getElementById('aha-status').textContent = comp.status || 'Activa';
                    
                    <?php if (!$isPaid && $walletBalance <= 0): ?>
                    document.getElementById('aha-address').innerHTML = '<span style="filter: blur(4px); user-select: none;">Calle Falsa 123, 28080 Madrid</span> <span style="font-size:0.8rem; color:#e11d48; margin-left:8px; font-weight:800;">🔒 Pro</span>';
                    document.getElementById('aha-activity').innerHTML = '<span style="filter: blur(4px); user-select: none;">La prestación de servicios de consultoría...</span> <span style="font-size:0.8rem; color:#e11d48; margin-left:8px; font-weight:800;">🔒 Pro</span>';
                    <?php else: ?>
                    document.getElementById('aha-address').textContent = comp.address || '-';
                    document.getElementById('aha-activity').textContent = comp.corporate_purpose || '-';
                    <?php endif; ?>
                    
                    ahaCard.style.display = 'block';
                    
                    // Remove internal AI fields from JSON view
                    if (data.data) {
                        delete data.data.ai_seo_text;
                        delete data.data.ai_faqs;
                    }
                    
                    // Update Inline JSON
                    const jsonContainer = document.getElementById('inlineJsonContainer');
                    const jsonPre = document.getElementById('inlineJsonPre');
                    if (jsonContainer && jsonPre) {
                        jsonPre.textContent = JSON.stringify(data, null, 4);
                        jsonContainer.style.display = 'block';
                    }
                    
                    ahaCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Store for JSON view
                    window.lastAhaResult = data;

                    // If it was the first request, visually activate the API Key section instead of reloading
                    <?php if ($requestsUsedThisMonth == 0): ?>
                    const sectionApiKey = document.getElementById('section-api-key');
                    if (sectionApiKey) {
                        sectionApiKey.style.opacity = '1';
                        sectionApiKey.style.borderColor = '#2152ff';
                        sectionApiKey.style.boxShadow = '0 10px 15px -3px rgba(33, 82, 255, 0.1)';
                        const paso2Badge = sectionApiKey.querySelector('div');
                        if (paso2Badge) paso2Badge.style.background = '#2152ff';
                        
                        const pDesc = sectionApiKey.querySelector('p');
                        if (pDesc) pDesc.textContent = 'Usa tu clave para conectar tu sistema.';
                        
                        const btnCopyKey = document.getElementById('btnCopyKey');
                        if (btnCopyKey) {
                            btnCopyKey.style.background = '';
                            btnCopyKey.style.borderColor = '';
                        }
                    }
                    
                    const sectionPaso3 = document.getElementById('section-paso3');
                    if (sectionPaso3) {
                        sectionPaso3.style.opacity = '1';
                        sectionPaso3.style.pointerEvents = 'auto';
                        const paso3Badge = sectionPaso3.querySelector('div');
                        if (paso3Badge) paso3Badge.style.background = '#2152ff';
                    }
                    <?php endif; ?>
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: data.message || 'No se encontró la empresa.',
                        confirmButtonColor: '#2152ff',
                        confirmButtonText: 'Aceptar'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error en la validación. Revisa tu conexión.',
                    confirmButtonColor: '#2152ff',
                    confirmButtonText: 'Aceptar'
                });
            } finally {
                btnValidate.disabled = false;
                btnValidate.textContent = 'Validar empresa ahora';
            }
        }

        btnValidate.addEventListener('click', validateCompany);
        inputQ.addEventListener('keypress', (e) => { if(e.key === 'Enter') validateCompany(); });

        document.getElementById('btnShowJson')?.addEventListener('click', () => {
            if(window.lastAhaResult) {
                const jsonStr = JSON.stringify(window.lastAhaResult, null, 4);
                Swal.fire({
                    title: 'Respuesta JSON',
                    html: `
                        <div style="text-align: left;">
                            <pre id="jsonPre" style="background: #0f172a; color: #f8fafc; padding: 16px; border-radius: 8px; font-size: 12px; max-height: 400px; overflow: auto; margin-bottom: 16px;">${jsonStr}</pre>
                            <button id="swalCopyJson" class="btn primary" style="width: 100%; border: none;">Copiar JSON</button>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '600px',
                    didOpen: () => {
                        document.getElementById('swalCopyJson').addEventListener('click', () => {
                            navigator.clipboard.writeText(jsonStr);
                            const btn = document.getElementById('swalCopyJson');
                            btn.textContent = '¡Copiado!';
                            btn.style.background = '#10b981';
                            setTimeout(() => {
                                btn.textContent = 'Copiar JSON';
                                btn.style.background = '#2152ff';
                            }, 2000);
                        });
                    }
                });
            }
        });

        document.getElementById('btnCopyInlineJson')?.addEventListener('click', () => {
            const pre = document.getElementById('inlineJsonPre');
            if(pre && pre.textContent) {
                navigator.clipboard.writeText(pre.textContent);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'JSON Copiado', showConfirmButton: false, timer: 1500
                });
            }
        });

        document.getElementById('btnCopyEndpoint')?.addEventListener('click', () => {
            const endpoint = `<?= site_url('api/v1/companies') ?>?cif=${encodeURIComponent(inputQ.value || 'B12345678')}`;
            navigator.clipboard.writeText(endpoint);
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Endpoint copiado',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        });

        document.getElementById('btnRotateKey')?.addEventListener('click', async () => {
            const { value: confirmed } = await Swal.fire({
                title: '¿Regenerar API Key?',
                text: 'La clave actual dejará de funcionar inmediatamente en todas tus integraciones.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, regenerar',
                cancelButtonText: 'Cancelar'
            });

            if(confirmed) {
                try {
                    const res = await fetch('<?= site_url('billing/rotate-key') ?>', {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                        }
                    });
                    const data = await res.json();
                    if(data.status === 'success' || data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡API Key regenerada!',
                            text: 'Actualiza tus sistemas con la nueva clave.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo regenerar.', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error de conexión al servidor.', 'error');
                }
            }
        });
    })();

    // === DEBUG: Reset Onboarding via URL ===
    if(window.location.search.includes('reset_onboarding=1')) {
        localStorage.removeItem('onboarding_completed');
        localStorage.removeItem('user_sector');
        localStorage.removeItem('user_province');
        window.location.href = window.location.pathname;
    }
</script>

<script>
function showPluginComingSoon() {
    Swal.fire({
        title: '¡Muy pronto!',
        text: 'Estamos terminando los últimos detalles del plugin oficial de WordPress. Te avisaremos en cuanto esté disponible para descarga.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#2152ff',
        background: '#ffffff',
        customClass: {
            popup: 'premium-swal-popup'
        }
    });
}

function showUpgradeModal() {
    // Tracking: Upgrade Limit Reached View
    if (window.trackEvent) {
        window.trackEvent('upgrade_limit_reached_view', {
            source: 'dashboard_search',
            limit_type: 'free_quota',
            page_type: 'dashboard'
        });
    }

    Swal.fire({
        title: '¡Límite alcanzado!',
        text: 'Has consumido tus <?= $freeLimit ?> consultas gratuitas. Activa el Plan Pro para tener acceso ilimitado y automatizar tus procesos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Activar Plan Pro',
        cancelButtonText: 'Ahora no',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        background: '#ffffff',
        customClass: {
            popup: 'premium-swal-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= site_url('billing') ?>';
        }
    });
}


</script>
<?= $this->endSection() ?>

