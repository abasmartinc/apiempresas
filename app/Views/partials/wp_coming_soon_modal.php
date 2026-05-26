<!-- PRO MODAL: COMING SOON -->
<div id="wp-coming-soon-modal"
    style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <!-- Backdrop -->
    <div style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);"></div>

    <!-- Modal Card -->
    <div
        style="position: relative; background: #fff; width: 100%; max-width: 520px; border-radius: 32px; padding: 48px 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); text-align: left; overflow: hidden;">
        <!-- Decoration -->
        <div
            style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%); pointer-events: none;">
        </div>

        <button type="button" onclick="document.getElementById('wp-coming-soon-modal').style.display = 'none'" style="position: absolute; top: 24px; right: 24px; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #475569; transition: all 0.2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div id="wp-coming-soon-icon-container"
                style="width: 56px; height: 56px; background: #eff6ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <span style="display: inline-block; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px; border: 1px solid #fcd34d;">FASE BETA CERRADA</span>
                <h3
                    style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.02em; line-height: 1.2;">
                    <span id="wp-coming-soon-product-name">La Integración</span></h3>
            </div>
        </div>

        <p style="font-size: 1.05rem; color: #475569; line-height: 1.5; margin-bottom: 28px;">
            Estamos finalizando esta integración y daremos acceso prioritario a solo <strong>50 cuentas</strong> para garantizar el soporte en la fase beta.
        </p>

        <form id="integration-waitlist-form" style="display: flex; flex-direction: column; gap: 16px;">
            <?php if(!session('logged_in')): ?>
            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Tu Email de trabajo</label>
                <input type="email" id="waitlist-email" required placeholder="ejemplo@tuempresa.com" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 1rem; background: #f8fafc; outline: none; transition: border-color 0.2s;">
            </div>
            <?php else: ?>
                <input type="hidden" id="waitlist-email" value="<?= esc(session('user_email')) ?>">
            <?php endif; ?>

            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 8px;">¿Para qué caso de uso principal la necesitas?</label>
                <select id="waitlist-use-case" required style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 1rem; background: #f8fafc; outline: none; cursor: pointer; transition: border-color 0.2s;">
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="Enriquecer mi CRM / ERP">Enriquecer mi CRM o ERP</option>
                    <option value="Validar clientes (KYC / Onboarding)">Validar clientes (Onboarding / KYC)</option>
                    <option value="Prospección comercial automatizada">Prospección comercial automatizada</option>
                    <option value="Limpiar BBDD">Actualizar/Limpiar Bases de Datos</option>
                    <option value="Otro">Otro caso de uso</option>
                </select>
            </div>

            <button type="submit" id="waitlist-submit-btn"
                style="width: 100%; background: #2563eb; color: #fff; font-weight: 800; font-size: 1.1rem; padding: 18px; border-radius: 14px; border: none; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.25); margin-top: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;"
                onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-2px)'"
                onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)'">
                <span>Solicitar Acceso Beta</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
            <p style="font-size: 0.8rem; color: #94a3b8; text-align: center; margin: 0;">Al solicitar acceso nos ayudas a priorizar el desarrollo.</p>
        </form>

        <div id="waitlist-success-msg" style="display: none; text-align: center; padding: 16px 0;">
            <div style="width: 64px; height: 64px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h4 style="font-size: 1.4rem; font-weight: 900; color: #166534; margin-bottom: 8px;">¡Lista de espera confirmada!</h4>
            <p style="color: #15803d; font-size: 1.05rem;">Te hemos reservado un lugar en la cola. Te avisaremos en cuanto tu acceso esté desbloqueado.</p>
            <button onclick="document.getElementById('wp-coming-soon-modal').style.display = 'none'" style="margin-top: 24px; background: transparent; border: 2px solid #cbd5e1; color: #475569; font-weight: 700; padding: 10px 24px; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">Cerrar ventana</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctaButtons = document.querySelectorAll('.js-track-wp-cta');
        const modal = document.getElementById('wp-coming-soon-modal');

        if(modal && ctaButtons.length > 0) {
            ctaButtons.forEach(btn => {
                // Ensure we don't bind multiple times
                if(btn.dataset.wpBound) return;
                btn.dataset.wpBound = "true";

                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    let label = this.innerText.trim();
                    const strongTag = this.querySelector('strong');
                    if (strongTag) {
                        label = strongTag.innerText.trim();
                    } else {
                        // En móvil a veces está dentro de un span
                        const spanTag = this.querySelector('span');
                        if (spanTag) label = spanTag.innerText.trim();
                    }
                    
                    let sourcePage = 'app_nav_integration';
                    if (window.location.pathname.includes('plugin-wordpress')) {
                        sourcePage = 'marketing_integration';
                    } else if (window.location.pathname.includes('dashboard')) {
                        sourcePage = 'dashboard_integration';
                    } else if (window.location.pathname === '/' || window.location.pathname === '') {
                        sourcePage = 'home_integration';
                    } else {
                        // Capture the exact path for other internal pages (e.g. /billing -> billing_integration)
                        const pathParts = window.location.pathname.split('/').filter(p => p);
                        if (pathParts.length > 0) {
                            sourcePage = pathParts[pathParts.length - 1] + '_integration';
                        }
                    }

                    // Update modal content dynamically
                    const productNameEl = document.getElementById('wp-coming-soon-product-name');
                    if(productNameEl) {
                        productNameEl.innerText = label;
                    }

                    // Dynamically update the icon
                    const sourceSvg = this.querySelector('svg');
                    const iconContainer = document.getElementById('wp-coming-soon-icon-container');
                    if (sourceSvg && iconContainer) {
                        const clonedSvg = sourceSvg.cloneNode(true);
                        clonedSvg.setAttribute('width', '28');
                        clonedSvg.setAttribute('height', '28');
                        clonedSvg.setAttribute('stroke', '#2563eb');
                        clonedSvg.setAttribute('stroke-width', '2');
                        iconContainer.innerHTML = '';
                        iconContainer.appendChild(clonedSvg);
                    }

                    fetch('<?= site_url('tracking/radar-demo-event') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            event_type: 'click_cta_coming_soon',
                            source: sourcePage,
                            page: sourcePage,
                            cta_label: label,
                            url: this.getAttribute('href') || '#'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Marketing event tracked (Coming Soon):', label);
                        modal.style.display = 'flex';
                        // Reset form visibility if it was submitted before
                        const wForm = document.getElementById('integration-waitlist-form');
                        const sMsg = document.getElementById('waitlist-success-msg');
                        const sBtn = document.getElementById('waitlist-submit-btn');
                        if(wForm && sMsg) {
                            wForm.style.display = 'flex';
                            sMsg.style.display = 'none';
                            wForm.reset();
                            if(sBtn) {
                                sBtn.innerHTML = '<span>Solicitar Acceso Beta</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
                                sBtn.style.pointerEvents = 'auto';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error tracking marketing event:', err);
                        modal.style.display = 'flex';
                    });
                });
            });
        }

        // Waitlist Form Submission Track
        const waitlistForm = document.getElementById('integration-waitlist-form');
        if (waitlistForm) {
            waitlistForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const btn = document.getElementById('waitlist-submit-btn');
                btn.innerHTML = '<span style="opacity: 0.7;">Procesando...</span>';
                btn.style.pointerEvents = 'none';
                
                const productName = document.getElementById('wp-coming-soon-product-name').innerText;
                const email = document.getElementById('waitlist-email').value;
                const useCase = document.getElementById('waitlist-use-case').value;
                
                fetch('<?= site_url('tracking/radar-demo-event') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        event_type: 'join_integration_waitlist',
                        source: 'waitlist_form',
                        page: window.location.pathname,
                        cta_label: productName,
                        metadata: {
                            email: email,
                            use_case: useCase
                        }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    waitlistForm.style.display = 'none';
                    document.getElementById('waitlist-success-msg').style.display = 'block';
                })
                .catch(err => {
                    console.error('Error submitting waitlist:', err);
                    // Show success anyway to not block user experience on tracking error
                    waitlistForm.style.display = 'none';
                    document.getElementById('waitlist-success-msg').style.display = 'block';
                });
            });
        }
    });
</script>
