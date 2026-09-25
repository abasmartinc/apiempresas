<!-- PASO 3: INTEGRACIÓN -->
<section id="section-paso3" class="activation-main-card" style="margin-top: 32px; position: relative; ">
    <div style="position: absolute; top: -14px; left: 32px; background: #2152ff; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"><?= lang('Dashboard.step_3') ?></div>
    <div style="margin-top: 10px;">
        <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 8px !important;"><?= lang('Dashboard.integrate_system') ?></h2>
        <p style="font-size: 0.95rem; color: #64748b; font-weight: 600; margin: 0 0 20px;">
            <?= lang('Dashboard.test_connection', [site_url('documentation')]) ?>
        </p>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="display: flex; gap: 8px; overflow-x: auto;" id="snippetTabs">
                <button class="snippet-tab active" data-target="curl">cURL</button>
                <button class="snippet-tab" data-target="php">PHP</button>
                <button class="snippet-tab" data-target="node">Node.js</button>
                <button class="snippet-tab" data-target="python">Python</button>
                <button class="snippet-tab" data-target="postman">Postman</button>
            </div>
            <button id="btnCopySnippet" style="background: none; border: none; color: #2152ff; font-weight: 800; font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; gap: 4px;"><?= lang('Dashboard.copy_code') ?></button>
        </div>
        
        <style>
            .snippet-tab { background: none; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; color: #64748b; cursor: pointer; transition: all 0.2s; }
            .snippet-tab:hover { color: #0f172a; background: #f1f5f9; }
            .snippet-tab.active { background: #1e293b; color: white; }
            .snippet-code { display: none; background: #1e293b; color: #e2e8f0; padding: 16px; border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; overflow-x: auto; white-space: pre; border: 1px solid #334155; }
            .snippet-code.active { display: block; }
        </style>

        <div id="snippet-curl" class="snippet-code active"><span style="color: #94a3b8;">curl -X GET</span> "<?= site_url('api/v1/companies') ?>?cif=A15075062" \
-H <span style="color: #12b48a;">"X-API-KEY: <?= htmlspecialchars($api_key->api_key ?? 'TU_API_KEY') ?>"</span></div>
        
        <div id="snippet-php" class="snippet-code"><span style="color: #94a3b8;">&lt;?php</span>
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => '<span style="color: #12b48a;"><?= site_url('api/v1/companies') ?>?cif=A15075062</span>',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HTTPHEADER => array(
<span style="color: #12b48a;">"X-API-KEY: <?= htmlspecialchars($api_key->api_key ?? 'TU_API_KEY') ?>"</span>
),
));
$response = curl_exec($curl);
$data = json_decode($response, true);
print_r($data);</div>
        
        <div id="snippet-node" class="snippet-code"><span style="color: #94a3b8;">const</span> fetch = require(<span style="color: #12b48a;">'node-fetch'</span>);

fetch(<span style="color: #12b48a;">'<?= site_url('api/v1/companies') ?>?cif=A15075062'</span>, {
headers: {
<span style="color: #12b48a;">'X-API-KEY'</span>: <span style="color: #12b48a;">'<?= htmlspecialchars($api_key->api_key ?? 'TU_API_KEY') ?>'</span>
}
})
.then(res => res.json())
.then(data => console.log(data));</div>

        <div id="snippet-python" class="snippet-code"><span style="color: #94a3b8;">import</span> requests

url = <span style="color: #12b48a;">"<?= site_url('api/v1/companies') ?>?cif=A15075062"</span>
headers = {
<span style="color: #12b48a;">"X-API-KEY"</span>: <span style="color: #12b48a;">"<?= htmlspecialchars($api_key->api_key ?? 'TU_API_KEY') ?>"</span>
}

response = requests.get(url, headers=headers)
print(response.json())</div>

        <div id="snippet-postman" class="snippet-code" style="text-align: center; padding: 32px 20px; white-space: normal;">
            <h3 style="font-size: 1.25rem; font-weight: 900; color: white; margin-bottom: 12px;"><?= lang('Wizard.postman_title') ?></h3>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px; max-width: 450px; margin-left: auto; margin-right: auto; line-height: 1.5;">
                <?= lang('Wizard.postman_desc') ?>
            </p>
            <a href="<?= base_url('public/docs/apiempresas_postman.json') ?>" download style="display: inline-flex; align-items: center; gap: 8px; background: #ff6c37; color: white; padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(255, 108, 55, 0.3);">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <?= lang('Wizard.download_collection') ?>
            </a>
        </div>

        <!-- Atajos para integrar: sandbox (no gasta consultas) y SDK oficiales -->
        <div class="dash-dev-links">
            <div class="dash-dev-card">
                <span class="dash-dev-kicker">Sandbox · gratis</span>
                <p>Prueba tu código sin gastar consultas: misma clave, base <code>/api/sandbox/v1</code> y CIF <code>A15075062</code>.</p>
                <a href="<?= site_url('documentation#auth') ?>">Ver cómo usarlo &rarr;</a>
            </div>
            <div class="dash-dev-card">
                <span class="dash-dev-kicker">SDK oficiales</span>
                <p class="dash-dev-cmds"><code>composer require apiempresas/apiempresas-php</code><code>npm install apiempresas</code><code>pip install apiempresas</code></p>
                <a href="<?= site_url('documentation#sdks') ?>">Ver los SDK &rarr;</a>
            </div>
        </div>
        <style>
            .dash-dev-links { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
            .dash-dev-card { padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; }
            .dash-dev-kicker { display: block; margin-bottom: 4px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #2152ff; }
            .dash-dev-card p { margin: 0 0 8px; font-size: 0.82rem; color: #475569; font-weight: 600; line-height: 1.45; }
            .dash-dev-card code { background: #e2e8f0; color: #0f172a; padding: 1px 5px; border-radius: 4px; font-size: 0.74rem; white-space: nowrap; }
            .dash-dev-cmds { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
            .dash-dev-cmds code { max-width: 100%; overflow-x: auto; }
            .dash-dev-card a { font-size: 0.8rem; font-weight: 800; color: #2152ff; text-decoration: none; }
            @media (max-width: 720px) { .dash-dev-links { grid-template-columns: 1fr; } }
        </style>
    </div>
    <script>
        document.querySelectorAll('.snippet-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.snippet-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.snippet-code').forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById('snippet-' + tab.dataset.target).classList.add('active');
                
                if(tab.dataset.target === 'postman') {
                    document.getElementById('btnCopySnippet').style.display = 'none';
                } else {
                    document.getElementById('btnCopySnippet').style.display = 'flex';
                }
            });
        });

        document.getElementById('btnCopySnippet').addEventListener('click', () => {
            const activeSnippet = document.querySelector('.snippet-code.active');
            let code = activeSnippet.innerText || activeSnippet.textContent;
            navigator.clipboard.writeText(code);
            if (window.trackEvent) trackEvent('snippet_copied', { type: document.querySelector('.snippet-tab.active').dataset.target });
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '<?= lang('Dashboard.code_copied') ?>', showConfirmButton: false, timer: 1500 });
        });
    </script>
</section>
