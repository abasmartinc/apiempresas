<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Integración Google Sheets']) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
</head>
<body>
    <?= view('partials/header') ?>
    <main>
<style>
    .gs-page-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 60px 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .gs-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .gs-hero {
        text-align: center;
        margin-bottom: 40px;
    }
    .gs-badge-top {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ecfdf5;
        color: #059669;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 24px;
        border: 1px solid #a7f3d0;
    }
    .gs-title {
        font-size: 3.5rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin: 0 0 20px 0;
        line-height: 1.1;
    }
    .gs-title span {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .gs-subtitle {
        font-size: 1.3rem;
        color: #475569;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* AHA Moment Animation Table */
    .gs-demo-wrapper {
        margin: 40px auto 60px auto;
        max-width: 800px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .gs-demo-header {
        background: #f1f5f9;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .gs-demo-dots {
        display: flex;
        gap: 6px;
    }
    .gs-demo-dot { width: 12px; height: 12px; border-radius: 50%; background: #cbd5e1; }
    .gs-demo-dot:nth-child(1) { background: #ef4444; }
    .gs-demo-dot:nth-child(2) { background: #f59e0b; }
    .gs-demo-dot:nth-child(3) { background: #10b981; }
    .gs-demo-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Inter', sans-serif;
        font-size: 0.95rem;
    }
    .gs-demo-table th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }
    .gs-demo-table th:first-child { width: 40px; text-align: center; }
    .gs-demo-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        color: #0f172a;
    }
    .gs-demo-table tr td:first-child { background: #f8fafc; text-align: center; font-weight: 600; color: #94a3b8; }
    
    .gs-cell-cif { font-family: monospace; font-weight: 700; color: #3b82f6; }
    .gs-cell-formula { font-family: monospace; color: #64748b; font-size: 0.85rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; }
    
    .gs-anim-text {
        position: relative;
        display: inline-block;
    }
    .gs-anim-text::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #ffffff;
        animation: revealText 4s infinite cubic-bezier(0.77, 0, 0.175, 1);
        transform-origin: right;
    }
    .gs-anim-text.delay-1::after { animation-delay: 0.5s; }
    .gs-anim-text.delay-2::after { animation-delay: 1.0s; }
    .gs-anim-text.delay-3::after { animation-delay: 1.5s; }

    @keyframes revealText {
        0%, 10% { transform: scaleX(1); }
        30%, 90% { transform: scaleX(0); }
        100% { transform: scaleX(1); }
    }

    /* Layout Grids */
    .gs-features {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 60px;
    }
    @media (min-width: 768px) {
        .gs-features { grid-template-columns: repeat(3, 1fr); }
    }
    .gs-feature-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .gs-feature-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: #eff6ff;
        color: #2563eb;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .gs-feature-card h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 8px 0; color: #0f172a; }
    .gs-feature-card p { margin: 0; color: #475569; font-size: 0.95rem; line-height: 1.5; }

    /* Integration Options */
    .gs-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 40px;
        position: relative;
    }
    .gs-card-accent {
        position: absolute; top: 0; left: 0; width: 100%; height: 6px;
        background: linear-gradient(90deg, #34d399, #059669);
    }
    .gs-card-body { padding: 48px; }
    .gs-step-title {
        font-size: 1.8rem; font-weight: 900; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 16px;
    }
    .gs-step-number {
        display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: #ffffff; font-size: 1.2rem; font-weight: 800; flex-shrink: 0;
    }
    .gs-step-number.blue { background: #2563eb; }
    .gs-step-number.green { background: #059669; }
    
    .gs-desc { color: #475569; font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; }
    
    /* Template CTA */
    .gs-template-box {
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; padding: 32px; display: flex; flex-direction: column; gap: 24px;
    }
    @media (min-width: 768px) {
        .gs-template-box { flex-direction: row; align-items: center; justify-content: space-between; }
    }
    .gs-template-title { font-weight: 900; color: #1e3a8a; font-size: 1.4rem; margin: 0 0 8px 0; }
    .gs-template-desc { color: #1d4ed8; margin: 0; font-size: 1.1rem; line-height: 1.5; }
    .gs-btn-primary {
        display: inline-block; background: #2563eb; color: #ffffff; font-weight: 800; font-size: 1.1rem; padding: 16px 32px; border-radius: 12px; text-decoration: none; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); transition: all 0.2s; text-align: center; white-space: nowrap; border: none; cursor: pointer;
    }
    .gs-btn-primary:hover { background: #1d4ed8; transform: translateY(-3px); box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4); }

    /* Custom Excel Steps */
    .gs-steps-list { display: flex; flex-direction: column; gap: 48px; }
    .gs-substep { display: flex; gap: 24px; align-items: flex-start; }
    .gs-substep-letter {
        display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #dcfce7; color: #15803d; font-weight: 900; border-radius: 10px; font-size: 1.1rem; flex-shrink: 0; margin-top: 4px;
    }
    .gs-substep-content h4 { margin: 0 0 8px 0; font-size: 1.4rem; color: #0f172a; font-weight: 800; }
    .gs-substep-content p { margin: 0 0 16px 0; color: #475569; font-size: 1.1rem; line-height: 1.6; }

    /* Code Block & Gated Content */
    .gs-code-wrapper { position: relative; width: 100%; border-radius: 12px; overflow: hidden; }
    .gs-code-block { background: #0f172a; }
    
    <?php if (!$is_logged_in): ?>
    .gs-code-block { filter: blur(6px); pointer-events: none; user-select: none; }
    .gs-gated-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.1); z-index: 10;
    }
    .gs-btn-gated {
        background: #f59e0b; color: #fff; font-weight: 900; font-size: 1.2rem; padding: 18px 40px; border-radius: 100px; text-decoration: none; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4); transition: transform 0.2s; border: 2px solid #fff;
    }
    .gs-btn-gated:hover { transform: scale(1.05); color: #fff; }
    <?php endif; ?>

    .gs-code-header { display: flex; justify-content: flex-end; padding: 12px; background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.1); }
    .gs-btn-copy { background: rgba(255,255,255,0.1); color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .gs-btn-copy:hover { background: rgba(255,255,255,0.2); }
    .gs-code-content { margin: 0; padding: 24px; color: #34d399; font-family: 'Courier New', Courier, monospace; font-size: 0.95rem; line-height: 1.5; overflow-x: auto; max-height: 400px; }
    
    .gs-formula-box { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 16px 24px; border-radius: 8px; font-family: 'Courier New', Courier, monospace; font-size: 1.2rem; color: #0f172a; font-weight: 800; display: inline-block; margin-bottom: 12px; }
</style>

<div class="gs-page-wrapper">
    <div class="gs-container">
        
        <div class="gs-hero">
            <div class="gs-badge-top">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                +3.000 equipos B2B ya lo usan
            </div>
            <h1 class="gs-title">Automatiza tu prospección B2B<br>directamente en <span>Google Sheets</span></h1>
            <p class="gs-subtitle">Conecta tu hoja de cálculo a la API oficial. Mete un CIF y observa cómo se rellena el teléfono, empresa y facturación al instante. Sin picar código, sin buscar en Google.</p>
        </div>

        <!-- AHA Moment Animation -->
        <div class="gs-demo-wrapper">
            <div class="gs-demo-header">
                <div class="gs-demo-dots">
                    <div class="gs-demo-dot"></div><div class="gs-demo-dot"></div><div class="gs-demo-dot"></div>
                </div>
                <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 500; font-family: sans-serif; margin-left: 12px;">Lista_Clientes_2026 - Google Sheets</div>
            </div>
            <table class="gs-demo-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>CIF Empresa</th>
                        <th>Razón Social</th>
                        <th>Provincia</th>
                        <th>Facturación</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="gs-cell-cif">A15075062</td>
                        <td><span class="gs-anim-text delay-1" style="font-weight:700;">INDITEX SA</span></td>
                        <td><span class="gs-anim-text delay-2">A Coruña</span></td>
                        <td><span class="gs-anim-text delay-3" style="color:#059669; font-weight:600;">35.947.000.000 €</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="gs-cell-cif">B14714489</td>
                        <td><span class="gs-anim-text delay-1" style="font-weight:700;">ARJONA MATAS SL</span></td>
                        <td><span class="gs-anim-text delay-2">Córdoba</span></td>
                        <td><span class="gs-anim-text delay-3" style="color:#059669; font-weight:600;">1.473.600 €</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><div style="width: 120px; height: 16px; background: #e2e8f0; border-radius: 4px;"></div></td>
                        <td class="gs-cell-formula">=APIEMPRESAS(B4; "name")</td>
                        <td class="gs-cell-formula">=APIEMPRESAS(B4; "province")</td>
                        <td class="gs-cell-formula">=APIEMPRESAS(B4; "capital_social_raw")</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Use cases -->
        <div class="gs-features">
            <div class="gs-feature-card">
                <div class="gs-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>Para SDRs y Ventas</h3>
                <p>Crea listas de prospección hiper-segmentadas sin tener que investigar empresa por empresa manualmente.</p>
            </div>
            <div class="gs-feature-card">
                <div class="gs-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <h3>Enriquecer Bases Antiguas</h3>
                <p>¿Tienes un Excel antiguo con CIFs? Pega la fórmula y actualiza todos los datos, teléfonos y estados (Activa/Extinguida).</p>
            </div>
            <div class="gs-feature-card">
                <div class="gs-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </div>
                <h3>Lead Scoring</h3>
                <p>Cruza los datos de facturación con tus leads entrantes para priorizar a los clientes más grandes primero.</p>
            </div>
        </div>

        <!-- Opción 1: Plantilla -->
        <div class="gs-card">
            <div class="gs-card-body">
                <h2 class="gs-step-title">
                    <span class="gs-step-number blue">1</span>
                    La forma rápida: Usa nuestra Plantilla
                </h2>
                <p class="gs-desc">Cero configuraciones. Hemos preparado un Google Sheets profesional que <strong>ya tiene el motor de IA instalado</strong>. Haz una copia, pega tu API Key y empieza a usarlo.</p>
                
                <div class="gs-template-box">
                    <div>
                        <h3 class="gs-template-title">Plantilla Prospección B2B 2026</h3>
                        <p class="gs-template-desc">Tablero preconfigurado con fórmulas avanzadas de enriquecimiento masivo.</p>
                    </div>
                    <a href="https://docs.google.com/spreadsheets/d/1ke9ErUQktrusqx1rVWx1WTJ5p6qNItGd4H7mUtXUipY/copy" target="_blank" class="gs-btn-primary">Descargar Plantilla Gratis</a>
                </div>
            </div>
        </div>

        <!-- Opción 2: Script -->
        <div class="gs-card">
            <div class="gs-card-accent"></div>
            <div class="gs-card-body">
                <h2 class="gs-step-title">
                    <span class="gs-step-number green">2</span>
                    La forma pro: Instálalo en tu propio Excel
                </h2>
                <p class="gs-desc">Integra la API directamente en tus hojas de trabajo actuales en menos de 1 minuto copiando un pequeño script.</p>

                <div class="gs-steps-list">
                    
                    <div class="gs-substep">
                        <div class="gs-substep-letter">A</div>
                        <div class="gs-substep-content">
                            <h4>Abre el editor de Apps Script</h4>
                            <p>En el menú superior de tu Google Sheets, haz clic en <strong>Extensiones &gt; Apps Script</strong>.</p>
                        </div>
                    </div>

                    <div class="gs-substep">
                        <div class="gs-substep-letter">B</div>
                        <div class="gs-substep-content" style="width: 100%;">
                            <h4>Pega el código de conexión</h4>
                            <p>Borra todo el texto que aparezca y pega este motor. <?php if ($is_logged_in): ?><strong style="color: #059669;">Ya lleva tu API Key personal inyectada.</strong><?php endif; ?></p>
                            
                            <div class="gs-code-wrapper">
                                <?php if (!$is_logged_in): ?>
                                <div class="gs-gated-overlay">
                                    <a href="<?= site_url('register?redirect=integraciones/google-sheets') ?>" class="gs-btn-gated">Crear cuenta gratis para ver el código</a>
                                    <p style="color: #cbd5e1; font-weight: 600; margin-top: 16px;">O si ya tienes cuenta, <a href="<?= site_url('enter?redirect=integraciones/google-sheets') ?>" style="color: #fff; text-decoration: underline;">inicia sesión</a>.</p>
                                </div>
                                <?php endif; ?>

                                <div class="gs-code-block">
                                    <div class="gs-code-header">
                                        <button onclick="copyScript()" class="gs-btn-copy" <?= !$is_logged_in ? 'disabled' : '' ?>>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                            <span id="copyText">Copiar código</span>
                                        </button>
                                    </div>
                                    <pre class="gs-code-content" id="scriptCode">/**
 * Extrae datos del BORME desde APIEmpresas B2B
 *
 * @param {string} cif El CIF de la empresa (ej: "A15075062").
 * @param {string} campo El campo a extraer (ej: "name", "province").
 * @return El valor del campo.
 * @customfunction
 */
function APIEMPRESAS(cif, campo) {
  if (!cif) return "";
  if (!campo) return "Falta el campo";
  
  var apiKey = "<?= $api_key ? esc($api_key) : 'PEGAR_AQUI' ?>";
  
  if (apiKey === "PEGAR" + "_AQUI" || apiKey.trim() === "") {
    return "⚠️ Falta API Key (Regístrate gratis en apiempresas.es)";
  }

  var url = "https://apiempresas.es/api/v1/companies?cif=" + encodeURIComponent(cif);
  
  var options = {
    "method": "get",
    "headers": {
      "X-API-KEY": apiKey
    },
    "muteHttpExceptions": true
  };
  
  var maxRetries = 5;
  var delay = 1000;
  
  for (var i = 0; i < maxRetries; i++) {
    try {
      var response = UrlFetchApp.fetch(url, options);
      var code = response.getResponseCode();
      
      if (code === 429) {
        Utilities.sleep(delay + (Math.random() * 1000));
        delay *= 1.5; // Backoff exponencial
        continue;
      }
      
      var json = JSON.parse(response.getContentText());
      
      if (json.success && json.data) {
        if (json.data[campo] !== undefined && json.data[campo] !== null) {
          return json.data[campo];
        } else {
          return "Campo no encontrado";
        }
      } else {
        return "Error: " + (json.message || "No encontrado");
      }
    } catch (e) {
      // Solo reintentamos si es un error de límite encubierto
      if (e.message && e.message.indexOf("429") !== -1 && i < maxRetries - 1) {
         Utilities.sleep(delay + (Math.random() * 1000));
         delay *= 1.5;
         continue;
      }
      return "Error de conexión";
    }
  }
  
  return "Límite de velocidad. Por favor, procesa menos filas a la vez.";
}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gs-substep">
                        <div class="gs-substep-letter">C</div>
                        <div class="gs-substep-content">
                            <h4>Haz tu primera consulta mágica</h4>
                            <p>Haz clic en el icono de guardar (disquete) y vuelve a tu hoja de cálculo. Escribe esta fórmula en cualquier celda:</p>
                            <div class="gs-formula-box">=APIEMPRESAS("A15075062"; "name")</div>
                            <p style="font-size: 1rem; color: #64748b; margin-top: 4px;">Recuerda poner el nombre del campo <strong>siempre entre comillas</strong>. <br>Campos disponibles: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #334155; font-weight:700;">name, cnae, cnae_label, founded, capital_social_raw, province, municipality, status</code>.</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
function copyScript() {
    const text = document.getElementById('scriptCode').innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btnText = document.getElementById('copyText');
        btnText.innerText = '¡Copiado!';
        setTimeout(() => {
            btnText.innerText = 'Copiar código';
        }, 2000);
    });
}
</script>
    </main>
    <?php if (service('request')->getLocale() === 'en'): ?>
        <?= view('partials/footer_en') ?>
    <?php else: ?>
        <?= view('partials/footer') ?>
    <?php endif; ?>
</body>
</html>
