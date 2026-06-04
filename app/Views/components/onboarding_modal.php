<?php 
$apiKey = 'TU_API_KEY';
if (isset($show_wizard) && $show_wizard && session()->has('user_id')) {
    $db = \Config\Database::connect();
    $keyRow = $db->table('api_keys')->where('user_id', session('user_id'))->where('is_active', 1)->get()->getRow();
    if ($keyRow) {
        $apiKey = $keyRow->api_key;
    }
}
?>
<?php if (isset($show_wizard) && $show_wizard): ?>
<div id="api-wizard-overlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fadeIn 0.3s ease; opacity: 1; transition: opacity 0.3s;">
    
    <!-- STEP 1: INTERACTION -->
    <div id="wizard-step-1" class="wizard-card" style="width: 100%; max-width: 550px; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="background: #eff6ff; color: #2152ff; width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2rem;">⚡</div>
        <h2 style="font-size: 1.75rem; font-weight: 950; color: #0f172a; margin-bottom: 12px; text-align: center;">Tu primera llamada a la API</h2>
        <p style="color: #64748b; font-weight: 600; text-align: center; margin-bottom: 16px; font-size: 0.95rem;">Prueba lo rápido que es obtener los datos oficiales del Registro Mercantil de cualquier empresa.</p>
        
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; font-size: 0.8rem; font-weight: 800; text-align: center; padding: 6px 16px; border-radius: 100px; width: fit-content; margin: 0 auto 32px; display: flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            Tranquilo, esta prueba no consume tus créditos
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Haz clic en una para probar:</label>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
                <button class="wiz-badge" onclick="simulateApi('A15075062', 'Inditex S.A.', 'Arteixo', 'A Coruña')">👕 Inditex</button>
                <button class="wiz-badge" onclick="simulateApi('A46103834', 'Mercadona S.A.', 'Tavernes Blanques', 'Valencia')">🛒 Mercadona</button>
                <button class="wiz-badge" onclick="simulateApi('A28015865', 'Telefónica S.A.', 'Madrid', 'Madrid')">📱 Telefónica</button>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">O usa un CIF</span>
                <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
            </div>
            
            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <input type="text" id="wiz-cif-input" placeholder="Ej: B12345678" style="flex: 1; padding: 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-weight: 700; font-size: 1rem; outline: none;">
                <button onclick="simulateApiManual()" style="background: #2152ff; color: white; border: none; padding: 0 24px; border-radius: 12px; font-weight: 800; cursor: pointer;">Validar</button>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 24px;">
            <button onclick="skipWizard()" style="background: none; border: none; color: #94a3b8; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: underline;">Saltar tutorial</button>
        </div>
    </div>

    <!-- LOADING STATE -->
    <div id="wizard-loading" class="wizard-card" style="display: none; width: 100%; max-width: 400px; background: white; border-radius: 24px; padding: 40px; text-align: center;">
        <div style="width: 48px; height: 48px; border: 4px solid #f1f5f9; border-top-color: #2152ff; border-radius: 50%; margin: 0 auto 24px; animation: api-spin 0.8s linear infinite;"></div>
        <h3 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Conectando...</h3>
        <p style="color: #64748b; font-weight: 600; font-size: 0.9rem;">Consultando fuentes oficiales en tiempo real.</p>
    </div>

    <!-- STEP 2: RESULTS & UPSELL -->
    <div id="wizard-step-2" class="wizard-card" style="display: none; width: 100%; max-width: 1050px; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; align-items: stretch;">
            
            <!-- JSON Response -->
            <div style="display: flex; flex-direction: column;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px rgba(16,185,129,0.5);"></div>
                        <span style="font-weight: 800; color: #0f172a; font-size: 0.9rem;">200 OK - <span id="wiz-res-name">Empresa</span></span>
                    </div>
                    <div style="background: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; font-family: monospace;" id="wiz-json-label">RESPONSE</div>
                </div>
                
                <div style="background: #1e293b; border-radius: 16px; padding: 20px; overflow-y: auto; flex: 1;">
                    <pre id="wiz-json-container" style="margin: 0; color: #e2e8f0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word;"></pre>
                </div>
            </div>

            <!-- Upsell Info -->
            <div style="display: flex; flex-direction: column; justify-content: center;">
                <h2 style="font-size: 1.4rem; font-weight: 950; color: #0f172a; margin-bottom: 8px;">Compara los resultados</h2>
                <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 24px; font-weight: 600;">Estás viendo la respuesta por defecto. <span style="color: #2152ff; font-weight: 800;">Haz clic en el Plan PRO abajo</span> para descubrir los datos ocultos que se desbloquean.</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
                    <!-- Plan Free Toggle -->
                    <div id="wiz-plan-free" onclick="togglePlan('free')" style="display: flex; gap: 16px; padding: 16px; background: #f8fafc; border: 2px solid #cbd5e1; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <div style="font-size: 1.5rem;">🆓</div>
                        <div>
                            <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem;">Plan Free (Actual)</div>
                            <div style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-top: 4px;">Datos básicos. Oculta dirección, objeto social y coordenadas.</div>
                        </div>
                    </div>
                    
                    <!-- Plan Pro Toggle -->
                    <div id="wiz-plan-pro" onclick="togglePlan('pro')" style="display: flex; gap: 16px; padding: 16px; background: #eff6ff; border: 2px solid #2152ff; border-radius: 12px; cursor: pointer; position: relative; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.1);">
                        <div style="position: absolute; top: -10px; right: 16px; background: #2152ff; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase;">Recomendado</div>
                        <div style="font-size: 1.5rem;">🚀</div>
                        <div>
                            <div style="font-weight: 900; color: #1e3a8a; font-size: 0.95rem;">Plan PRO</div>
                            <div style="font-size: 0.8rem; color: #3b82f6; font-weight: 600; margin-top: 4px;">Datos 100% completos, ilimitados y actualizados.</div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button onclick="showCodeSnippets()" style="background: #0f172a; color: white; border: none; text-align: center; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; cursor: pointer;">💻 Ver ejemplos de código (Copiar y pegar)</button>
                    <button onclick="goBackToStep1()" style="background: white; color: #0f172a; border: 2px solid #e2e8f0; text-align: center; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: background 0.2s;">🔄 Probar con otra empresa</button>
                    <button onclick="finishWizard()" style="background: #2152ff; border: none; color: white; font-weight: 900; font-size: 1rem; padding: 16px; border-radius: 12px; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">¡Impresionante! Entrar al Dashboard</button>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3: CODE SNIPPETS -->
    <div id="wizard-step-3" class="wizard-card" style="display: none; width: 100%; max-width: 800px; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h2 style="font-size: 1.75rem; font-weight: 950; color: #0f172a; margin-bottom: 8px;">Copia y pega en tu código</h2>
                <p style="color: #64748b; font-weight: 600;">Ya hemos inyectado tu <strong>API Key real</strong> y el <strong>CIF de prueba</strong> en estos fragmentos.</p>
            </div>
            <button onclick="document.getElementById('wizard-step-3').style.display='none'; document.getElementById('wizard-step-2').style.display='block';" style="background: #f1f5f9; border: none; padding: 10px 16px; border-radius: 10px; font-weight: 800; cursor: pointer; color: #475569;">&larr; Volver atrás</button>
        </div>

        <!-- TABS -->
        <div style="display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; overflow-x: auto;">
            <button onclick="showCodeTab('curl')" id="tab-curl" style="background: #0f172a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">cURL</button>
            <button onclick="showCodeTab('php')" id="tab-php" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">PHP</button>
            <button onclick="showCodeTab('js')" id="tab-js" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Node.js / Fetch</button>
            <button onclick="showCodeTab('python')" id="tab-python" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Python</button>
            <button onclick="showCodeTab('postman')" id="tab-postman" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Postman</button>
        </div>

        <div style="position: relative; background: #1e293b; border-radius: 16px; padding: 24px; overflow-x: auto;">
            <button id="copy-btn" onclick="copySnippet()" style="position: absolute; top: 12px; right: 12px; background: #334155; border: none; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Copiar
            </button>
            
            <pre id="code-curl" class="code-snippet" style="margin: 0; color: #e2e8f0; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.5; display: block;">curl --request GET \
  --url 'https://apiempresas.es/api/v1/companies?cif=<span class="snip-cif">A15075062</span>' \
  --header 'Authorization: Bearer <?= $apiKey ?>'</pre>

            <pre id="code-php" class="code-snippet" style="margin: 0; color: #e2e8f0; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.5; display: none;">&lt;?php
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://apiempresas.es/api/v1/companies?cif=<span class="snip-cif">A15075062</span>",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer <?= $apiKey ?>"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
?&gt;</pre>

            <pre id="code-js" class="code-snippet" style="margin: 0; color: #e2e8f0; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.5; display: none;">fetch("https://apiempresas.es/api/v1/companies?cif=<span class="snip-cif">A15075062</span>", {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer <?= $apiKey ?>'
  }
})
  .then(response => response.json())
  .then(result => console.log(result))
  .catch(error => console.log('error', error));</pre>

            <pre id="code-python" class="code-snippet" style="margin: 0; color: #e2e8f0; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.5; display: none;">import requests

url = "https://apiempresas.es/api/v1/companies?cif=<span class="snip-cif">A15075062</span>"
headers = {"Authorization": "Bearer <?= $apiKey ?>"}

response = requests.get(url, headers=headers)
print(response.json())</pre>

            <div id="code-postman" class="code-snippet" style="display: none; padding: 32px 20px; text-align: center;">
                <h3 style="font-size: 1.25rem; font-weight: 900; color: white; margin-bottom: 12px;">Colección Oficial de Postman</h3>
                <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px; max-width: 450px; margin-left: auto; margin-right: auto; line-height: 1.5;">
                    Descarga nuestra colección pre-configurada para probar todos los endpoints cómodamente desde la aplicación de Postman.
                </p>
                <a href="<?= base_url('public/docs/apiempresas_postman.json') ?>" download style="display: inline-flex; align-items: center; gap: 8px; background: #ff6c37; color: white; padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(255, 108, 55, 0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Descargar Colección
                </a>
            </div>
        </div>

        <div style="margin-top: 32px; text-align: center;">
            <button onclick="finishWizard()" style="background: #2152ff; border: none; color: white; font-weight: 900; font-size: 1.1rem; padding: 18px 32px; border-radius: 12px; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">🚀 ¡Todo listo! Ir al Dashboard</button>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes api-spin { to { transform: rotate(360deg); } }
    .wiz-badge { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 100px; padding: 8px 16px; font-size: 0.9rem; color: #0f172a; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .wiz-badge:hover { background: #e0e7ff; color: #2152ff; border-color: #a5b4fc; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(33, 82, 255, 0.1); }
    
    /* JSON Syntax Highlighting */
    .json-key { color: #38bdf8; }
    .json-string { color: #a3e635; }
    .json-number { color: #f472b6; }
    .json-boolean { color: #fbbf24; }
    .json-null { color: #94a3b8; }
</style>

<script>
    let currentCompany = {};

    function syntaxHighlight(json) {
        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'json-number';
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

    function simulateApi(cif, name, municipality, province) {
        document.getElementById('wizard-step-1').style.display = 'none';
        document.getElementById('wizard-loading').style.display = 'block';
        
        currentCompany = { cif: cif, name: name, municipality: municipality || 'Madrid', province: province || 'Madrid' };
        document.getElementById('wiz-res-name').innerText = name;

        // Inyectar el CIF en los códigos de ejemplo del Paso 3
        document.querySelectorAll('.snip-cif').forEach(el => el.innerText = cif);

        setTimeout(() => {
            document.getElementById('wizard-loading').style.display = 'none';
            document.getElementById('wizard-step-2').style.display = 'block';
            togglePlan('free'); // Mostrar Free por defecto para no confundir
        }, 1200);
    }

    function simulateApiManual() {
        const cif = document.getElementById('wiz-cif-input').value.trim();
        if (!cif) {
            document.getElementById('wiz-cif-input').style.borderColor = '#ef4444';
            return;
        }
        simulateApi(cif, "Empresa " + cif, "Localidad", "Provincia");
    }

    function goBackToStep1() {
        document.getElementById('wizard-step-2').style.display = 'none';
        document.getElementById('wizard-step-1').style.display = 'block';
        document.getElementById('wiz-cif-input').value = '';
        document.getElementById('wiz-cif-input').style.borderColor = '#cbd5e1';
    }

    function showCodeSnippets() {
        document.getElementById('wizard-step-2').style.display = 'none';
        document.getElementById('wizard-step-3').style.display = 'block';
    }

    function showCodeTab(lang) {
        document.querySelectorAll('.code-snippet').forEach(el => el.style.display = 'none');
        document.getElementById('code-' + lang).style.display = 'block';
        
        if (lang === 'postman') {
            document.getElementById('copy-btn').style.display = 'none';
        } else {
            document.getElementById('copy-btn').style.display = 'flex';
        }

        const tabs = ['curl', 'php', 'js', 'python', 'postman'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            if (t === lang) {
                btn.style.background = '#0f172a';
                btn.style.color = 'white';
            } else {
                btn.style.background = '#f1f5f9';
                btn.style.color = '#475569';
            }
        });
    }

    function copySnippet() {
        const visibleSnippet = Array.from(document.querySelectorAll('.code-snippet')).find(el => el.style.display === 'block');
        if (visibleSnippet) {
            const textToCopy = visibleSnippet.innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                alert('¡Código copiado al portapapeles!');
            }).catch(err => {
                console.error('Error al copiar: ', err);
            });
        }
    }

    function togglePlan(planType) {
        const freeBtn = document.getElementById('wiz-plan-free');
        const proBtn = document.getElementById('wiz-plan-pro');
        const label = document.getElementById('wiz-json-label');

        let dataObj = {};

        if (planType === 'free') {
            freeBtn.style.borderColor = '#2152ff';
            freeBtn.style.backgroundColor = '#eff6ff';
            freeBtn.style.boxShadow = '0 10px 15px -3px rgba(33, 82, 255, 0.1)';
            
            proBtn.style.borderColor = '#cbd5e1';
            proBtn.style.backgroundColor = '#f8fafc';
            proBtn.style.boxShadow = 'none';

            label.innerText = 'FREE RESPONSE';
            label.style.backgroundColor = '#f1f5f9';
            label.style.color = '#475569';

            dataObj = {
                "success": true,
                "data": {
                    "name": currentCompany.name,
                    "cif": currentCompany.cif,
                    "cnae": "4771",
                    "cnae_label": "Comercio al por menor",
                    "cnae_2025": "4771",
                    "cnae_2025_label": "Comercio al por menor",
                    "corporate_purpose": "La importación, exportación, fabricación, comercialización y venta al p... [ACTUALIZA A PRO PARA VER EL DETALLE COMPLETO]",
                    "founded": "1985-06-12",
                    "province": currentCompany.province,
                    "address": "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]",
                    "municipality": currentCompany.municipality,
                    "status": "Activa",
                    "upsell_opportunities": {
                        "campos_ocultos": [
                            "direccion_completa",
                            "objeto_social_completo",
                            "geolocalizacion_lat_lng"
                        ],
                        "mensaje": "🔒 Pásate al plan Pro para desbloquear la ubicación y los datos societarios completos de esta empresa."
                    }
                }
            };
        } else {
            proBtn.style.borderColor = '#2152ff';
            proBtn.style.backgroundColor = '#eff6ff';
            proBtn.style.boxShadow = '0 10px 15px -3px rgba(33, 82, 255, 0.1)';
            
            freeBtn.style.borderColor = '#cbd5e1';
            freeBtn.style.backgroundColor = '#f8fafc';
            freeBtn.style.boxShadow = 'none';

            label.innerText = 'PRO RESPONSE';
            label.style.backgroundColor = '#dbeafe';
            label.style.color = '#1d4ed8';

            dataObj = {
                "success": true,
                "data": {
                    "name": currentCompany.name,
                    "cif": currentCompany.cif,
                    "cnae": "4771",
                    "cnae_label": "Comercio al por menor",
                    "cnae_2025": "4771",
                    "cnae_2025_label": "Comercio al por menor",
                    "corporate_purpose": "La importación, exportación, fabricación, comercialización y venta al por menor y al por mayor de artículos de vestir y complementos.",
                    "founded": "1985-06-12",
                    "province": currentCompany.province,
                    "address": "AVENIDA DE LA DIPUTACION (EDIFICIO PRINCIPAL), S/N",
                    "municipality": currentCompany.municipality,
                    "lat": 43.3155,
                    "lng": -8.5022,
                    "status": "Activa"
                }
            };
        }

        document.getElementById('wiz-json-container').innerHTML = syntaxHighlight(dataObj);
    }

    function markWizardDone() {
        fetch('<?= site_url('dashboard/complete-wizard') ?>', {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        }).catch(err => console.error('Error marking wizard done:', err));
    }

    function finishWizard() {
        markWizardDone();
        const overlay = document.getElementById('api-wizard-overlay');
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }

    function skipWizard() {
        finishWizard();
    }
</script>
<?php endif; ?>
