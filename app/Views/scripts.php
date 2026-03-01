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

  <!-- Lead Gen Section -->
  <div class="lead-form-container">
    <h4 class="lead-form-title">¿Trabajas con empresas?</h4>
    <p class="lead-form-subtitle">Recibe cada semana las nuevas empresas creadas en tu provincia.</p>
    <form class="lead-form" onsubmit="handleLeadSubmit(event, this)">
      <input type="email" name="email" class="input" placeholder="Tu correo electrónico" required>
      <input type="text" name="province" class="input" placeholder="Provincia" value="${company.province || company.provincia || ''}">
      <input type="hidden" name="source" value="home_search">
      <button type="submit" class="btn secondary" style="padding: 12px 24px;">Recibir empresas nuevas</button>
    </form>
  </div>
  <a href="${BASE_URL}empresas-nuevas" class="secondary-radar-link" style="display: block; text-align: center; margin-top: 15px;">Ver cómo funciona Radar →</a>

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

        // Lead Submission Handler
        window.handleLeadSubmit = function(event, form) {
            event.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            
            btn.disabled = true;
            btn.innerText = 'Enviando...';

            const formData = new FormData(form);

            fetch(`${BASE_URL}leads/subscribe`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || (data.status >= 200 && data.status < 300)) {
                    Swal.fire({
                        title: '¡Registro completado!',
                        text: data.message || 'Pronto empezarás a recibir las nuevas empresas.',
                        icon: 'success',
                        confirmButtonText: 'Genial',
                        customClass: {
                            popup: 've-swal',
                            confirmButton: 'btn ve-swal-confirm'
                        },
                        buttonsStyling: false
                    });
                    form.reset();
                } else {
                    throw new Error(data.messages?.error || 'Error desconocido');
                }
            })
            .catch(error => {
                console.error('Lead submission error:', error);
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Hubo un error al procesar tu solicitud.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
        };

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

            const closer = e.target.closest('[data-modal-close]');
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
    });
</script>
