<!-- app/Views/search.php -->
<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => !empty($q) ? ('Buscar empresa: ' . $q . ' | APIEmpresas.es') : 'Buscar empresa | APIEmpresas.es',
        'excerptText' => 'Busca empresas por CIF o nombre comercial. Resultados trazables con fuentes oficiales y salida por API.',
        'canonical' => site_url('search_company') . (!empty($q) ? ('?q=' . rawurlencode($q)) : ''),
        'robots' => 'noindex,follow',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css?v=' . time()) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header') ?>

    <main style="padding:40px 0 70px;">
        <section class="container search-section">
            <div class="search-panel">
                <div style="text-align: center; margin-bottom: 34px;">
                    <span class="badge-intro">
                        <span class="dot-live"></span>
                        Buscador oficial en tiempo real
                    </span>
                    <h2 style="font-size: 2.5rem; font-weight: 950; letter-spacing: -0.04em;">Validación de <span class="highlight">empresas</span></h2>
                    <p class="subtitle" style="font-size: 1.15rem; color: #64748b; margin-top: 10px;">
                        Introduce un <strong>CIF</strong> o nombre comercial. Resultados oficiales con trazabilidad mercantil completa.
                    </p>
                </div>

                <div class="search-form-wrapper">
                    <form class="search-form" method="GET" action="<?= site_url('search_company') ?>" id="searchForm">
                        <?= csrf_field() ?>
                        <input class="search-input" id="q" name="q" value="<?= esc($q ?? '') ?>"
                            placeholder="Ej. Gestiones López o B12345678" aria-label="Buscar empresa por nombre o CIF"
                            autocomplete="off" />
                        <button class="btn secondary" id="btnBuscar" type="submit" style="padding: 0 40px; font-size: 1.1rem; height: 72px; border-radius: 14px; min-width: 200px;">Validar ahora</button>
                    </form>
                </div>

                <div id="resultado" class="result" style="margin-top: 40px;">
                    <?php if (!empty($errorMsg)): ?>
                        <div class="muted"><?= esc($errorMsg) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($company) && is_array($company)): ?>
                        <?php
                        $statusRaw = (string) ($company['status'] ?? '');
                        $isActive = strtoupper($statusRaw) === 'ACTIVA';
                        $statusClass = $isActive ? 'activa' : 'inactiva';
                        $statusStyle = $isActive ? 'background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7;' : 'background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2;';

                        $province = $company['province'] ?? $company['provincia'] ?? 'España';
                        $cnaeFull = (!empty($company['cnae_label'])) ? $company['cnae_label'] : ($company['cnae'] ?? '-');
                        
                        $jsonForCode = ['success' => true, 'data' => $company];
                        $jsonPretty = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                        // Radar Logic (Simplified for SSR)
                        $radarUrl = site_url('leads-empresas-nuevas');
                        $radarTitle = "Accede a nuevas oportunidades comerciales";
                        ?>
                        <div class="search-result-card reveal" style="background: #ffffff; border-radius: 24px; padding: 40px; box-shadow: 0 20px 50px -10px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; text-align: left;">
                          
                          <div class="result-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                            <div style="display: flex; gap: 20px; align-items: center;">
                              <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #2563eb, #10b981); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; font-weight: 800; box-shadow: 0 8px 16px rgba(37,99,235,0.2);">
                                <?= esc(mb_substr($company['name'] ?? 'N', 0, 1, 'UTF-8')) ?>
                              </div>
                              <div>
                                <h2 style="margin: 0; font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;"><?= esc($company['name'] ?? 'N/A') ?></h2>
                                <div style="display: flex; gap: 12px; margin-top: 6px; align-items: center;">
                                  <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 8px; font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem;"><?= esc($company['cif'] ?? $company['nif'] ?? 'Sin CIF') ?></span>
                                  <span style="font-size: 0.9rem; color: #94a3b8;">•</span>
                                  <span style="font-size: 0.9rem; color: #64748b; font-weight: 600;"><?= esc($province) ?></span>
                                </div>
                              </div>
                            </div>
                            <span class="status-badge <?= esc($statusClass) ?>" style="padding: 8px 16px; border-radius: 100px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; <?= $statusStyle ?>">
                              <?= esc($statusRaw ?: 'N/A') ?>
                            </span>
                          </div>

                          <div class="result-info-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 40px; padding: 24px; background: #f8fafc; border-radius: 20px; border: 1px solid #f1f5f9;">
                            <div class="info-item">
                              <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Actividad</span>
                              <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700; line-height: 1.4;"><?= esc($cnaeFull) ?></span>
                            </div>
                            <div class="info-item">
                              <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Constitución</span>
                              <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;"><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-item">
                              <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Capital Social</span>
                              <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;"><?= esc($company['capital'] ?? 'Consultar') ?></span>
                            </div>
                          </div>

                          <!-- Bridge to Radar Dinámico -->
                          <div class="radar-bridge" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border: 1px solid #dcfce7; border-radius: 24px; padding: 32px; display: flex; gap: 28px; align-items: center; margin-bottom: 24px; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);"></div>
                            
                            <div style="width: 56px; height: 56px; background: #ffffff; color: #16a34a; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
                              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4M12 16V8"/></svg>
                            </div>
                            
                            <div style="flex-grow: 1;">
                              <div class="radar-badge" style="display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid #dcfce7; padding: 4px 12px; border-radius: 100px; font-size: 0.75rem; font-weight: 700; color: #16a34a; margin-bottom: 12px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                +1.400 empresas nuevas esta semana
                              </div>
                              <h4 style="margin: 0 0 8px 0; font-size: 1.25rem; color: #0f172a; font-weight: 900;"><?= $radarTitle ?></h4>
                              <p style="margin: 0; font-size: 1rem; color: #475569; line-height: 1.6; max-width: 480px;">Además de consultar esta empresa, puedes detectar nuevas sociedades con potencial de negocio y trabajarlas antes que la competencia.</p>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; z-index: 1;">
                                <a href="<?= $radarUrl ?>" class="btn secondary" style="padding: 16px 32px; font-size: 1.1rem; border-radius: 16px; box-shadow: 0 15px 30px rgba(18, 180, 138, 0.25); margin: 0; min-width: 200px; text-align: center;">Ver empresas nuevas hoy</a>
                                <span style="font-size: 0.8rem; color: #64748b; font-weight: 600; text-align: center; opacity: 0.8;">Oportunidades limitadas en el tiempo</span>
                            </div>
                          </div>

                          <div style="text-align: center; margin-bottom: 40px;">
                            <span style="font-size: 0.95rem; font-weight: 500; color: #64748b; font-style: italic;">“La detección temprana de empresas puede marcar la diferencia en procesos comerciales.”</span>
                          </div>

                          <div class="result-actions" style="display: flex; gap: 16px;">
                            <a href="<?= company_url($company) ?>" class="btn" style="text-decoration:none; padding: 20px 32px; font-weight: 800; flex-grow: 1; text-align: center; background: #ffffff; color: #0f172a; border: 2px solid #e2e8f0; border-radius: 16px; transition: all 0.3s ease;">Ver ficha completa detallada</a>
                            <a href="<?= site_url('documentation') ?>" class="btn secondary" style="text-decoration:none; padding: 20px 32px; font-weight: 800; text-align: center; border-radius: 16px; flex-grow: 1; background: #2563eb;">Integrar vía API</a>
                          </div>

                          <div class="company-card__footer" style="margin-top: 24px; text-align: center;">
                              <button type="button" class="btn-json-api" style="background: none; border: none; color: #2563eb; font-weight: 700; cursor: pointer; text-decoration: underline;">Ver JSON de la API</button>
                          </div>

                          <pre class="company-card__json is-hidden" style="margin-top: 20px; text-align: left;"><code><?= esc($jsonPretty) ?></code></pre>
                        </div>

                    <?php elseif (!empty($companies) && is_array($companies)): ?>
                        <div
                            style="display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
                            <?php
                            helper('company');
                            foreach ($companies as $co):
                                $coUrl = company_url($co);
                                ?>
                                <a href="<?= esc($coUrl) ?>"
                                    style="text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 24px; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; position: relative; overflow: hidden;"
                                    onmouseover="this.style.borderColor='var(--ae-blue)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)'"
                                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)'">
                                    
                                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px;">
                                        <div style="width: 44px; height: 44px; background: #f1f5f9; color: #475569; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                                            <?= esc(mb_substr($co['name'] ?? 'E', 0, 1, 'UTF-8')) ?>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; line-height: 1.3; margin-bottom: 2px;">
                                                <?= esc($co['name'] ?? 'Empresa') ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; font-family: 'JetBrains Mono', monospace;">
                                                <?= esc($co['cif'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;"><?= esc($co['province'] ?? $co['provincia'] ?? 'España') ?></span>
                                        <span style="font-size: 0.75rem; background: #f0fdf4; color: #16a34a; padding: 4px 10px; border-radius: 100px; font-weight: 800; text-transform: uppercase;">Activa</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- RADAR PRO CTA -->
        <section class="container" style="margin-top: 40px;">
            <div class="dash-cta-card" style="display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center; max-width: 1000px; margin-inline: auto;">
                <div>
                    <h3>
                        <div class="dash-cta-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                        ¿Necesitas monitorizar nuevas empresas en tiempo real?
                    </h3>
                    <p style="margin-bottom: 0;">
                        Nuestro motor <strong>Radar Pro</strong> analiza el BORME diariamente para ofrecerte leads frescos de cualquier sector o provincia. Impulsa tu estrategia comercial con datos oficiales en tiempo real.
                    </p>
                </div>
                <div style="min-width: 240px;">
                    <a href="<?=site_url() ?>leads-empresas-nuevas" class="btn">
                        Ver Radar PRO →
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /**
         * Buscador SEO-friendly + robusto:
         * - Evita doble inicialización aunque haya otro JS en view('scripts')
         * - Usa endpoint correcto con site_url('search') (respeta /apiempresas en local)
         * - Evita carreras con AbortController
         * - Usa solo submit (sin keydown enter extra)
         */
        (function () {
            if (window.__APIE_SEARCH_INIT__) return; // ✅ evita duplicados
            window.__APIE_SEARCH_INIT__ = true;

            // Tracking mínimo hacia CodeIgniter (igual que tu versión)
            window.track = window.track || (async function (name, props = {}) {
                try {
                    await fetch('<?= site_url('events/track') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            name,
                            session_id: (localStorage.getItem('ve_session_id') || (function () {
                                const v = Math.random().toString(36).slice(2) + Date.now().toString(36);
                                localStorage.setItem('ve_session_id', v);
                                return v;
                            })()),
                            page_path: window.location.pathname + window.location.search,
                            referer: document.referrer || null,
                            props
                        })
                    });
                } catch (e) { /* silencioso */ }
            });

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('searchForm');
                const btn = document.getElementById('btnBuscar');
                const qEl = document.getElementById('q');
                const out = document.getElementById('resultado');

                if (!form || !btn || !qEl || !out) return;

                // Endpoint JSON real (IMPORTANTE: site_url para respetar subcarpeta /apiempresas)
                const JSON_ENDPOINT_BASE = '<?= site_url('search') ?>'; // -> http://localhost/apiempresas/search

                function sectionRegistro(company, apiJson) {
                    const statusRaw = (company.status || '').toString();
                    const isActive = statusRaw.toUpperCase() === 'ACTIVA';
                    const statusClass = isActive ? 'activa' : 'inactiva';
                    const statusStyle = isActive ? 'background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7;' : 'background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2;';

                    const province = company.province || company.provincia || 'España';
                    const cnaeFull = (company.cnae_label) ? company.cnae_label : (company.cnae || '-');

                    const jsonForCode = (apiJson && typeof apiJson === 'object')
                        ? apiJson
                        : { success: true, data: company };

                    const jsonPretty = JSON.stringify(jsonForCode, null, 2)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');

                    const radarUrl = '<?= site_url('leads-empresas-nuevas') ?>';
                    const baseUrl = '<?= site_url() ?>'.replace(/\/$/, '') + '/';
                    const companyUrl = baseUrl + (company.cif || company.nif);

                    return `
<div class="search-result-card reveal" style="background: #ffffff; border-radius: 24px; padding: 40px; box-shadow: 0 20px 50px -10px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; text-align: left;">
  
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
    <span class="status-badge ${statusClass}" style="padding: 8px 16px; border-radius: 100px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; ${statusStyle}">
      ${statusRaw || 'N/A'}
    </span>
  </div>

  <div class="result-info-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 40px; padding: 24px; background: #f8fafc; border-radius: 20px; border: 1px solid #f1f5f9;">
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Actividad</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700; line-height: 1.4;">${cnaeFull}</span>
    </div>
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Constitución</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;">${company.incorporation_date || company.founded || company.fecha_constitucion || 'N/A'}</span>
    </div>
    <div class="info-item">
      <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Capital Social</span>
      <span style="display: block; font-size: 1rem; color: #1e293b; font-weight: 700;">${company.capital || 'Consultar'}</span>
    </div>
  </div>

  <div class="radar-bridge" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border: 1px solid #dcfce7; border-radius: 24px; padding: 32px; display: flex; gap: 28px; align-items: center; margin-bottom: 24px; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);"></div>
    
    <div style="width: 56px; height: 56px; background: #ffffff; color: #16a34a; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4M12 16V8"/></svg>
    </div>
    
    <div style="flex-grow: 1;">
      <div class="radar-badge" style="display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid #dcfce7; padding: 4px 12px; border-radius: 100px; font-size: 0.75rem; font-weight: 700; color: #16a34a; margin-bottom: 12px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
        +1.400 empresas nuevas esta semana
      </div>
      <h4 style="margin: 0 0 8px 0; font-size: 1.25rem; color: #0f172a; font-weight: 900;">Accede a nuevas oportunidades comerciales</h4>
      <p style="margin: 0; font-size: 1rem; color: #475569; line-height: 1.6; max-width: 480px;">Además de consultar esta empresa, puedes detectar nuevas sociedades con potencial de negocio y trabajarlas antes que la competencia.</p>
    </div>
    
    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; z-index: 1;">
        <a href="${radarUrl}" class="btn secondary" style="padding: 16px 32px; font-size: 1.1rem; border-radius: 16px; box-shadow: 0 15px 30px rgba(18, 180, 138, 0.25); margin: 0; min-width: 200px; text-align: center;">Ver empresas nuevas hoy</a>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600; text-align: center; opacity: 0.8;">Oportunidades limitadas en el tiempo</span>
    </div>
  </div>

  <div style="text-align: center; margin-bottom: 40px;">
    <span style="font-size: 0.95rem; font-weight: 500; color: #64748b; font-style: italic;">“La detección temprana de empresas puede marcar la diferencia en procesos comerciales.”</span>
  </div>

  <div class="result-actions" style="display: flex; gap: 16px;">
    <a href="${companyUrl}" class="btn" style="text-decoration:none; padding: 20px 32px; font-weight: 800; flex-grow: 1; text-align: center; background: #ffffff; color: #0f172a; border: 2px solid #e2e8f0; border-radius: 16px; transition: all 0.3s ease;">Ver ficha completa detallada</a>
    <a href="<?= site_url('documentation') ?>" class="btn secondary" style="text-decoration:none; padding: 20px 32px; font-weight: 800; text-align: center; border-radius: 16px; flex-grow: 1; background: #2563eb;">Integrar vía API</a>
  </div>

  <div class="company-card__footer" style="margin-top: 24px; text-align: center;">
      <button type="button" class="btn-json-api" style="background: none; border: none; color: #2563eb; font-weight: 700; cursor: pointer; text-decoration: underline;">Ver JSON de la API</button>
  </div>

  <pre class="company-card__json is-hidden" style="margin-top: 20px; text-align: left;"><code>${jsonPretty}</code></pre>
</div>`;
                }

                function bindJsonButtons(container) {
                    const buttons = container.querySelectorAll('.btn-json-api');
                    buttons.forEach((b) => {
                        if (b.dataset.bound === '1') return;
                        b.dataset.bound = '1';
                        b.addEventListener('click', (e) => {
                            e.preventDefault();
                            const card = b.closest('.search-result-card');
                            const pre = card?.querySelector('.company-card__json');
                            if (!pre) return;
                            const nowHidden = pre.classList.toggle('is-hidden');
                            b.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
                        });
                    });
                }

                // Si SSR pintó una card, activamos el botón
                bindJsonButtons(out);

                let currentController = null;
                let lastRequestId = 0;

                async function doSearch(rawValue) {
                    const v = (rawValue || '').trim();

                    if (!v) {
                        out.innerHTML = '<div class="muted">Escribe un CIF (ej. B12345678).</div><div style="margin-top:20px;"><a href="<?= site_url('empresas-nuevas') ?>" class="btn secondary">Explorar empresas nuevas en España</a></div>';
                        return;
                    }

                    // Cancela request anterior (evita “primero error luego ok” por carreras)
                    if (currentController) currentController.abort();
                    currentController = new AbortController();

                    const requestId = ++lastRequestId;

                    // UI: limpia y muestra loading (en el MISMO tick)
                    out.innerHTML = '<div class="muted">Buscando empresa en la base de datos...</div>';
                    btn.disabled = true;

                    const endpoint = JSON_ENDPOINT_BASE + '?q=' + encodeURIComponent(v);

                    try {
                        const res = await fetch(endpoint, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Search-Origin': 'web-search',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: currentController.signal
                        });

                        // Si entra una búsqueda nueva, ignoramos esta respuesta
                        if (requestId !== lastRequestId) return;

                        let json = null;
                        try { json = await res.json(); } catch (_) { json = null; }

                        if (!res.ok) {
                            const msg = (json && json.message) ? json.message : 'No se encontró ninguna empresa con ese CIF.';
                            out.innerHTML = `<div class="muted">${msg}</div><div style="margin-top:20px;"><a href="<?= site_url('empresas-nuevas') ?>" class="btn secondary">Ver listado de empresas nuevas</a></div>`;
                            return;
                        }

                        if (!json || json.success === false) {
                            const msg = (json && json.message) ? json.message : 'Se ha producido un error al consultar la empresa.';
                            out.innerHTML = `<div class="muted">${msg}</div><div style="margin-top:20px;"><a href="<?= site_url('empresas-nuevas') ?>" class="btn secondary">Explorar empresas nuevas en España</a></div>`;
                            return;
                        }

                        const company = json.data || {};
                        out.innerHTML = sectionRegistro(company, json);
                        bindJsonButtons(out);

                        // URL shareable sin recargar
                        const url = new URL(window.location.href);
                        url.searchParams.set('q', v);
                        window.history.replaceState({}, '', url.toString());

                        if (typeof track === 'function') {
                            track('search_by_cif', { cif: v });
                        }

                    } catch (e) {
                        if (e && e.name === 'AbortError') return; // búsqueda cancelada
                        console.error(e);
                        out.innerHTML = '<div class="muted">Error de conexión con la API.</div>';
                    } finally {
                        if (requestId === lastRequestId) {
                            btn.disabled = false;
                        }
                    }
                }

                // Solo SUBMIT (evita doble disparo con Enter)
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    doSearch(qEl.value);
                });

                // Si vienes con ?q=..., rellena input (no fuerza AJAX; SSR ya pudo pintar)
                const params = new URLSearchParams(window.location.search);
                const qUrl = (params.get('q') || '').trim();
                if (qUrl) qEl.value = qUrl;
            });
        })();
    </script>

</body>

</html>