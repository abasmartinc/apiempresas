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
            const jsonOutput = document.getElementById('json-output');
            const resultContainer = document.getElementById('resultado_container');

            if (!data.success) {
                searchResultContainer.innerHTML = `
                    <div class="search-result-card" style="text-align: center; padding: 48px;">
                        <p style="color: #64748b; font-size: 1.1rem;">${data.message || 'No se han encontrado resultados.'}</p>
                    </div>`;
                if (jsonOutput) jsonOutput.textContent = JSON.stringify(data, null, 2);
                if (resultContainer) resultContainer.style.display = 'block';
                return;
            }

            const company = data.data;
            const jsonPretty = JSON.stringify(data, null, 2);
            if (jsonOutput) jsonOutput.textContent = jsonPretty;

            const province = company.province || company.provincia || 'España';
            const status = (company.status || '').toLowerCase() === 'activa' ? 'activa' : 'inactiva';

            searchResultContainer.innerHTML = `
<div class="search-result-card">
  <div class="result-header">
    <div class="result-title-group">
      <h2 class="result-company-name">${company.name || 'N/A'}</h2>
      <span class="result-cif-pill">${company.cif || company.nif || 'Sin CIF'}</span>
    </div>
    <span class="status-badge ${status}">${company.status || 'N/A'}</span>
  </div>

  <div class="result-info-grid">
    <div class="info-item">
      <span class="info-label">Sector (CNAE)</span>
      <span class="info-value bold">${company.cnae || 'N/A'} - ${company.cnae_label || 'Sin sector'}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Provincia</span>
      <span class="info-value">${province}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Fecha de constitución</span>
      <span class="info-value">${company.founded || 'N/A'}</span>
    </div>
  </div>

  <div class="info-item" style="margin-bottom: 32px;">
    <span class="info-label">Objeto social</span>
    <span class="info-value" style="font-size: 0.9rem; font-weight: 400;">${company.corporate_purpose || 'N/A'}</span>
  </div>

  <!-- Radar PRO Integration (Integrated as Insight) -->
  <div class="radar-insight-nudge">
    <div class="radar-insight-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
    </div>
    <div class="radar-insight-content">
      <h4>Oportunidad comercial detectada</h4>
      <p>Monitorizamos el BORME para entregarte leads de <strong>${province}</strong> antes que nadie. ¿Quieres ver quién más ha nacido hoy?</p>
    </div>
    <a href="${BASE_URL}leads-empresas-nuevas" class="btn-radar-insight">Descubrir en Radar PRO →</a>
  </div>

  <div class="result-actions">
    <a href="${BASE_URL}${company.cif || company.nif}" class="btn" style="text-decoration:none; padding: 12px 24px;">Ver ficha completa</a>
  </div>
</div>`;
            
            if (resultContainer) resultContainer.style.display = 'block';
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
