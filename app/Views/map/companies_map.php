<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>

    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/css/companies_map.css') ?>" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url() ?>">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                            <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                            </filter>
                            <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                            </filter>
                            <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                                <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                            <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                                <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                            </linearGradient>
                        </defs>

                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                            <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                        </g>

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
                <h3 style="margin-bottom: 0px;">Mapa inteligente de empresas</h3>
                <p style="margin-top: 5px;">Selecciona provincia/sector, acota la zona en el mapa y exporta un listado operativo.</p>
            </div>

            <div class="map2-layout">
                <!-- LEFT: Filters -->
                <aside class="filters">
                    <section class="dash-card">
                        <div class="field">
                            <label>Provincia</label>
                            <select id="f_province" class="input">
                                <option value="">— Selecciona —</option>
                            </select>
                            <div class="hint">Recomendado para búsquedas vendibles y exportables.</div>
                        </div>

                        <div class="field">
                            <label>Municipio (opcional)</label>
                            <select id="f_municipality" class="input" disabled>
                                <option value="">— Selecciona provincia antes —</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Sector (CNAE) – Sección</label>
                            <select id="c_section" class="input">
                                <option value="">— Todas —</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Grupo</label>
                            <select id="c_group" class="input" disabled>
                                <option value="">— Selecciona sección —</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Clase</label>
                            <select id="c_class" class="input" disabled>
                                <option value="">— Selecciona grupo —</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Subclase</label>
                            <select id="c_subclass" class="input" disabled>
                                <option value="">— Selecciona clase —</option>
                            </select>
                            <div class="hint">La subclase filtra por prefijo CNAE (más preciso).</div>
                        </div>

                        <div class="field">
                            <label>Estado</label>
                            <input id="f_estado" class="input" placeholder="Ej: ACTIVA">
                        </div>

                        <div class="checks">
                            <!-- NUEVO: Limitar bbox -->
                            <label class="check">
                                <input id="f_use_bbox" type="checkbox" checked>
                                <span>Limitar al área visible</span>
                            </label>

                            <label class="check">
                                <input id="f_has_phone" type="checkbox">
                                <span>Solo con teléfono</span>
                            </label>

                            <label class="check">
                                <input id="f_only_geocoded" type="checkbox" checked>
                                <span>Solo geolocalizadas</span>
                            </label>
                        </div>

                        <div class="actions">
                            <button id="btnSearch" class="btn-small primary" type="button">
                                Buscar
                            </button>
                            <button id="btnExport" class="btn-small" type="button" disabled>
                                Export CSV
                            </button>
                        </div>

                        <div class="note">
                            Consejo: desmarca “Limitar al área visible” si quieres abarcar toda la provincia.
                        </div>
                    </section>

                    <section class="dash-card">
                        <h2>Insights</h2>
                        <div class="kpis">
                            <div class="kpi">
                                <span class="kpi-label">Empresas</span>
                                <span class="kpi-value" id="k_count">—</span>
                            </div>
                            <div class="kpi">
                                <span class="kpi-label">Con teléfono</span>
                                <span class="kpi-value" id="k_phone">—</span>
                            </div>
                        </div>

                        <div class="mini-block">
                            <div class="mini-title">Top CNAE en el área</div>
                            <div id="topCnae" class="mini-list">—</div>
                        </div>
                    </section>
                </aside>

                <!-- RIGHT: Map + List -->
                <section class="map-area">
                    <div class="map-card">
                        <div class="map-head">
                            <div class="map-title">Área de búsqueda</div>
                            <div class="map-status" id="statusText">Configura filtros y pulsa “Buscar”.</div>
                        </div>

                        <div class="map-wrap">
                            <div id="map" class="map"></div>

                            <div id="emptyOverlay" class="empty-overlay">
                                <div class="eo-card">
                                    <div class="eo-pill">Mapa inteligente</div>
                                    <div class="eo-title">Empieza acotando la zona y el sector</div>
                                    <div class="eo-text">
                                        Selecciona una provincia (y si quieres, un CNAE) y luego pulsa <strong>Buscar</strong>.
                                        Esto evita ruido, mejora rendimiento y genera listados exportables.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="map-foot">
                            <span>Se muestran hasta <strong>5.000</strong> puntos por búsqueda (clusterizados).</span>
                            <span class="muted">Exportación: hasta <strong>50.000</strong> filas (según filtros).</span>
                        </div>
                    </div>

                    <div class="dash-card list-card">
                        <h2>Resultados</h2>
                        <div id="results" class="results"></div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    (function () {
        const el = (id) => document.getElementById(id);

        const fProvince = el('f_province');
        const fMunicipality = el('f_municipality');

        const cSection = el('c_section');
        const cGroup = el('c_group');
        const cClass = el('c_class');
        const cSubclass = el('c_subclass');

        const fEstado = el('f_estado');
        const fHasPhone = el('f_has_phone');
        const fOnlyGeocoded = el('f_only_geocoded');
        const fUseBbox = el('f_use_bbox');

        const btnSearch = el('btnSearch');
        const btnExport = el('btnExport');

        const statusText = el('statusText');
        const emptyOverlay = el('emptyOverlay');
        const resultsEl = el('results');

        const kCount = el('k_count');
        const kPhone = el('k_phone');
        const topCnae = el('topCnae');

        // ---------- Map init ----------
        const map = L.map('map', { preferCanvas: true }).setView([40.4168, -3.7038], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const cluster = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 55 });
        map.addLayer(cluster);

        // ---------- Helpers ----------
        function escapeHtml(s) {
            return (s ?? '').toString()
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }
        function escapeAttr(s){ return escapeHtml(s).replaceAll('"', '&quot;'); }

        function selectedDataName(selectEl) {
            return selectEl?.selectedOptions?.[0]?.dataset?.name || '';
        }

        function selectedDataCode(selectEl) {
            return selectEl?.selectedOptions?.[0]?.dataset?.code || '';
        }

        function setDisabledSelect(selectEl, placeholder) {
            selectEl.disabled = true;
            selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        }

        // Extrae un prefijo numérico 2-4 dígitos de:
        //  - dataset.code (ideal)
        //  - value/slug
        //  - texto visible (si empieza por "62 ..." o "6201 - ...")
        function extractCnaePrefixFromOption(selectEl) {
            const opt = selectEl?.selectedOptions?.[0];
            if (!opt) return '';

            const c1 = (opt.dataset?.code || '').trim();
            if (/^\d{2,4}$/.test(c1)) return c1;

            const rawCandidates = [
                (opt.value || ''),
                (opt.dataset?.name || ''),
                (opt.textContent || '')
            ].map(x => (x ?? '').toString().trim());

            for (const s of rawCandidates) {
                // busca 2-4 dígitos al inicio o tras separadores
                const m = s.match(/(^|\s|-|–|—)(\d{2,4})(\s|$)/);
                if (m && m[2]) return m[2];
                // fallback: quita no-dígitos y toma 2-4 primeros si tiene sentido
                const digits = s.replace(/[^0-9]/g, '');
                if (digits.length >= 2) return digits.slice(0, Math.min(4, digits.length));
            }
            return '';
        }

        function getSelectedCnaePrefix() {
            // Nivel más bajo seleccionado gana.
            // NOTA: no dependemos de que "slug" sea numérico: extraemos.
            const sub = extractCnaePrefixFromOption(cSubclass);
            if (sub) return sub;

            const cls = extractCnaePrefixFromOption(cClass);
            if (cls) return cls;

            const grp = extractCnaePrefixFromOption(cGroup);
            if (grp) return grp;

            const sec = extractCnaePrefixFromOption(cSection);
            if (sec) return sec;

            return '';
        }

        // bbox “España” para cuando no quieras limitar por mapa.
        // Incluye Península + Baleares + Canarias (aprox).
        function getSpainBbox() {
            return {
                north: 44.95,
                south: 27.50,
                east:   5.30,
                west: -18.40
            };
        }

        function getEffectiveBbox() {
            if (fUseBbox && fUseBbox.checked) {
                const b = map.getBounds();
                return {
                    north: b.getNorth(),
                    south: b.getSouth(),
                    east:  b.getEast(),
                    west:  b.getWest()
                };
            }
            return getSpainBbox();
        }

        // ---------- Load dropdown data ----------
        async function loadProvinces() {
            const res = await fetch('<?= site_url('api/geo/provinces') ?>', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = json.data || [];

            fProvince.innerHTML =
                `<option value="">— Selecciona —</option>` +
                rows.map(r =>
                    `<option value="${r.id}" data-name="${escapeAttr(r.pro_name)}">${escapeHtml(r.pro_name)}</option>`
                ).join('');

            setDisabledSelect(fMunicipality, '— Selecciona provincia antes —');
        }

        async function loadMunicipalities(provinceId) {
            fMunicipality.disabled = true;
            fMunicipality.innerHTML = `<option value="">Cargando…</option>`;

            const url = '<?= site_url('api/geo/municipalities') ?>?province_id=' + encodeURIComponent(provinceId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = json.data || [];

            fMunicipality.innerHTML =
                `<option value="">— Todos —</option>` +
                rows.map(r =>
                    `<option value="${r.id}" data-name="${escapeAttr(r.mun_name)}">${escapeHtml(r.mun_name)}</option>`
                ).join('');

            fMunicipality.disabled = false;
        }

        async function loadCnaeSections() {
            const res = await fetch('<?= site_url('api/cnae/sections') ?>', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = json.data || [];

            cSection.innerHTML =
                `<option value="">— Todas —</option>` +
                rows.map(r => `<option value="${r.id}">${escapeHtml(r.name)}</option>`).join('');
        }

        async function loadCnaeGroups(sectionId) {
            cGroup.disabled = true;
            cGroup.innerHTML = `<option value="">Cargando…</option>`;

            const res = await fetch('<?= site_url('api/cnae/groups') ?>?section_id=' + encodeURIComponent(sectionId), {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const rows = json.data || [];

            cGroup.innerHTML =
                `<option value="">— Todos —</option>` +
                rows.map(r => `<option value="${r.id}">${escapeHtml(r.name)}</option>`).join('');

            cGroup.disabled = false;
        }

        async function loadCnaeClasses(groupId) {
            cClass.disabled = true;
            cClass.innerHTML = `<option value="">Cargando…</option>`;

            const res = await fetch('<?= site_url('api/cnae/classes') ?>?group_id=' + encodeURIComponent(groupId), {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const rows = json.data || [];

            cClass.innerHTML =
                `<option value="">— Todas —</option>` +
                rows.map(r => `<option value="${r.id}">${escapeHtml(r.name)}</option>`).join('');

            cClass.disabled = false;
        }

        async function loadCnaeSubclasses(classId) {
            cSubclass.disabled = true;
            cSubclass.innerHTML = `<option value="">Cargando…</option>`;

            const res = await fetch('<?= site_url('api/cnae/subclasses') ?>?class_id=' + encodeURIComponent(classId), {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const rows = json.data || [];

            // IMPORTANTE:
            // - value sigue siendo slug (como ya lo tenías)
            // - pero el prefijo lo extraemos robusto (de slug o del texto)
            cSubclass.innerHTML =
                `<option value="">— Todas —</option>` +
                rows.map(r => `<option value="${escapeAttr(r.slug)}">${escapeHtml(r.name)}</option>`).join('');

            cSubclass.disabled = false;
        }

        // ---------- Events ----------
        fProvince.addEventListener('change', async () => {
            const provinceId = fProvince.value;
            if (!provinceId) {
                setDisabledSelect(fMunicipality, '— Selecciona provincia antes —');
                return;
            }
            try {
                await loadMunicipalities(provinceId);
            } catch (e) {
                setDisabledSelect(fMunicipality, 'Error cargando municipios');
            }
        });

        cSection.addEventListener('change', async () => {
            cGroup.innerHTML = `<option value="">— Selecciona sección —</option>`;
            cClass.innerHTML = `<option value="">— Selecciona grupo —</option>`;
            cSubclass.innerHTML = `<option value="">— Selecciona clase —</option>`;
            cGroup.disabled = true; cClass.disabled = true; cSubclass.disabled = true;

            if (!cSection.value) return;
            await loadCnaeGroups(cSection.value);
        });

        cGroup.addEventListener('change', async () => {
            cClass.innerHTML = `<option value="">— Selecciona grupo —</option>`;
            cSubclass.innerHTML = `<option value="">— Selecciona clase —</option>`;
            cClass.disabled = true; cSubclass.disabled = true;

            if (!cGroup.value) return;
            await loadCnaeClasses(cGroup.value);
        });

        cClass.addEventListener('change', async () => {
            cSubclass.innerHTML = `<option value="">— Selecciona clase —</option>`;
            cSubclass.disabled = true;

            if (!cClass.value) return;
            await loadCnaeSubclasses(cClass.value);
        });

        // ---------- Search / Export ----------
        async function search() {
            const bbox = getEffectiveBbox();

            const provinceText = selectedDataName(fProvince).trim();
            const municipalityText = selectedDataName(fMunicipality).trim();

            // CNAE: usa el nivel más bajo disponible
            const cnaePrefix = getSelectedCnaePrefix();

            const params = new URLSearchParams({
                north: bbox.north,
                south: bbox.south,
                east:  bbox.east,
                west:  bbox.west,
                limit: 5000,

                province: provinceText,
                municipality: municipalityText,

                cnae_prefix: cnaePrefix,
                cnae_text: '',

                estado: fEstado.value.trim(),
                has_phone: fHasPhone.checked ? 1 : 0,
                only_geocoded: fOnlyGeocoded.checked ? 1 : 0,

                // backend puede ignorarlo, pero lo mandamos para trazabilidad
                use_bbox: (fUseBbox && fUseBbox.checked) ? 1 : 0
            });

            statusText.textContent = 'Buscando empresas…';
            btnSearch.disabled = true;
            btnExport.disabled = true;

            try {
                const res = await fetch('<?= site_url('api/map/search') ?>?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });

                const json = await res.json();
                if (!res.ok || json.success === false) {
                    const msg = json.message || json.error || 'no se pudo buscar';
                    statusText.textContent = 'Error: ' + msg;
                    return;
                }

                emptyOverlay.style.display = 'none';

                const rows = json.data || [];
                const meta = json.meta || {};

                cluster.clearLayers();
                for (const r of rows) {
                    if (!r.lat || !r.lng) continue;
                    const m = L.marker([parseFloat(r.lat), parseFloat(r.lng)]);
                    m.bindPopup(popupHtml(r));
                    cluster.addLayer(m);
                }

                renderResults(rows);

                kCount.textContent = (meta.count ?? rows.length).toString();
                kPhone.textContent = (meta.with_phone ?? '—').toString();
                renderTopCnae(meta.top_cnae || {});

                statusText.textContent = `Listo: ${rows.length} empresas (límite ${meta.limit || '—'}).`;
                btnExport.disabled = false;
            } catch (e) {
                statusText.textContent = 'Error de red al buscar.';
            } finally {
                btnSearch.disabled = false;
            }
        }

        btnSearch.addEventListener('click', search);

        btnExport.addEventListener('click', () => {
            const bbox = getEffectiveBbox();

            const provinceText = selectedDataName(fProvince).trim();
            const municipalityText = selectedDataName(fMunicipality).trim();

            const cnaePrefix = getSelectedCnaePrefix();

            const params = new URLSearchParams({
                north: bbox.north,
                south: bbox.south,
                east:  bbox.east,
                west:  bbox.west,
                limit: 50000,

                province: provinceText,
                municipality: municipalityText,

                cnae_prefix: cnaePrefix,
                cnae_text: '',

                estado: fEstado.value.trim(),
                has_phone: fHasPhone.checked ? 1 : 0,
                only_geocoded: fOnlyGeocoded.checked ? 1 : 0,

                use_bbox: (fUseBbox && fUseBbox.checked) ? 1 : 0
            });

            window.location.href = '<?= site_url('api/map/export') ?>?' + params.toString();
        });

        // ---------- UI rendering ----------
        function popupHtml(r) {
            const safe = (s) => escapeHtml((s ?? '').toString());
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

        function renderResults(rows) {
            const MAX = 50;
            const subset = rows.slice(0, MAX);

            if (subset.length === 0) {
                resultsEl.innerHTML = `<div class="empty">Sin resultados con los filtros actuales.</div>`;
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

            if (rows.length > MAX) {
                resultsEl.innerHTML += `<div class="more">Mostrando ${MAX} de ${rows.length}. Usa Export para descargar más.</div>`;
            }
        }

        function renderTopCnae(obj) {
            const entries = Object.entries(obj || {});
            if (entries.length === 0) {
                topCnae.textContent = '—';
                return;
            }
            topCnae.innerHTML = entries.map(([k, v]) => `
      <div class="mini-item"><span>${escapeHtml(k)}</span><strong>${v}</strong></div>
    `).join('');
        }

        // ---------- Boot ----------
        loadProvinces();
        loadCnaeSections();
    })();
</script>


</body>
</html>
