<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>

    <!-- Tu estilo base -->
    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/css/companies_map.css') ?>" />

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- MarkerCluster -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO APIEMPRESAS (check limpio, sin triángulo) -->
                <a href="<?=site_url() ?>">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <!-- Degradado de marca -->
                            <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                            <!-- Halo del bloque -->
                            <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                            </filter>
                            <!-- Sombra suave del check (no genera triángulos) -->
                            <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                            </filter>
                            <!-- Brillo muy leve arriba-izquierda -->
                            <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                                <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                            <!-- Aro exterior para definir borde en fondos muy claros -->
                            <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                                <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                            </linearGradient>
                        </defs>

                        <!-- Tarjeta con halo + brillo sutil -->
                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                            <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                        </g>

                        <!-- Check principal sin trazo oscuro debajo, con sombra de filtro -->
                        <path d="M18 33 L28 43 L46 22"
                              stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                              fill="none" filter="url(#ve-checkShadow)"/>
                    </svg>
                </a>


                <div class="brand-text">
                    <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación empresarial en segundos</span>
                </div>
            </div>


            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?=site_url() ?>billing">Planes y facturación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>usage">Consumo</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
            </nav>

            <div class="desktop-only">
                <?php if(!session('logged_in')){ ?>
                    <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">
                        <span>Iniciar sesión</span>
                    </a>
                <?php } else { ?>
                    <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout">
                        <span>Salir</span>
                    </a>
                <?php } ?>
            </div>



        </div>
    </header>

    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Mapa de empresas</h1>
                <p>Explora empresas por zona, filtra por CNAE/estado y exporta resultados.</p>
            </div>

            <div class="map-layout">
                <section class="map-card">
                    <div class="map-toolbar">
                        <div class="tool">
                            <label>CNAE</label>
                            <input id="f_cnae" class="input" placeholder="Ej: 6201 o 'Informática'">
                        </div>

                        <div class="tool">
                            <label>Estado</label>
                            <input id="f_estado" class="input" placeholder="Ej: ACTIVA">
                        </div>

                        <div class="tool tool-check">
                            <label class="check">
                                <input id="f_has_phone" type="checkbox">
                                <span>Solo con teléfono</span>
                            </label>
                        </div>

                        <div class="tool tool-check">
                            <label class="check">
                                <input id="f_only_geocoded" type="checkbox" checked>
                                <span>Solo geolocalizadas</span>
                            </label>
                        </div>

                        <div class="tool tool-actions">
                            <button id="btnRefresh" class="btn-small primary" type="button">Actualizar</button>
                            <button id="btnExport" class="btn-small" type="button">Export CSV</button>
                        </div>
                    </div>

                    <div id="map" class="map"></div>

                    <div class="map-foot">
                        <span id="statusText">Mueve el mapa y pulsa “Actualizar”.</span>
                        <span class="muted">Consejo: haz zoom para ver resultados más precisos.</span>
                    </div>
                </section>

                <aside class="side">
                    <section class="dash-card side-card">
                        <h2>Insights</h2>
                        <div class="insights">
                            <div class="kpi">
                                <span class="kpi-label">Empresas</span>
                                <span class="kpi-value" id="kpiCount">—</span>
                            </div>
                            <div class="kpi">
                                <span class="kpi-label">Con teléfono</span>
                                <span class="kpi-value" id="kpiPhone">—</span>
                            </div>
                        </div>

                        <div class="mini-block">
                            <div class="mini-title">Top CNAE</div>
                            <div id="topCnae" class="mini-list">—</div>
                        </div>
                    </section>

                    <section class="dash-card side-card">
                        <h2>Resultados</h2>
                        <div id="results" class="results"></div>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- MarkerCluster -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    (function(){
        const map = L.map('map', { preferCanvas: true }).setView([40.4168, -3.7038], 6); // España

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const cluster = L.markerClusterGroup({
            chunkedLoading: true,
            maxClusterRadius: 50
        });

        map.addLayer(cluster);

        const el = (id) => document.getElementById(id);

        const statusText = el('statusText');
        const resultsEl = el('results');
        const kpiCount = el('kpiCount');
        const kpiPhone = el('kpiPhone');
        const topCnae = el('topCnae');

        const fCnae = el('f_cnae');
        const fEstado = el('f_estado');
        const fHasPhone = el('f_has_phone');
        const fOnlyGeocoded = el('f_only_geocoded');

        const btnRefresh = el('btnRefresh');
        const btnExport = el('btnExport');

        // Evitar spamear: solo actualiza manual o con debounced si quisieras
        async function loadCompanies(){
            const b = map.getBounds();
            const params = new URLSearchParams({
                north: b.getNorth(),
                south: b.getSouth(),
                east:  b.getEast(),
                west:  b.getWest(),
                limit: 2000,
                cnae: fCnae.value.trim(),
                estado: fEstado.value.trim(),
                has_phone: fHasPhone.checked ? 1 : 0,
                only_geocoded: fOnlyGeocoded.checked ? 1 : 0
            });

            statusText.textContent = 'Cargando empresas…';
            btnRefresh.disabled = true;

            try{
                const res = await fetch('<?= site_url('api/map/companies') ?>?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();

                if(!res.ok){
                    statusText.textContent = 'Error: ' + (json.error || 'No se pudo cargar');
                    btnRefresh.disabled = false;
                    return;
                }

                // limpiar markers
                cluster.clearLayers();

                // pintar
                const rows = json.data || [];
                for(const r of rows){
                    if(!r.lat || !r.lng) continue;

                    const m = L.marker([parseFloat(r.lat), parseFloat(r.lng)]);
                    m.bindPopup(popupHtml(r));
                    cluster.addLayer(m);
                }

                // listado lateral
                renderResults(rows);

                // insights
                const meta = json.meta || {};
                kpiCount.textContent = meta.count ?? rows.length;
                kpiPhone.textContent = meta.with_phone ?? '—';
                renderTopCnae(meta.top_cnae || {});

                statusText.textContent = `Listo: ${rows.length} empresas (límite ${meta.limit || '—'}).`;
            }catch(e){
                statusText.textContent = 'Error de red al cargar empresas.';
            }finally{
                btnRefresh.disabled = false;
            }
        }

        function popupHtml(r){
            const safe = (s) => (s ?? '').toString()
                .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');

            const phone = [r.phone, r.phone_mobile].filter(Boolean).join(' · ');
            return `
            <div class="popup">
                <div class="p-title">${safe(r.company_name || 'Empresa')}</div>
                <div class="p-row"><strong>CIF:</strong> ${safe(r.cif || '—')}</div>
                <div class="p-row"><strong>CNAE:</strong> ${safe(r.cnae_code || '—')} ${safe(r.cnae_label || '')}</div>
                <div class="p-row"><strong>Estado:</strong> ${safe(r.estado || '—')}</div>
                <div class="p-row"><strong>Dirección:</strong> ${safe(r.address || '—')}</div>
                <div class="p-row"><strong>Tel:</strong> ${safe(phone || '—')}</div>
            </div>
        `;
        }

        function renderResults(rows){
            // muestra top N en listado para no saturar
            const MAX = 40;
            const subset = rows.slice(0, MAX);

            if(subset.length === 0){
                resultsEl.innerHTML = `<div class="empty">No hay resultados en esta zona con los filtros actuales.</div>`;
                return;
            }

            resultsEl.innerHTML = subset.map(r => {
                const name = escapeHtml(r.company_name || 'Empresa');
                const cnae = escapeHtml(r.cnae_code || '—');
                const estado = escapeHtml(r.estado || '—');
                const addr = escapeHtml(r.address || '—');
                const phone = escapeHtml(([r.phone, r.phone_mobile].filter(Boolean).join(' · ')) || '—');

                return `
                <div class="result">
                    <div class="r-top">
                        <div class="r-name">${name}</div>
                        <div class="r-pill">${estado}</div>
                    </div>
                    <div class="r-meta">
                        <span><strong>CNAE:</strong> ${cnae}</span>
                        <span class="sep">•</span>
                        <span><strong>Tel:</strong> ${phone}</span>
                    </div>
                    <div class="r-addr">${addr}</div>
                </div>
            `;
            }).join('');

            if(rows.length > MAX){
                resultsEl.innerHTML += `<div class="more">Mostrando ${MAX} de ${rows.length}. Usa export para descargar más.</div>`;
            }
        }

        function renderTopCnae(obj){
            const entries = Object.entries(obj || {});
            if(entries.length === 0){
                topCnae.textContent = '—';
                return;
            }

            topCnae.innerHTML = entries.map(([k,v]) => {
                return `<div class="mini-item"><span>${escapeHtml(k)}</span><strong>${v}</strong></div>`;
            }).join('');
        }

        function escapeHtml(s){
            return (s ?? '').toString()
                .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
        }

        btnRefresh.addEventListener('click', loadCompanies);

        btnExport.addEventListener('click', () => {
            const b = map.getBounds();
            const params = new URLSearchParams({
                north: b.getNorth(),
                south: b.getSouth(),
                east:  b.getEast(),
                west:  b.getWest(),
                limit: 20000,
                cnae: fCnae.value.trim(),
                estado: fEstado.value.trim(),
                has_phone: fHasPhone.checked ? 1 : 0,
                only_geocoded: fOnlyGeocoded.checked ? 1 : 0
            });

            window.location.href = '<?= site_url('api/map/export') ?>?' + params.toString();
        });

        // Carga inicial
        loadCompanies();
    })();
</script>

</body>
</html>

