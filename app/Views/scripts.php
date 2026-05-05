<?php helper('api'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // We use the 'search' route which is now configured for both GET and POST
        const SEARCH_ENDPOINT = '<?= site_url("search") ?>';
        const RADAR_BASE_URL = '<?= site_url("leads-empresas-nuevas") ?>';
        const COMPANY_BASE_URL = '<?= site_url() ?>'.replace(/\/$/, '') + '/';

        $('#btnBuscar').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const query = $('#q').val().trim();
            
            if (!query) return;

            $btn.prop('disabled', true).text('Buscando...');
            $('#resultado').html('<div class="card"><p class="muted">Buscando datos oficiales...</p></div>');
            $('#resultado_container').show();

            $.ajax({
                url: SEARCH_ENDPOINT,
                method: 'POST', // Switched to POST for higher reliability in local environments
                data: { 
                    q: query,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Search response:', data);
                    renderResult(data, COMPANY_BASE_URL, RADAR_BASE_URL);
                },
                error: function(xhr, status, error) {
                    console.error('Ajax Error Detail:', {
                        status: xhr.status,
                        error: error,
                        response: xhr.responseText,
                        url: SEARCH_ENDPOINT
                    });
                    
                    let msg = 'Error al consultar la API. Inténtalo de nuevo.';
                    if (xhr.status === 404) {
                        msg = 'No se ha encontrado ninguna empresa con ese nombre o CIF.';
                    } else if (xhr.status === 429) {
                        msg = 'Demasiadas solicitudes. Por favor espera un minuto.';
                    }

                    $('#resultado').html(`<div class="card" style="padding: 30px; border-radius: 16px; border: 1px solid #fee2e2; background: #fff5f5;"><p style="color:#dc2626; font-weight:700; margin:0;">${msg}</p></div>`);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Validar ahora');
                }
            });
        });

        let searchCount = 0;

        function renderResult(data, baseUrl, radarUrl) {
            const $resultado = $('#resultado');
            const $container = $('#resultado_container');

            if (!data.success) {
                $resultado.html(`
                    <div class="search-result-card" style="text-align: center; padding: 48px;">
                        <p style="color: #64748b; font-size: 1.1rem;">${data.message || 'No se han encontrado resultados.'}</p>
                    </div>`);
                $container.show();
                return;
            }

            searchCount++;
            const company = data.data;

            // Prepare masked JSON for display
            const maskedJson = {
                success: true,
                data: {
                    name: company.name,
                    cif: company.cif || company.nif,
                    status: company.status,
                    address: company.address ? company.address : "ACTUALIZA A PRO PARA VER LA DIRECCCION",
                    founded: company.founded,
                    capital: company.capital || "Consultar",
                    activity: company.cnae_label
                }
            };

            const jsonHtml = formatJsonForIde(maskedJson);

            const province = company.province || company.provincia || 'España';
            const status = (company.status || '').toLowerCase() === 'activa' ? 'activa' : 'inactiva';

            // 1. DETECTAR ANTIGÜEDAD
            let año_constitucion = null;
            if (company.founded) {
                const match = company.founded.toString().match(/\d{4}/);
                if (match) año_constitucion = parseInt(match[0]);
            }
            const currentYear = new Date().getFullYear();
            const años = año_constitucion ? currentYear - año_constitucion : null;

            // 2. BLOQUE PRINCIPAL DINÁMICO
            let radarTitle = "";
            if (años === null) {
                radarTitle = "Accede a nuevas oportunidades comerciales";
            } else if (años <= 1) {
                radarTitle = "Empresa de reciente creación";
            } else if (años <= 5) {
                radarTitle = "Empresa en fase de actividad";
            } else {
                radarTitle = "Empresa consolidada";
            }

            $resultado.html(`
<div class="search-result-card reveal" style="background: #ffffff; border-radius: 24px; padding: 40px; box-shadow: 0 20px 50px -10px rgba(0,0,0,0.08); border: 1px solid #f1f5f9;">
  
  <div class="result-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
    <div style="display: flex; gap: 20px; align-items: center;">
      <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #2563eb, #10b981); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; font-weight: 800; box-shadow: 0 8px 16px rgba(37,99,235,0.2);">
        ${(company.name || 'N').charAt(0).toUpperCase()}
      </div>
      <div>
        <h2 style="margin: 0; font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">${company.name || 'N/A'}</h2>
        <div style="display: flex; gap: 12px; margin-top: 6px; align-items: center;">
          <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 8px; font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem;">${company.cif || company.nif || 'Sin CIF'}</span>
          <span style="font-size: 0.9rem; color: #94a3b8;">•</span>
          <span style="font-size: 0.9rem; color: #64748b; font-weight: 600;">${province}</span>
        </div>
      </div>
    </div>
    <span class="status-badge ${status}" style="padding: 8px 16px; border-radius: 100px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; ${status === 'activa' ? 'background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7;' : 'background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2;'}">
      ${company.status || 'N/A'}
    </span>
  </div>

  <div class="result-info-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 24px; padding: 24px; background: #f8fafc; border-radius: 20px; border: 1px solid #f1f5f9; text-align: left;">
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Actividad</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700; line-height: 1.4;">${company.cnae_label || 'Sin sector'}</span>
    </div>
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Constitución</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;">${company.founded || 'N/A'}</span>
    </div>
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Capital Social</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;">${company.capital || 'Consultar'}</span>
    </div>
  </div>

  <div class="address-box" style="margin-bottom: 32px; padding: 18px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; display: flex; gap: 16px; align-items: center; text-align: left;">
    <div style="background: #f1f5f9; padding: 10px; border-radius: 12px; color: #64748b;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
    </div>
    <div>
        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">Dirección Fiscal Oficial</span>
        <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;">${company.address || 'Consultar en ficha completa'}</span>
    </div>
  </div>

  <div class="result-actions" style="display: flex; gap: 16px; margin-bottom: 32px;">
    <a href="${baseUrl}${company.cif || company.nif}" class="btn" style="text-decoration:none; padding: 20px 32px; font-weight: 800; flex-grow: 1; text-align: center; background: #ffffff; color: #475569; border: 2px solid #e2e8f0; border-radius: 16px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--ae-blue)'; this.style.color='var(--ae-blue)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'">Ver ficha detallada completa</a>
    <a href="javascript:void(0)" onclick="$('#json-ide-section').slideToggle();" class="btn secondary" style="text-decoration:none; padding: 20px 32px; font-weight: 900; text-align: center; border-radius: 16px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15); background: linear-gradient(135deg, #2563eb, #10b981); color: white; display: flex; align-items: center; justify-content: center; gap: 10px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
        Ver respuesta JSON
    </a>
  </div>

  <div id="json-ide-section" style="display:none; margin-bottom: 32px;">
    <div class="json-ide-container">
        <div class="json-ide-header">
            <div class="json-ide-dots">
                <div class="json-ide-dot red"></div>
                <div class="json-ide-dot yellow"></div>
                <div class="json-ide-dot green"></div>
            </div>
            <div class="json-ide-title">response.json</div>
            <button class="json-ide-btn-copy" onclick="triggerAhaMoment()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copiar JSON
            </button>
        </div>
        <div class="json-ide-content">
            <pre style="margin:0;">${jsonHtml}</pre>
        </div>
        <div class="json-blur-overlay">
            <div class="json-upgrade-hint" onclick="triggerAhaMoment()">
                🚀 Obtener acceso completo a la API
            </div>
        </div>
    </div>
  </div>

  <!-- Bridge to Radar Dinámico -->
  <div class="radar-bridge" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border: 1px solid #dcfce7; border-radius: 24px; padding: 32px; display: flex; gap: 28px; align-items: center; margin-bottom: 24px; position: relative; overflow: hidden; text-align: left;">
    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);"></div>
    
    <div style="width: 56px; height: 56px; background: #ffffff; color: #16a34a; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4M12 16V8"/></svg>
    </div>
    
    <div style="flex-grow: 1;">
      <div class="radar-badge" style="background: #ffffff; border: 1px solid #dcfce7; display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 0.75rem; font-weight: 800; color: #16a34a; margin-bottom: 12px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
        +1.400 empresas nuevas esta semana
      </div>
      <h4 style="margin: 0 0 8px 0; font-size: 1.25rem; color: #0f172a; font-weight: 900;">${radarTitle}</h4>
      <p style="margin: 0; font-size: 1rem; color: #475569; line-height: 1.6; max-width: 480px;">Además de consultar esta empresa, puedes detectar nuevas sociedades con potencial de negocio y trabajarlas antes que la competencia.</p>
    </div>
    
    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; z-index: 1;">
        <a href="${radarUrl}" class="btn-radar" style="padding: 12px 24px; font-size: 0.95rem; border-radius: 14px; background: #ecfdf5; color: #059669 !important; border: 1px solid #10b981; box-shadow: none; margin: 0; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
            Ver empresas nuevas hoy
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600; text-align: center; opacity: 0.8;">Oportunidades limitadas en el tiempo</span>
    </div>
  </div>

  <div style="text-align: center; margin-bottom: 20px;">
    <span class="radar-context-text" style="font-size: 0.95rem; font-weight: 500;">“La detección temprana de empresas puede marcar la diferencia en procesos comerciales.”</span>
  </div>
</div>`);

            $container.show();
        }

        function formatJsonForIde(obj) {
            let json = JSON.stringify(obj, null, 4);
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }

        window.triggerAhaMoment = function() {
            Swal.fire({
                html: `
                    <div style="padding: 20px 10px; text-align: center; position: relative;">
                        <!-- Premium API Icon -->
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #0f172a, #2563eb); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2); transform: rotate(-5deg);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="4 17 10 11 4 5"></polyline>
                                <line x1="12" y1="19" x2="20" y2="19"></line>
                            </svg>
                        </div>

                        <h2 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.03em;">Tu API Key está lista</h2>
                        <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 32px; line-height: 1.6;">Introduce tu email para activar tu acceso gratuito y ver todos los datos sin límites.</p>

                        <form action="<?= site_url('register/quick_store') ?>" method="POST" id="quick-reg-form" style="display: grid; gap: 16px; text-align: left;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect" value="dashboard">
                            
                            <div class="input-group-premium">
                                <label style="display: block; font-size: 0.85rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; margin-left: 4px;">Correo Electrónico Profesional</label>
                                <input type="email" name="email" required placeholder="nombre@empresa.com" 
                                    style="width: 100%; padding: 18px 24px; border-radius: 16px; border: 2px solid #f1f5f9; background: #f8fafc; font-size: 1.1rem; font-weight: 600; color: #1e293b; transition: all 0.3s;"
                                    onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff'; this.style.boxShadow='0 0 0 4px rgba(37, 99, 235, 0.1)';"
                                    onblur="this.style.borderColor='#f1f5f9'; this.style.background='#f8fafc'; this.style.boxShadow='none';">
                            </div>

                            <button type="submit" class="btn-ae js-loading-btn" style="background: #2563eb; color: white; padding: 20px; font-size: 1.15rem; font-weight: 800; border-radius: 16px; border: none; box-shadow: 0 15px 30px rgba(37, 99, 235, 0.25); cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                                Activar mi acceso gratis
                            </button>
                        </form>

                        <div class="auth-separator" style="margin: 20px 0; display: flex; align-items: center; text-align: center; color: #94a3b8; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                            <span style="flex: 1; border-bottom: 1px solid #f1f5f9; margin-right: 15px;"></span>
                            O continúa con un clic
                            <span style="flex: 1; border-bottom: 1px solid #f1f5f9; margin-left: 15px;"></span>
                        </div>

                        <a href="<?= site_url('auth/google') ?>" class="btn-google-modal">
                            <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google">
                            Entrar con Google
                        </a>

                        <div style="margin-top: 24px; display: flex; align-items: center; justify-content: center; gap: 12px; opacity: 0.7;">
                            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">✨ Sin tarjeta · Plan gratuito de <?= get_free_plan_limit() ?> consultas/mes</span>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    popup: 'premium-swal-popup-v2'
                },
                background: '#ffffff',
                width: '540px',
                padding: '0'
            });
        };

        // Modal triggers
        $(document).on('click', '[data-modal-target]', function(e) {
            e.preventDefault();
            const targetId = $(this).attr('data-modal-target');
            $('#' + targetId).addClass('active');
        });

        $(document).on('click', '[data-close-modal], .modal-overlay', function(e) {
            if (e.target !== this && !$(e.target).closest('[data-close-modal]').length) return;
            e.preventDefault();
            $(this).closest('.modal-overlay').removeClass('active');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal-overlay.active').removeClass('active');
            }
        });

        // Global Loading Button Handler
        $(document).on('click', '.js-loading-btn', function(e) {
            const $btn = $(this);
            if ($btn.hasClass('btn-loading')) {
                e.preventDefault();
                return;
            }
            if ($btn.is('a') || ($btn.is('button') && $btn.attr('type') !== 'submit')) {
                showLoadingState($btn[0]);
            }
        });

        $(document).on('submit', 'form', function(e) {
            const $btn = $(this).find('.js-loading-btn[type="submit"]');
            if ($btn.length) {
                showLoadingState($btn[0]);
            }
        });

        function showLoadingState(el) {
            const $el = $(el);
            if ($el.hasClass('btn-loading')) return;
            
            $el.addClass('btn-loading');
            
            if ($el.is('button')) {
                setTimeout(() => { $el.prop('disabled', true); }, 10);
            }

            if (!$el.find('.btn-spinner').length) {
                $el.prepend('<span class="btn-spinner"></span>');
            }
        }
    });
</script>
