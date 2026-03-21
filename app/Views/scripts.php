<script>
    document.addEventListener('DOMContentLoaded', () => {
        const BASE_URL = '<?= site_url() ?>';

        const btnBuscar = document.getElementById('btnBuscar');
        const inputQ = document.getElementById('q');
        const searchResultContainer = document.getElementById('resultado');

        if (btnBuscar) {
            btnBuscar.addEventListener('click', () => {
                const query = inputQ.value.trim();
                if (!query) return;

                btnBuscar.disabled = true;
                btnBuscar.innerText = 'Buscando...';
                searchResultContainer.innerHTML = '<div class="card"><p class="muted">Buscando datos oficiales...</p></div>';

                const formData = new FormData();
                formData.append('q', query);

                fetch(`${BASE_URL}search_company`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    renderResult(data);
                })
                .catch(err => {
                    searchResultContainer.innerHTML = '<div class="card"><p style="color:red">Error al consultar la API. Inténtalo de nuevo.</p></div>';
                })
                .finally(() => {
                    btnBuscar.disabled = false;
                    btnBuscar.innerText = 'Validar CIF / Buscar empresa';
                });
            });
        }

        function renderResult(data) {
            if (!data.success) {
                searchResultContainer.innerHTML = `<div class="card"><p class="muted">${data.message || 'No se han encontrado resultados.'}</p></div>`;
                return;
            }

            const company = data.data;
            const jsonPretty = JSON.stringify(data, null, 2);

            searchResultContainer.innerHTML = `
<article class="card company-card" style="margin-top:20px; animation: fadeIn 0.4s ease-out;">
  <div class="card-head" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div style="flex:1">
      <h2 style="margin:0; font-size:1.4rem; color:var(--primary);">${company.name || 'N/A'}</h2>
      <div class="meta" style="margin-top:4px">
        <span class="pill mini-pill">${company.cif || company.nif || 'Sin CIF'}</span>
      </div>
    </div>
    <span class="pill estado--${(company.status || '').toLowerCase() === 'activa' ? 'activa' : 'inactiva'}" style="margin-top:4px;">${company.status || 'N/A'}</span>
  </div>

  <section style="margin-top:16px;">
    <dl class="grid-2">
      <div>
        <dt>Sector (CNAE)</dt>
        <dd>${company.cnae || 'N/A'} - ${company.cnae_label || 'Sin sector'}</dd>
      </div>
      <div>
        <dt>Provincia</dt>
        <dd>${company.province || company.provincia || 'N/A'}</dd>
      </div>
      <div>
        <dt>Fecha de constitución</dt>
        <dd>${company.founded || 'N/A'}</dd>
      </div>
      <div style="grid-column: span 2; margin-top:8px;">
        <dt>Objeto social</dt>
        <dd style="font-weight:400; line-height:1.4; color:#334155;">${company.corporate_purpose || 'N/A'}</dd>
      </div>
    </dl>
  </section>

  <div class="company-card__footer">
    <button type="button" class="btn-json-api">Ver JSON de la API</button>
    <a href="${BASE_URL}${company.cif || company.nif}" class="btn" style="text-decoration:none;">Ver ficha completa</a>
  </div>

  <!-- Premium Lead CTA -->
  <div class="premium-cta-card" style="margin-top:24px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(33, 82, 255, 0.05); text-align: left;">
    <!-- Decorative Glow -->
    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(33, 82, 255, 0.1) 0%, transparent 70%);"></div>
    
    <div style="display: flex; gap: 24px; align-items: center; position: relative; z-index: 1;">
      <div style="background: #ffffff; color: #2152ff; width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 16px rgba(33, 82, 255, 0.1); border: 1px solid #f1f5f9;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: pulse_radar_final 2s infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
      </div>
      <div style="flex: 1;">
        <h3 style="margin: 0 0 8px; font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1.2;">¿Vendes a otras empresas?</h3>
        <p style="margin: 0; color: #64748b; font-size: 1rem; line-height: 1.5;">Monitorizamos el BORME cada día para entregarte leads cualificados de <strong>${company.province || company.provincia || 'España'}</strong> antes que nadie.</p>
      </div>
    </div>
    
    <div style="margin-top: 24px; display: flex; align-items: center; justify-content: center;">
      <a href="${BASE_URL}leads-empresas-nuevas" class="btn" style="background: linear-gradient(90deg, #2152ff, #12b48a); color: white; border: none !important; font-weight: 700; padding: 16px 36px; border-radius: 14px; font-size: 1.1rem; box-shadow: 0 10px 25px rgba(33, 82, 255, 0.2); text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; display: inline-flex; align-items: center; gap: 10px;"
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 28px rgba(33, 82, 255, 0.3)'"
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(33, 82, 255, 0.2)'">
        Descubrir oportunidades en Radar PRO →
      </a>
    </div>
  </div>

  <pre class="company-card__json is-hidden"><code>${jsonPretty}</code></pre>
</article>`;
            
            bindJsonButtons(searchResultContainer);
        }

        function bindJsonButtons(container) {
            const buttons = container.querySelectorAll('.btn-json-api');
            buttons.forEach((b) => {
                if (b.dataset.bound === '1') return;
                b.dataset.bound = '1';

                b.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const card = b.closest('.company-card');
                    const pre = card?.querySelector('.company-card__json');
                    if (!pre) return;

                    const nowHidden = pre.classList.toggle('is-hidden');
                    b.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
                });
            });
        }

        // Modal triggers
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-target]');
            if (trigger) {
                e.preventDefault();
                const targetId = trigger.getAttribute('data-modal-target');
                const modal = document.getElementById(targetId);
                if (modal) modal.classList.add('active');
                return;
            }

            const closer = e.target.closest('[data-close-modal]');
            if (closer) {
                e.preventDefault();
                const modal = closer.closest('.modal-overlay');
                if (modal) modal.classList.remove('active');
                return;
            }

            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal-overlay.active');
                if (activeModal) activeModal.classList.remove('active');
            }
        });

        // Global Loading Button Handler
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-loading-btn');
            if (!btn) return;

            // If it's a link and it's already loading, prevent default
            if (btn.tagName === 'A' && btn.classList.contains('btn-loading')) {
                e.preventDefault();
                return;
            }

            // If it's not a submit button (handled below), apply loading state immediately
            if (btn.tagName === 'A' || (btn.tagName === 'BUTTON' && btn.type !== 'submit')) {
                showLoadingState(btn);
            }
        });

        // Handle form submissions for loading buttons
        document.addEventListener('submit', (e) => {
            const btn = e.target.querySelector('.js-loading-btn[type="submit"]');
            if (btn) {
                showLoadingState(btn);
            }
        });

        function showLoadingState(el) {
            if (el.classList.contains('btn-loading')) return;
            
            el.classList.add('btn-loading');
            
            // For buttons, disable them
            if (el.tagName === 'BUTTON') {
                el.disabled = true;
            }

            // Add spinner if not present
            if (!el.querySelector('.btn-spinner')) {
                const spinner = document.createElement('span');
                spinner.className = 'btn-spinner';
                el.prepend(spinner);
            }

            // Optional: change text if it's a "Confirmar y Pagar" type
            if (el.textContent.includes('Pagar') || el.textContent.includes('Descargar')) {
                // Keep the spinner and just slightly change text or keep it
                // For now, just adding the spinner is enough as requested.
            }
        }
    });
</script>
