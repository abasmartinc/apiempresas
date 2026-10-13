<!doctype html>
<html lang="en">

<head>
    <?= view('partials/head', [
        'title'       => 'Spanish Company Data API: CIF Lookup, BORME & Directors | SpainCompanyAPI',
        'excerptText' => 'REST API for Spanish company data: validate a CIF, check if a company is active, get directors and BORME filings. 100 free requests, no card required.',
    ]) ?>
    <!-- Fonts handled by partials/head -->
    <?php $cssV = static fn($f) => is_file(FCPATH . $f) ? filemtime(FCPATH . $f) : '1'; // como la home española: time() impedía cachear el CSS ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= $cssV('public/css/home.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= $cssV('public/css/home-mobile.css') ?>" media="screen and (max-width: 768px)">
</head>

<body>

    <?= view('partials/header_en') ?>

    <main>

        <!-- 1. HERO -->
        <section class="hero container" data-track-section="hero">
            <h1 class="reveal">
                Spanish Company Data API:
                <span class="gradient-text">CIF lookup, directors and BORME filings</span>
            </h1>
            <p class="reveal delay-1">Look up any Spanish company by its CIF (tax ID) or name and get its legal name, status, incorporation date, CNAE activity code, registered address, directors and BORME filings in a single REST call, then track their changes through the API or webhooks. Built for KYB, customer onboarding and ERP or CRM integrations.</p>

            <div class="hero-btns reveal delay-3">
                <a href="<?= site_url('register?intent=api&plan=free&source=en_home_hero') ?>" class="btn-ae btn-ae-primary" data-track-event="hero_cta_click" data-track-metadata='{"cta_text": "Get a free API key", "plan": "free", "source_block": "hero", "page_type": "home_en"}'>Get a free API key</a>
                <a href="#buscar" class="btn-ae btn-ae-outline" data-track-event="hero_cta_click" data-track-metadata='{"cta_text": "Try it live", "source_block": "hero", "page_type": "home_en"}'>Try it live</a>
            </div>

            <span class="trust-tag reveal delay-3"><?= (int) $freeLimit ?> free requests · No card required · Data from the BORME (Commercial Registry gazette)</span>

            <?php
            // Real figures (App\Libraries\PublicStats, 12 h cache), rounded down so "over" is always true.
            $fmtOver = static function (int $n, string $what): string {
                if ($n >= 1000000) {
                    $m = floor($n / 100000) / 10;
                    return '<strong>Over ' . number_format($m, $m == floor($m) ? 0 : 1, '.', ',') . ' million</strong> ' . esc($what);
                }
                if ($n >= 1000) {
                    return '<strong>Over ' . number_format(floor($n / 1000) * 1000, 0, '.', ',') . '</strong> ' . esc($what);
                }
                return '<strong>' . $n . '</strong> ' . esc($what);
            };
            $hs = $homeStats ?? [];
            ?>
            <?php if (!empty($hs['companies']) && !empty($hs['acts'])): ?>
            <ul class="hero-stats reveal delay-3" aria-label="Database coverage">
                <li><?= $fmtOver((int) $hs['companies'], 'Spanish companies') ?></li>
                <li><?= $fmtOver((int) $hs['acts'], 'BORME filings') ?></li>
                <?php if (!empty($hs['last_borme'])): ?>
                <li>Latest BORME processed: <strong><?= date('j M Y', strtotime($hs['last_borme'])) ?></strong> (published every working day)</li>
                <?php endif; ?>
            </ul>
            <style>
                .hero-stats { list-style: none; padding: 0; margin: 22px auto 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 28px; font-size: 0.92rem; color: var(--ae-slate); }
                .hero-stats li { position: relative; }
                .hero-stats li + li::before { content: "·"; position: absolute; left: -17px; color: #94a3b8; }
                .hero-stats strong { color: var(--ae-dark); font-weight: 800; }
                @media (max-width: 640px) { .hero-stats { flex-direction: column; align-items: center; gap: 6px; } .hero-stats li + li::before { display: none; } }
            </style>
            <?php endif; ?>

            <!-- First call: copyable example (same header and base URL as the docs) -->
            <div class="hero-curl reveal delay-3" data-track-section="hero_curl">
                <div class="code-editor-window">
                    <div class="editor-header">
                        <div class="dots">
                            <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                        </div>
                        <div class="hc-tabs" role="tablist" aria-label="Example language">
                            <button type="button" class="hc-tab active" role="tab" aria-selected="true" data-lang="curl">curl</button>
                            <button type="button" class="hc-tab" role="tab" aria-selected="false" data-lang="node">Node.js</button>
                            <button type="button" class="hc-tab" role="tab" aria-selected="false" data-lang="python">Python</button>
                            <button type="button" class="hc-tab" role="tab" aria-selected="false" data-lang="php">PHP</button>
                        </div>
                        <button type="button" class="hero-curl-copy" id="heroCurlCopy" aria-label="Copy the example">Copy</button>
                    </div>
                    <div class="editor-body">
<pre class="hc-code" data-lang="curl"><code id="heroCurlCode"><span class="hc-cmd">curl</span> <span class="hc-str">"https://apiempresas.es/api/v1/companies?cif=A46103834"</span> \
  <span class="hc-flag">-H</span> <span class="hc-str">"X-API-KEY: YOUR_API_KEY"</span></code></pre>
<pre class="hc-code" data-lang="node" hidden><code><span class="hc-muted">// npm install apiempresas</span>
<span class="hc-cmd">import</span> { ApiEmpresas } <span class="hc-cmd">from</span> <span class="hc-str">'apiempresas'</span>;

<span class="hc-cmd">const</span> api = <span class="hc-cmd">new</span> ApiEmpresas({ apiKey: <span class="hc-str">'YOUR_API_KEY'</span> });
<span class="hc-cmd">const</span> company = <span class="hc-cmd">await</span> api.companies.get(<span class="hc-str">'A46103834'</span>);
console.log(company.name);</code></pre>
<pre class="hc-code" data-lang="python" hidden><code><span class="hc-muted"># pip install apiempresas</span>
<span class="hc-cmd">from</span> apiempresas <span class="hc-cmd">import</span> ApiEmpresas

api = ApiEmpresas(<span class="hc-str">'YOUR_API_KEY'</span>)
company = api.companies.get(<span class="hc-str">'A46103834'</span>)
print(company[<span class="hc-str">'name'</span>])</code></pre>
<pre class="hc-code" data-lang="php" hidden><code><span class="hc-muted">// composer require apiempresas/apiempresas-php</span>
<span class="hc-cmd">require</span> <span class="hc-str">'vendor/autoload.php'</span>;
<span class="hc-cmd">use</span> ApiEmpresas\ApiEmpresas;

$api = <span class="hc-cmd">new</span> ApiEmpresas(<span class="hc-str">'YOUR_API_KEY'</span>);
$company = $api-&gt;companies-&gt;get(<span class="hc-str">'A46103834'</span>);
<span class="hc-cmd">echo</span> $company[<span class="hc-str">'name'</span>];</code></pre>
<pre class="hc-res"><code><span class="hc-muted"># Response (shortened)</span>
{ <span class="hc-key">"success"</span>: <span class="hc-bool">true</span>, <span class="hc-key">"data"</span>: { <span class="hc-key">"cif"</span>: <span class="hc-val">"A46103834"</span>, <span class="hc-key">"name"</span>: <span class="hc-val">"MERCADONA SA"</span>, <span class="hc-key">"status"</span>: <span class="hc-val">"Activa"</span>, … } }</code></pre>
                    </div>
                </div>
                <p class="hero-curl-note">Sign up for free, then replace <code>A46103834</code> with any CIF and <code>YOUR_API_KEY</code> with your key. <a href="<?= site_url('docs#sdks') ?>">See PHP, Node.js and Python examples →</a></p>
            </div>
            <style>
                .hero-curl { max-width: 760px; margin: 40px auto 0; text-align: left; }
                .hero-curl .code-editor-window:hover { transform: none; }
                .hero-curl .editor-body { padding: 18px 22px; font-size: 0.9rem; }
                .hero-curl pre { margin: 0; white-space: pre; }
                .hero-curl .hc-res { margin-top: 14px; padding-top: 14px; border-top: 1px dashed rgba(255,255,255,0.12); white-space: pre-wrap; word-break: break-word; }
                .hero-curl .hc-cmd { color: #82AAFF; font-weight: 700; }
                .hero-curl .hc-flag { color: #C792EA; }
                .hero-curl .hc-str, .hero-curl .hc-val { color: #C3E88D; }
                .hero-curl .hc-key { color: #F07178; }
                .hero-curl .hc-bool { color: #FFCB6B; }
                .hero-curl .hc-muted { color: #64748B; }
                .hero-curl code { color: #E2E8F0; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; }
                .hero-curl-copy { margin-left: auto; background: rgba(255,255,255,0.08); color: #E2E8F0; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
                .hero-curl-copy:hover { background: rgba(255,255,255,0.16); }
                .hero-curl .editor-header { flex-wrap: wrap; gap: 8px; }
                .hero-curl .hc-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
                .hero-curl .hc-tab { background: transparent; color: #94A3B8; border: 0; border-radius: 6px; padding: 4px 10px; font-size: 0.8rem; font-weight: 700; cursor: pointer; font-family: inherit; }
                .hero-curl .hc-tab:hover { color: #E2E8F0; }
                .hero-curl .hc-tab.active { background: rgba(255,255,255,0.1); color: #E2E8F0; }
                .hero-curl pre.hc-code[hidden] { display: none; }
                @media (max-width: 520px) { .hero-curl .editor-header .dots { display: none; } }
                .hero-curl-note { font-size: 0.9rem !important; margin: 14px 0 0 !important; max-width: none !important; text-align: center; color: var(--ae-slate); }
                .hero-curl-note code { background: #eef2ff; color: #1e3a8a; padding: 1px 6px; border-radius: 6px; font-size: 0.85em; }
                .hero-curl-note a { color: var(--ae-blue); font-weight: 700; text-decoration: none; }
                @media (max-width: 768px) {
                    .hero-curl .editor-header .tab { display: none; }
                    .hero-curl .editor-body { font-size: 0.78rem; padding: 14px; }
                }
            </style>
        </section>

        <!-- 2. BLOQUE DE BÚSQUEDA -->
        <section id="buscar" class="search-section container" data-track-section="search_block">
            <div class="search-panel reveal delay-3">
                <div style="text-align: center;">
                    <div class="badge-intro">
                        <div class="dot-live"></div>
                        Live demo
                    </div>
                </div>
                <h2>Try the API: <span class="highlight">look up a Spanish company</span></h2>
                <p class="subtitle">Search by CIF or company name and see the same data the API returns, built from the BORME and other Spanish public sources.</p>
                
                <div class="search-form-wrapper">
                    <div class="search-form">
                        <input type="text" id="q" class="search-input" placeholder="Ex: B12345678 or Company Name" aria-label="Company search">
                        <button id="btnBuscar" class="btn-ae btn-ae-primary" style="height: 72px; padding: 0 48px; border-radius: 14px; font-size: 1.15rem;">Validate Company Now</button>
                    </div>
                </div>
                
                <?php if (!empty($socialProofText)): ?>
                    <div class="social-proof-wrapper">
                        <div class="social-proof-counter">
                            <span class="social-proof-dot"></span>
                            <span><?= esc($socialProofText) ?></span>
                        </div>
                    </div>
                <?php endif; ?>


                <div id="resultado_container" style="display:none; margin-top: 24px;">
                    <div id="resultado"></div>
                </div>

                <div class="search-benefits">
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>CIF check digit validation</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Updated with every BORME</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Directors and filings</span>
                    </div>
                </div>
                <p style="text-align: center; font-size: 1rem; color: var(--ae-slate); margin-top: 16px; font-weight: 600;">Looking for API documentation? <a href="<?= site_url('docs') ?>" style="color: var(--ae-blue); font-weight: 800; text-decoration: none; border-bottom: 2px solid rgba(37, 99, 235, 0.2); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--ae-blue)'" onmouseout="this.style.borderColor='rgba(37, 99, 235, 0.2)'" data-cta="radar_home" data-source="home_search">View Docs.</a></p>
            </div>
        </section>

        <!-- 2b. SOURCES (13-10-2026): right after the live lookup -->
        <section class="band sources-band" data-track-section="sources">
            <div class="container">
                <div class="src-wrap reveal">
                    <div class="src-head">
                        <h3>Where the data comes from</h3>
                        <p>Every record matched by CIF, with each source&#8217;s update frequency.</p>
                    </div>
                    <div class="src-grid">
                        <article class="src-tile">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg></span>
                                <span class="src-freq">Every working day</span>
                            </div>
                            <h4>BORME (Boletín Oficial del Registro Mercantil)</h4>
                            <p>The official gazette of Spain's Commercial Registry: incorporations, director appointments and resignations, changes of address, purpose or capital, dissolutions, insolvencies and closures. We process every issue, every day.</p>
                            <div class="src-tags"><span>BOE</span></div>
                        </article>
                        <article class="src-tile">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="2" y1="13" x2="22" y2="13"/></svg></span>
                                <span class="src-freq">Every day</span>
                            </div>
                            <h4>Public contracts</h4>
                            <p>The Spanish Public Procurement Platform and TED (Official Journal of the EU): contracts awarded to each company, with contracting authority, amounts and dates.</p>
                            <div class="src-tags"><span>PLACSP</span><span>TED</span></div>
                        </article>
                        <article class="src-tile">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M15.5 8.5a4 4 0 1 0 0 7"/><line x1="7" y1="11" x2="13" y2="11"/><line x1="7" y1="13.5" x2="12" y2="13.5"/></svg></span>
                                <span class="src-freq">Every day</span>
                            </div>
                            <h4>Public grants</h4>
                            <p>The Spanish National Grants Database (BDNS): grants awarded to each company, with granting body and amount.</p>
                            <div class="src-tags"><span>BDNS</span><span>Kit Digital</span></div>
                        </article>
                        <article class="src-tile">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V10"/><path d="M19 21V10"/><path d="M9 21V10"/><path d="M15 21V10"/><path d="M2 10l10-6 10 6"/></svg></span>
                                
                            </div>
                            <h4>Other official registries</h4>
                            <p>LEI codes (GLEIF), EU VAT numbers checked live (VIES), regional registries of associations and cooperatives, and CartoCiudad coordinates (IGN).</p>
                            <div class="src-tags"><span>GLEIF</span><span>VIES</span><span>CartoCiudad</span></div>
                        </article>
                        <article class="src-tile">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 0 20a15 15 0 0 1 0-20z"/></svg></span>
                                
                            </div>
                            <h4>The company's own website</h4>
                            <p>Website, phone and generic email, only when the website shows the company's CIF, as Spanish law requires in the legal notice.</p>
                            <div class="src-tags"><span>Legal notice</span></div>
                        </article>
                        <article class="src-tile src-tile-calc">
                            <div class="src-top">
                                <span class="src-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="11" x2="8" y2="11.01"/><line x1="12" y1="11" x2="12" y2="11.01"/><line x1="16" y1="11" x2="16" y2="11.01"/><line x1="8" y1="15" x2="8" y2="15.01"/><line x1="12" y1="15" x2="12" y2="15.01"/><line x1="16" y1="15" x2="16" y2="18"/></svg></span>
                                <span class="src-freq src-freq-calc">Computed</span>
                            </div>
                            <h4>Data computed by SpainCompanyAPI</h4>
                            <p>Registry status, commercial score, risk profile and estimated size are calculated from those sources. They are not an official certificate from the Commercial Registry.</p>
                        </article>
                    </div>
                    <div class="src-note">
                        <span>Some address, contact and size data is completed from other public sources.</span>
                        <a href="<?= site_url('data-sources') ?>" data-track-event="sources_click" data-track-metadata='{"page_type": "home_en"}'>See all sources and their licences <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
            <style>
                .sources-band .src-wrap { background: #fff; border: 1px solid var(--ae-border); border-radius: 24px; padding: 32px; box-shadow: 0 10px 30px -18px rgba(15, 23, 42, 0.18); }
                .sources-band .src-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 6px 24px; margin-bottom: 24px; }
                .sources-band .src-head h3 { font-size: 1.35rem; font-weight: 900; color: var(--ae-dark); margin: 0; letter-spacing: -0.02em; }
                .sources-band .src-head p { margin: 0; color: var(--ae-slate); font-size: 0.95rem; }
                .sources-band .src-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
                .sources-band .src-tile { background: #f8fafc; border: 1px solid #eef2f7; border-radius: 16px; padding: 18px 20px; display: flex; flex-direction: column; transition: border-color .2s, box-shadow .2s, transform .2s; }
                .sources-band .src-tile:hover { border-color: #bfdbfe; box-shadow: 0 8px 20px -14px rgba(37, 99, 235, 0.45); transform: translateY(-1px); }
                .sources-band .src-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
                .sources-band .src-ico { width: 38px; height: 38px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; flex-shrink: 0; }
                .sources-band .src-freq { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 0.72rem; font-weight: 800; white-space: nowrap; }
                .sources-band .src-freq::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }
                .sources-band .src-tile h4 { font-size: 1rem; font-weight: 850; color: var(--ae-dark); margin: 0 0 6px; line-height: 1.3; }
                .sources-band .src-tile p { color: var(--ae-slate); font-size: 0.9rem; line-height: 1.55; margin: 0; }
                .sources-band .src-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: auto; padding-top: 14px; }
                .sources-band .src-tags span { font-size: 0.72rem; font-weight: 700; color: #334155; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px; }
                .sources-band .src-tile-calc { background: #fff; border: 1px dashed #cbd5e1; }
                .sources-band .src-tile-calc .src-ico { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
                .sources-band .src-freq-calc { background: #f1f5f9; color: #475569; }
                .sources-band .src-freq-calc::before { background: #94a3b8; }
                .sources-band .src-note { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px 24px; margin-top: 20px; padding: 14px 18px; border-radius: 14px; background: #f8fafc; color: var(--ae-slate); font-size: 0.9rem; }
                .sources-band .src-note a { color: var(--ae-blue); font-weight: 800; text-decoration: none; white-space: nowrap; }
                .sources-band .src-note a:hover { text-decoration: underline; }
                @media (max-width: 960px) { .sources-band .src-grid { grid-template-columns: repeat(2, 1fr); } }
                @media (max-width: 600px) {
                    .sources-band .src-wrap { padding: 22px 18px; border-radius: 20px; }
                    .sources-band .src-grid { grid-template-columns: 1fr; gap: 10px; }
                    .sources-band .src-note a { white-space: normal; }
                }
            </style>
        </section>

        <!-- 6. WHAT THE API DOES (13-10-2026): endpoint map with plans. Replaces the JSON sample,
             which repeated the hero; the H2 and its paragraph stay. -->
        <section class="band band-light api-map" data-track-section="api_map">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-green reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        What you can ask the API
                    </div>
                    <h2 class="reveal delay-1">A REST API with data from the <span class="gradient-text">Commercial Registry and the BORME</span></h2>
                    <p class="reveal delay-1">Bring the data of any Spanish company into your sign-up flows, forms or internal tools. JSON responses, standard HTTP status codes, an OpenAPI specification and SDKs for PHP, Node.js and Python.</p>
                </div>
                <ul class="api-map-grid">
                    <li class="api-map-card">
                        <code>GET /companies</code>
                        <h3>Company data by CIF</h3>
                        <p>Legal name, status, CNAE code, registered address and directors.</p>
                        <span class="api-map-plan">Free · address and directors on Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/search</code>
                        <h3>Search by name</h3>
                        <p>The best match or a paginated list of candidates.</p>
                        <span class="api-map-plan">Free</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/verify</code>
                        <h3>KYB verification</h3>
                        <p>Whether it exists, whether it is active and who runs it, in one call.</p>
                        <span class="api-map-plan">Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>POST /companies/batch · /reconcile</code>
                        <h3>Batches and name to CIF</h3>
                        <p>Up to 100 CIFs per request, or your list of names matched to CIFs.</p>
                        <span class="api-map-plan">Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/borme</code>
                        <h3>BORME filing history</h3>
                        <p>Every published filing: appointments, resignations, capital, dissolutions…</p>
                        <span class="api-map-plan">Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/network</code>
                        <h3>Corporate network</h3>
                        <p>Companies linked through their directors.</p>
                        <span class="api-map-plan">Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/score · /signals</code>
                        <h3>Commercial score and signals</h3>
                        <p>A 0-100 priority with its reasons and the latest BORME filing, linked to the official PDF.</p>
                        <span class="api-map-plan">Free number only · full on Pro</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/radar</code>
                        <h3>Newly incorporated companies</h3>
                        <p>Today's incorporations, by province, CNAE code and priority.</p>
                        <span class="api-map-plan">Free 10 · Pro 100 · Business 1,000</span>
                    </li>
                    <li class="api-map-card">
                        <code>/watchlist · /webhooks</code>
                        <h3>Monitoring and webhooks</h3>
                        <p>Alerts when a monitored company files in the BORME, changes status or risk level.</p>
                        <span class="api-map-plan">Pro 100 companies · Business 1,000 and webhooks</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/risk-profile</code>
                        <h3>Risk profile</h3>
                        <p>Level, score, dimensions and alerts calculated from the BORME.</p>
                        <span class="api-map-plan">Business</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/contracts</code>
                        <h3>Public contracts</h3>
                        <p>Awards from the Public Procurement Platform: contracting body, amounts and dates.</p>
                        <span class="api-map-plan">Business</span>
                    </li>
                    <li class="api-map-card">
                        <code>GET /companies/filter</code>
                        <h3>Company segments</h3>
                        <p>Companies by province, CNAE code, size and status. The count is free.</p>
                        <span class="api-map-plan">Business</span>
                    </li>
                </ul>
                <div class="api-map-cta">
                    <a href="<?= site_url('api/reference') ?>" class="btn-ae btn-ae-primary" data-track-event="api_map_click" data-track-metadata='{"target": "reference", "page_type": "home_en"}'>Open the API reference</a>
                    <a href="<?= site_url('docs') ?>" class="api-map-doc" data-track-event="api_map_click" data-track-metadata='{"target": "documentation", "page_type": "home_en"}'>Read the docs →</a>
                </div>
            </div>
            <style>
                .api-map .api-map-grid { list-style: none; padding: 0; margin: 40px 0 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
                .api-map .api-map-card { background: #fff; border: 1px solid var(--ae-border); border-radius: 16px; padding: 20px 22px; display: flex; flex-direction: column; }
                .api-map .api-map-card code { align-self: flex-start; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; font-size: 0.78rem; font-weight: 700; color: #1e3a8a; background: #eef2ff; padding: 3px 8px; border-radius: 6px; word-break: break-word; }
                .api-map .api-map-card h3 { font-size: 1.05rem; font-weight: 850; color: var(--ae-dark); margin: 12px 0 6px; }
                .api-map .api-map-card p { color: var(--ae-slate); font-size: 0.92rem; line-height: 1.5; margin: 0 0 14px; }
                .api-map .api-map-plan { margin-top: auto; font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #047857; }
                .api-map .api-map-cta { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 14px 28px; margin-top: 36px; }
                .api-map .api-map-doc { color: var(--ae-blue); font-weight: 800; text-decoration: none; }
                .api-map .api-map-doc:hover { text-decoration: underline; }
                @media (max-width: 960px) { .api-map .api-map-grid { grid-template-columns: repeat(2, 1fr); } }
                @media (max-width: 600px) { .api-map .api-map-grid { grid-template-columns: 1fr; gap: 10px; margin-top: 28px; } .api-map .api-map-card { padding: 16px 18px; } .api-map .api-map-card h3 { margin-top: 10px; } .api-map .api-map-cta .btn-ae { width: 100%; justify-content: center; text-align: center; } }
            </style>
        </section>
        <!-- 3. USE CASES -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-blue reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Use cases
                    </div>
                    <h2 class="reveal delay-1">What teams use the <span class="gradient-text">Spanish company API</span> for</h2>
                    <p class="reveal delay-2">Three processes where Commercial Registry and BORME data save manual work and errors from the first call.</p>
                </div>

                <div class="grid-3">
                    <div class="feature-card use-case card-blue reveal delay-1">
                        <div class="use-case-header">
                            <div class="use-case-header-top">
                                <div class="use-case-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
                                </div>
                                <span class="use-case-badge">
                                    <span class="use-case-badge-dot"></span>
                                    B2B onboarding
                                </span>
                            </div>
                            <h3 class="use-case-title">KYB for Spanish customers and suppliers</h3>
                        </div>
                        <p>When a Spanish business signs up or you add a new supplier, check with its CIF that the company exists and is active before you approve it.</p>
                        <ul class="use-case-list">
                            <li>CIF, legal name and registry status</li>
                            <li>Current directors, to check who is signing</li>
                            <li>Registered address and CNAE code to complete the record</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">From the Free plan</span>
                            <a href="<?= site_url('spanish-company-data-api') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "KYB", "page_type": "home_en"}'>See the KYB API →</a>
                        </div>
                    </div>
                    <div class="feature-card use-case card-teal reveal delay-2">
                        <div class="use-case-header">
                            <div class="use-case-header-top">
                                <div class="use-case-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="13" y2="17"></line></svg>
                                </div>
                                <span class="use-case-badge">
                                    <span class="use-case-badge-dot"></span>
                                    ERP automation
                                </span>
                            </div>
                            <h3 class="use-case-title">Invoicing and ERP</h3>
                        </div>
                        <p>Avoid invoices with wrong tax details: fill in the legal name and address from the CIF or the company name.</p>
                        <ul class="use-case-list">
                            <li>Autocomplete by CIF or company name</li>
                            <li>Clean your customer base in batches (100 CIFs per request)</li>
                            <li>SDKs for PHP, Node.js and Python</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">Batches from the Pro plan</span>
                            <a href="<?= site_url('docs#endpoint-search') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "Invoicing and ERP", "page_type": "home_en"}'>See name search →</a>
                        </div>
                    </div>
                    <div class="feature-card use-case card-indigo reveal delay-3">
                        <div class="use-case-header">
                            <div class="use-case-header-top">
                                <div class="use-case-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                </div>
                                <span class="use-case-badge">
                                    <span class="use-case-badge-dot"></span>
                                    Due diligence
                                </span>
                            </div>
                            <h3 class="use-case-title">Counterparty risk</h3>
                        </div>
                        <p>Before you extend credit or sign with a Spanish supplier, check its legal situation and its history in the BORME.</p>
                        <ul class="use-case-list">
                            <li>Published dissolutions, insolvencies and closures</li>
                            <li>BORME history and director changes</li>
                            <li>Risk and solvency profile (0-100)</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">Risk profile on Business</span>
                            <a href="<?= site_url('docs#endpoint-risk-profile') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "Counterparty risk", "page_type": "home_en"}'>See the risk profile →</a>
                        </div>
                    </div>
                </div>
                <style>
                    .use-case {
                        display: flex;
                        flex-direction: column;
                        position: relative;
                        overflow: hidden;
                    }
                    .use-case.card-blue {
                        background: radial-gradient(circle at 18% 12%, rgba(37, 99, 235, 0.05) 0%, transparent 60%), #ffffff;
                    }
                    .use-case.card-teal {
                        background: radial-gradient(circle at 18% 12%, rgba(16, 185, 129, 0.05) 0%, transparent 60%), #ffffff;
                    }
                    .use-case.card-indigo {
                        background: radial-gradient(circle at 18% 12%, rgba(99, 102, 241, 0.05) 0%, transparent 60%), #ffffff;
                    }
                    .use-case::after {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 28px;
                        right: 28px;
                        height: 3px;
                        border-radius: 0 0 3px 3px;
                        opacity: 0;
                        transform: scaleX(0.6);
                        transition: opacity 0.35s ease, transform 0.35s ease;
                        pointer-events: none;
                    }
                    .use-case.card-blue::after { background: linear-gradient(90deg, #2563EB, #60A5FA); }
                    .use-case.card-teal::after { background: linear-gradient(90deg, #059669, #34D399); }
                    .use-case.card-indigo::after { background: linear-gradient(90deg, #4F46E5, #818CF8); }
                    .use-case:hover::after {
                        opacity: 1;
                        transform: scaleX(1);
                    }

                    /* Cabecera de la card */
                    .use-case-header {
                        margin-bottom: 16px;
                    }
                    .use-case-header-top {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                    }
                    .use-case-icon {
                        width: 52px;
                        height: 52px;
                        border-radius: 14px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                        flex-shrink: 0;
                    }
                    .card-blue .use-case-icon {
                        background: linear-gradient(135deg, rgba(37, 99, 235, 0.12) 0%, rgba(59, 130, 246, 0.05) 100%);
                        border: 1px solid rgba(37, 99, 235, 0.2);
                        box-shadow: 0 6px 14px -3px rgba(37, 99, 235, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.8);
                        color: #2563EB;
                    }
                    .card-teal .use-case-icon {
                        background: linear-gradient(135deg, rgba(5, 150, 105, 0.12) 0%, rgba(16, 185, 129, 0.05) 100%);
                        border: 1px solid rgba(5, 150, 105, 0.2);
                        box-shadow: 0 6px 14px -3px rgba(5, 150, 105, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.8);
                        color: #059669;
                    }
                    .card-indigo .use-case-icon {
                        background: linear-gradient(135deg, rgba(79, 70, 229, 0.12) 0%, rgba(99, 102, 241, 0.05) 100%);
                        border: 1px solid rgba(79, 70, 229, 0.2);
                        box-shadow: 0 6px 14px -3px rgba(79, 70, 229, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.8);
                        color: #4F46E5;
                    }

                    .use-case.card-blue:hover .use-case-icon {
                        background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
                        color: #ffffff;
                        border-color: #2563EB;
                        transform: translateY(-2px) scale(1.04);
                        box-shadow: 0 10px 22px -3px rgba(37, 99, 235, 0.38);
                    }
                    .use-case.card-teal:hover .use-case-icon {
                        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                        color: #ffffff;
                        border-color: #059669;
                        transform: translateY(-2px) scale(1.04);
                        box-shadow: 0 10px 22px -3px rgba(5, 150, 105, 0.38);
                    }
                    .use-case.card-indigo:hover .use-case-icon {
                        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
                        color: #ffffff;
                        border-color: #4F46E5;
                        transform: translateY(-2px) scale(1.04);
                        box-shadow: 0 10px 22px -3px rgba(79, 70, 229, 0.38);
                    }

                    /* Badge píldora superior */
                    .use-case-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.72rem;
                        font-weight: 750;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        padding: 5px 11px;
                        border-radius: 9999px;
                        white-space: nowrap;
                    }
                    .card-blue .use-case-badge {
                        background: rgba(37, 99, 235, 0.08);
                        color: #1D4ED8;
                        border: 1px solid rgba(37, 99, 235, 0.16);
                    }
                    .card-teal .use-case-badge {
                        background: rgba(5, 150, 105, 0.08);
                        color: #047857;
                        border: 1px solid rgba(5, 150, 105, 0.16);
                    }
                    .card-indigo .use-case-badge {
                        background: rgba(79, 70, 229, 0.08);
                        color: #4338CA;
                        border: 1px solid rgba(79, 70, 229, 0.16);
                    }
                    .use-case-badge-dot {
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: currentColor;
                    }

                    /* Título de la card */
                    .use-case-title {
                        font-size: 1.28rem;
                        font-weight: 850;
                        color: var(--ae-dark, #0F172A);
                        letter-spacing: -0.02em;
                        line-height: 1.3;
                        margin: 18px 0 0 0;
                    }

                    .use-case > p {
                        margin: 0 0 14px 0;
                        color: var(--ae-slate, #64748B);
                        font-size: 0.98rem;
                        line-height: 1.6;
                    }
                    .use-case-list {
                        list-style: none;
                        padding: 0;
                        margin: 4px 0 20px;
                        display: grid;
                        gap: 8px;
                    }
                    .use-case-list li {
                        position: relative;
                        padding-left: 22px;
                        font-size: 0.95rem;
                        color: var(--ae-slate, #64748B);
                        line-height: 1.45;
                    }
                    .use-case-list li::before {
                        content: "";
                        position: absolute;
                        left: 4px;
                        top: 0.55em;
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        opacity: .8;
                    }
                    .card-blue .use-case-list li::before { background: #2563EB; }
                    .card-teal .use-case-list li::before { background: #059669; }
                    .card-indigo .use-case-list li::before { background: #4F46E5; }

                    .use-case-foot {
                        margin-top: auto;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 12px;
                        flex-wrap: wrap;
                        padding-top: 16px;
                        border-top: 1px solid rgba(15,23,42,0.08);
                    }
                    .use-case-plan {
                        font-size: 0.78rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: .04em;
                        color: #64748B;
                    }
                    .use-case-foot a {
                        font-weight: 800;
                        font-size: 0.92rem;
                        text-decoration: none;
                        transition: opacity 0.2s ease;
                    }
                    .card-blue .use-case-foot a { color: #2563EB; }
                    .card-teal .use-case-foot a { color: #059669; }
                    .card-indigo .use-case-foot a { color: #4F46E5; }
                    .use-case-foot a:hover {
                        text-decoration: underline;
                        opacity: 0.85;
                    }
                </style>
            </div>
        </section>

        <!-- 4b. HOW IT WORKS, SOURCES AND COVERAGE -->
        <section class="band band-light how-it-works" data-track-section="how_it_works">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <h2 class="reveal">How to start with the <span class="gradient-text">Spanish company data API</span></h2>
                    <p class="reveal delay-1">Three steps from sign-up to your first response.</p>
                </div>
                <ol class="hiw-steps">
                    <li class="hiw-step reveal delay-1">
                        <span class="hiw-num">1</span>
                        <h3>Create a free account</h3>
                        <p>No card. You get <?= (int) $freeLimit ?> requests to test with real data.</p>
                        <a href="<?= site_url('register?intent=api&plan=free&source=en_home_how') ?>" data-track-event="how_cta_click" data-track-metadata='{"step": 1, "page_type": "home_en"}'>Create account →</a>
                    </li>
                    <li class="hiw-step reveal delay-2">
                        <span class="hiw-num">2</span>
                        <h3>Copy your API key</h3>
                        <p>It is in your dashboard right after sign-up. Send it in the <code>X-API-KEY</code> header of each request (<code>Authorization: Bearer</code> also works).</p>
                    </li>
                    <li class="hiw-step reveal delay-3">
                        <span class="hiw-num">3</span>
                        <h3>Make your first call</h3>
                        <p>With <code>curl</code> or the SDKs for PHP, Node.js and Python. To build without using requests, there is a Sandbox that takes the same key.</p>
                        <a href="<?= site_url('api/reference') ?>" data-track-event="how_cta_click" data-track-metadata='{"step": 3, "target": "sandbox", "page_type": "home_en"}'>Try it in the Sandbox →</a>
                    </li>
                </ol>
                <p class="hiw-tools reveal">No code? It also works from Google Sheets and WordPress.</p>


                <div class="hiw-sources reveal">
                    <h3>Coverage and limits</h3>
                    <div class="hiw-sources-grid">
                        <div>
                            <strong>What a CIF is</strong>
                            <p>The CIF (now officially the NIF of a legal entity) is a Spanish company's tax ID: a letter for the legal form, seven digits and a check character, for example <code>A46103834</code>. The API checks the format and returns the company behind it.</p>
                        </div>
                        <div>
                            <strong>Covered</strong>
                            <p>Companies registered in Spain's Commercial Registry (S.L., S.A., cooperatives and other entities with BORME filings): identity, status, directors and filing history. On Pro, the EU VAT number is also checked in VIES.</p>
                        </div>
                        <div>
                            <strong>Not covered</strong>
                            <p>Ultimate beneficial owners (UBO), sanctions or PEP screening, sole traders (autónomos) and full annual accounts. Pair the API with a screening provider if your process needs them.</p>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .how-it-works .hiw-steps { list-style: none; padding: 0; margin: 40px 0 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
                .how-it-works .hiw-step { background: #fff; border: 1px solid var(--ae-border); border-radius: 20px; padding: 28px; position: relative; }
                .how-it-works .hiw-num { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--ae-blue); color: #fff; font-weight: 900; margin-bottom: 14px; }
                .how-it-works .hiw-step h3 { font-size: 1.15rem; font-weight: 850; margin: 0 0 8px; }
                .how-it-works .hiw-step p { color: var(--ae-slate); font-size: 0.95rem; margin: 0 0 12px; }
                .how-it-works .hiw-step a { color: var(--ae-blue); font-weight: 800; text-decoration: none; font-size: 0.92rem; }
                .how-it-works code { background: #eef2ff; color: #1e3a8a; padding: 1px 6px; border-radius: 6px; font-size: 0.85em; }
                .how-it-works .hiw-tools { text-align: center; margin: 28px 0 0; color: var(--ae-slate); font-size: 0.98rem; }
                .how-it-works .hiw-sources { margin-top: 48px; background: #fff; border: 1px solid var(--ae-border); border-radius: 20px; padding: 28px 32px; }
                .how-it-works .hiw-sources h3 { font-size: 1.2rem; font-weight: 850; margin: 0 0 18px; }
                .how-it-works .hiw-sources-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
                .how-it-works .hiw-sources-grid strong { display: block; color: var(--ae-dark); margin-bottom: 6px; font-size: 0.98rem; }
                .how-it-works .hiw-sources-grid p { color: var(--ae-slate); font-size: 0.92rem; margin: 0; line-height: 1.5; }
                .how-it-works .hiw-freq { display: inline-block; margin: 0 0 8px; padding: 2px 10px; border-radius: 99px; background: #ecfdf5; color: #047857; font-size: 0.75rem; font-weight: 800; }
                .per-query-home { margin: -8px 0 12px; font-size: 0.85rem; font-weight: 600; color: #ecfdf5; opacity: .85; }
                .per-query-home b { font-weight: 800; }
                @media (max-width: 900px) {
                    .how-it-works .hiw-steps, .how-it-works .hiw-sources-grid { grid-template-columns: 1fr; }
                    .how-it-works .hiw-sources { padding: 22px; }
                }
            </style>
        </section>

        <!-- 8. PRICING -->
        <section id="precios" class="band band-light" data-track-section="pricing_cta">
            <div class="container">
                <div class="band-header" style="text-align: left; max-width: 800px;">
                    <h2 class="reveal delay-1">Spanish company API pricing</h2>
                    <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #4f46e5, #4b9a69); margin-top: 16px; margin-bottom: 24px;"></div>
                    <p class="reveal delay-2" style="font-size: 1.1rem; color: var(--ae-slate);">Test for free with real data and move to Pro or Business when you go live. Prices in euros, VAT not included. No lock-in: cancel any time.<br><br></p>
                </div>
                
                <!-- TOGGLE ANUAL / MENSUAL -->
                <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 40px; margin-top: 24px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; transition: all 0.3s;" id="labelMonthlyHome">Monthly</span>
                    <button type="button" id="billingToggleHome" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricingHome()">
                        <div id="toggleKnobHome" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(0px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnualHome">Annual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">SAVE 20%</span></span>
                </div>

                <div class="tier-grid" style="margin-top: 16px;">
                    <!-- FREE -->
                    <div class="tier tier-free reveal delay-1">
                        <div class="tier-tag">TESTING</div>
                        <h3>Free</h3>
                        <div class="tier-subtitle">To test the API</div>
                        <div class="tier-desc">Test the API with real data and validate results before moving to production.</div>
                        <div class="price">0€<span>/once</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $freeLimit ?> free requests (one-time, not renewed)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Lookup by CIF and search by name</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Basic data: company name, normalised status, CNAE, province and incorporation date</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Commercial score (0-100): the number only</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Newly incorporated companies (today, 7 or 30 days): 10 per request, with name and CIF hidden</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Free segment counts: how many companies by sector, area and size</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> No credit card required</li>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=free&source=en_home_pricing') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Start for free", "plan": "free", "source_block": "pricing_cta", "page_type": "home"}'>Start for free</a>
                    </div>
                    
                    <!-- PRO -->
                    <div class="tier tier-pro reveal delay-2">
                        <div class="tier-tag">MOST POPULAR</div>
                        <h3>Pro</h3>
                        <div class="tier-subtitle">To automate validations</div>
                        <div class="tier-desc">The ideal option for SaaS, ERPs and products that require validation in production.</div>
                        <div class="price"><b id="priceProHome" data-monthly="19" data-annual="15.17" style="font-weight: inherit;">19</b>€<span>/mo</span></div>
                        <div class="per-query-home">About <b data-monthly="0.6" data-annual="0.5">0.6</b> euro cents per request</div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 3,000 requests per month</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Full data, with address and current directors (with appointment date)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Revenue band and latest filed accounts year</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> One-call KYB verification: status, name, signer and VAT number in VIES</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Name to CIF in batch: complete your customer database (you only pay for matches)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Batch lookups: up to 100 CIFs per request</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> BORME history, signals and corporate graphs</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Watchlist of 100 companies: BORME, status and risk changes, without using requests</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Commercial score with its reasons and the latest BORME signal</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Newly incorporated companies (today, 7 or 30 days): up to 100 per request, with name and CIF</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Priority email support</li>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=pro&source=en_home_pricing') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Start with Pro", "plan": "pro", "source_block": "pricing_cta", "page_type": "home"}'>Start with Pro</a>
                    </div>
                    
                    <!-- BUSINESS -->
                    <div class="tier tier-biz reveal delay-3">
                        <div class="tier-tag">SCALE</div>
                        <h3>Business</h3>
                        <div class="tier-subtitle">For teams and high volume</div>
                        <div class="tier-desc">For higher volume, and for teams that need risk, public contracts, webhooks and segments.</div>
                        <div class="price"><b id="priceBizHome" data-monthly="49" data-annual="39.17" style="font-weight: inherit;">49</b>€<span>/mo</span></div>
                        <div class="per-query-home">About <b data-monthly="0.5" data-annual="0.4">0.5</b> euro cents per request</div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 10,000 requests per month</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Risk and solvency profile (separate from the commercial score)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Public contracts and awards</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Watchlist of 1,000 companies (BORME, status and risk)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Push webhooks for watchlist changes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Segments: download companies by CNAE, area, size and phone</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Full AI Business Insights</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> AI Contact Prep and B2B Match Calculator</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Newly incorporated companies (today, 7 or 30 days): up to 1,000 per request</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Priority email support</li>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=business&source=en_home_pricing') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Start with Business", "plan": "business", "source_block": "pricing_cta", "page_type": "home"}'>Start with Business</a>
                    </div>
                </div>

                <!-- Custom Bonus Banner -->
                <div style="margin-top: 60px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdf4 100%); padding: 48px 32px; border-radius: 24px; text-align: center; position: relative; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.15); box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); max-width: 900px; margin-left: auto; margin-right: auto;">
                    
                    <!-- Patrón de puntos decorativo de fondo -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                    
                    <!-- Efectos de luz suaves -->
                    <div style="position: absolute; top: -30%; left: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 60%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -30%; right: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 60%); pointer-events: none;"></div>

                    <div style="position: relative; z-index: 1;">
                        <div style="display: inline-block; background: #ffffff; color: #2563eb; font-size: 0.8rem; font-weight: 800; padding: 6px 16px; border-radius: 99px; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.1); border: 1px solid rgba(59,130,246,0.1);">No subscription</div>
                        <h3 style="color: #0f172a; font-size: 2.1rem; font-weight: 900; margin: 0 0 12px; letter-spacing: -0.03em;">Prefer to pay only for what you use?</h3>
                        <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">Buy <strong style="color: #0f172a;">prepaid credits</strong> with no subscription and no expiry: 1,000 credits for €9, or build a custom pack from 10,000 credits with volume discounts.</p>
                        
                        <a href="<?= site_url('buy-api-credits') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 18px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="21" y1="4" x2="14" y2="4"></line>
                                <line x1="10" y1="4" x2="3" y2="4"></line>
                                <line x1="21" y1="12" x2="12" y2="12"></line>
                                <line x1="8" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="20" x2="16" y2="20"></line>
                                <line x1="12" y1="20" x2="3" y2="20"></line>
                                <line x1="14" y1="1" x2="14" y2="7"></line>
                                <line x1="8" y1="9" x2="8" y2="15"></line>
                                <line x1="16" y1="17" x2="16" y2="23"></line>
                            </svg>
                            See credit packs
                        </a>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 80px; margin-bottom: 40px;">
                    <h3 class="reveal" style="font-size: 1.8rem; font-weight: 850; color: var(--ae-dark);">Detailed feature comparison</h3>
                </div>
                
                <details class="cap-details">
                    <summary class="cap-details-sum" data-track-event="compare_toggle" data-track-metadata='{"page_type": "home_en"}'>Show the full comparison by plan</summary>
                    <style>
                        .cap-details > summary { list-style: none; cursor: pointer; display: table; margin: 0 auto 24px; padding: 12px 24px; border-radius: 999px; background: #eff6ff; border: 1px solid #bfdbfe; color: var(--ae-blue); font-weight: 800; }
                        .cap-details > summary::-webkit-details-marker { display: none; }
                        .cap-details > summary::after { content: " ↓"; }
                        .cap-details[open] > summary::after { content: " ↑"; }
                        .cap-details > summary:hover { background: #dbeafe; }
                    </style>
                <div class="table-responsive" style="overflow-x: auto; background: #fff; border-radius: 24px; border: 1px solid var(--ae-border); box-shadow: var(--ae-shadow-sm);">
                    <table class="capabilities-table">
                        <thead>
                            <tr>
                                <th>Feature / Capability</th>
                                <th>Free</th>
                                <th class="cap-featured-col">Pro</th>
                                <th>Business</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Validation and Enrichment</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies</div>
                                        <div class="cap-feature-desc">Check that a company exists and get its registry data (CNAE, address, capital).</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Intelligent Search</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/search</div>
                                        <div class="cap-feature-desc">Find companies by name with autocomplete and normalization.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_search')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">KYB Verification</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/verify</div>
                                        <div class="cap-feature-desc">In one call: whether the company operates, whether the name matches, whether the signer is a current director and whether the VAT number is in VIES. Returns pass, review or fail.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_verify')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Name to CIF (batch)</div>
                                        <div class="cap-feature-endpoint">POST /api/v1/companies/reconcile</div>
                                        <div class="cap-feature-desc">Send up to 100 company names and get each one's CIF with a confidence score. You only pay for the matches.</div>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Batch Query</div>
                                        <div class="cap-feature-endpoint">POST /api/v1/companies/batch</div>
                                        <div class="cap-feature-desc">Query up to 100 CIFs in a single request, saving network times.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('post_batch')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Commercial score</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/score</div>
                                        <div class="cap-feature-desc">Rates from 0 to 100 how interesting a company is commercially, based on its recent BORME activity, how complete its record is and whether it has a phone, email or website. It does not measure solvency: that is what the risk profile is for.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_score')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">Basic</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">BORME Corporate Signals</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/signals</div>
                                        <div class="cap-feature-desc">Monitor real events: capital increases, changes in administrators and more.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_signals')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">BORME Acts History</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/borme</div>
                                        <div class="cap-feature-desc">Complete chronological history of publications in the Mercantile Registry.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_borme')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">IA Business Insights</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/insights</div>
                                        <div class="cap-feature-desc">Advanced analysis of business needs and conversion probability.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_insights')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">Preview</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">IA Contact Prep</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/contact-prep</div>
                                        <div class="cap-feature-desc">Generate customized sales pitches for each company using our AI.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_contact_prep')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Corporate Power Graphs</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/network</div>
                                        <div class="cap-feature-desc">Obtains the network of links between companies through their administrators.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_network')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">B2B Match Calculator</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/match</div>
                                        <div class="cap-feature-desc">Evaluates commercial fit and generates a personalized sales pitch.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_match')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Public Contracts & Tenders</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/contracts</div>
                                        <div class="cap-feature-desc">Query public tenders, awarded contracts, contracting authorities and award amounts.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_contracts')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Corporate Risk Profile</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/risk-profile</div>
                                        <div class="cap-feature-desc">Algorithmic risk score, annual accounts filing compliance and distress events published in the BORME.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_risk_profile')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Company Watchlist</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/watchlist/events</div>
                                        <div class="cap-feature-desc">Watch 100 (Pro) or 1,000 (Business) companies and get their new BORME acts, status changes and risk level changes. Does not use requests.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_watchlist_events')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Company Segments</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/filter</div>
                                        <div class="cap-feature-desc">Filter by CNAE, province, municipality, incorporation date, phone, revenue band and accounts year. Counting is free; downloading rows costs 5 requests per company.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_filter')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; font-size: 0.85rem; font-weight: 700;">Count</td>
                                <td class="cap-featured-col" style="text-align: center; font-size: 0.85rem; font-weight: 700;">Count</td>
                                <td style="text-align: center; font-size: 0.85rem; font-weight: 700;">Count and rows</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Webhooks PUSH</div>
                                        <div class="cap-feature-endpoint">POST /api/v1/webhooks</div>
                                        <div class="cap-feature-desc">Receive the changes of your watched companies on your server, signed with HMAC, without polling the API.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('post_webhook')" class="btn-json-preview">View JSON Response</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                </details>

                <p style="text-align: center; margin-top: 24px; color: var(--ae-slate); font-size: 0.9rem; font-weight: 500;">
                    Need a custom high-volume plan? <a href="<?= site_url('contact') ?>" style="color: var(--ae-blue); font-weight: 700; text-decoration: none;">Contact us</a>
                </p>
            </div>
        </section>



        <!-- 9. FAQ -->
        <section class="band" style="background: #F8FAFC; border-top: 1px solid var(--ae-border); border-bottom: 1px solid var(--ae-border); position: relative; overflow: hidden;">
            <!-- Abstract background elements for WOW effect -->
            <div style="position: absolute; top: -20%; left: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37, 99, 235, 0.04) 0%, transparent 70%); filter: blur(60px); pointer-events: none;"></div>
            <div style="position: absolute; bottom: -20%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, transparent 70%); filter: blur(60px); pointer-events: none;"></div>
            <div class="bg-grid"></div>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div class="faq-grid">
                    
                    <!-- Left Column: Intro -->
                    <div style="position: sticky; top: 120px;">
                        <span class="tag reveal" style="background: rgba(37,99,235,0.1); color: var(--ae-blue); border: none; font-weight: 800; padding: 6px 16px; border-radius: 100px; display: inline-block; margin-bottom: 8px;">FAQ</span>
                        <h2 class="reveal delay-1" style="font-size: 3rem; font-weight: 950; margin-top: 16px; margin-bottom: 24px; text-align: left; line-height: 1.1; letter-spacing: -0.03em;">Questions about the <span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">Spanish company API</span></h2>
                        <p class="reveal delay-2" style="color: var(--ae-slate); font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; font-weight: 500;">If your question is not here, write to us. The developer who builds the API answers, in English or Spanish, and can help you with the integration.</p>

                        <div class="reveal delay-2" style="display: grid; gap: 12px; margin-bottom: 32px; max-width: 420px;">
                            <div style="display: flex; align-items: center; gap: 14px; padding: 12px 18px; background: #ffffff; border-radius: 16px; border: 1px solid var(--ae-border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(37,99,235,0.1); color: var(--ae-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--ae-dark); font-size: 0.95rem;">Direct support from the developer</div>
                                    <div style="color: var(--ae-slate); font-size: 0.8rem; font-weight: 500;">Based in Spain · Reply in under 2 h on business days</div>
                                </div>
                            </div>
                        </div>

                        <a href="mailto:soporte@apiempresas.es" class="btn-ae reveal delay-3" style="background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal)); color: #ffffff; border-radius: 14px; box-shadow: 0 10px 20px -5px rgba(37,99,235,0.4); padding: 16px 32px; font-size: 1.05rem; display: inline-flex; align-items: center; gap: 12px; transition: all 0.4s ease; border: none; font-weight: 700;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 15px 30px -5px rgba(37, 99, 235, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(37,99,235,0.4)';">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Email support
                        </a>
                    </div>

                    <!-- Right Column: Accordion -->
                    <div class="faq-accordion" style="width: 100%; margin: 0;">
                        <?php
                        // Single source for the FAQ: rendered here and in the FAQPage JSON-LD at the bottom.
                        $homeFaqs = [
                            ['What data does the Spanish company API return?', 'Legal name, status (active, dissolved, in insolvency…), incorporation date, province and main activity (CNAE code). On the Pro plan you also get current directors, the full registered address, revenue band, the BORME filing history, the commercial score with its reasons and change monitoring. On Business, also the risk profile, public contracts and webhooks.'],
                            ['What is a CIF and how do I validate it?', 'The CIF (now officially the NIF of a legal entity) is the tax ID of a Spanish company: a letter for the legal form, seven digits and a check character, for example A46103834. The API checks the check digit and tells you whether a company with that CIF exists and whether it is active.'],
                            ['Can I try it for free?', 'Yes. The Free plan gives you ' . (int) $freeLimit . ' requests against the live API (one-time, not renewed), with no card and no commitment. There is also a sandbox that does not use requests, for testing the response format.'],
                            ['Is the data official?', 'Most of the data comes from official public sources such as the BORME (the gazette of the Spanish Commercial Registry), the Spanish Public Procurement Platform, the BDNS grants database and GLEIF; the BORME is processed every working day. Some address, contact and size data is completed from other public sources; the full list is on the data sources page. Registry status, the commercial score and the risk profile are calculated by us from those publications, so they are not an official Commercial Registry certificate.'],
                            ['How do I authenticate?', 'Send your API key in the X-API-KEY header (Authorization: Bearer is also accepted). The base URL is https://apiempresas.es/api/v1. You get your key in the dashboard as soon as you sign up.'],
                            ['Can it check EU VAT numbers in VIES?', 'Yes. The KYB verification endpoint (Pro) checks in one call that the company exists and is active, that the name matches, that the signer is a current director and that the VAT number is registered in VIES.'],
                            ['What does it not cover?', 'Ultimate beneficial owners (UBO), sanctions and PEP screening, sole traders (autónomos) and full annual accounts. If your compliance process needs them, combine the API with a screening provider.'],
                            ['How do pricing and invoicing work?', 'Plans are billed in euros, VAT not included: Pro at €19/month (3,000 requests) and Business at €49/month (10,000 requests), with a yearly option. You can also buy credits without a subscription. Payment is by card through Stripe and you get an invoice for every payment. No lock-in.'],
                        ];
                        foreach ($homeFaqs as $i => [$q, $a]): ?>
                        <div class="faq-item reveal delay-<?= min(3, intdiv($i, 2) + 1) ?>">
                            <div class="faq-header">
                                <h3><?= esc($q) ?></h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    <?= esc($a) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </section>

        <!-- 10. CTA FINAL -->
        <section class="band" style="background: #ffffff;" data-track-section="final_cta">
            <div class="container">
                <div style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); border-radius: 32px; padding: 56px 32px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.4);">
                    <!-- Decorative Glows -->
                    <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(96, 165, 250, 0.4) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    <div style="position: absolute; bottom: -50%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    
                    <!-- Content -->
                    <div style="position: relative; z-index: 1;">
                        <h2 style="font-size: 2.8rem; font-weight: 950; margin-bottom: 24px; color: #ffffff; letter-spacing: -0.02em; line-height: 1.1;">Start validating Spanish companies today</h2>
                        <p style="font-size: 1.25rem; margin-bottom: 48px; color: #E2E8F0; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">Look up Spanish companies from the Commercial Registry and the BORME, automate CIF checks and start with <?= (int) $freeLimit ?> free requests, no card required.</p>
                        
                        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                            <a href="<?= site_url('register?intent=api&plan=free&source=en_home_final') ?>" class="btn-ae" style="background: #ffffff; color: #0F172A; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); font-weight: 800;" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Get a free API key", "plan": "free", "source_block": "final_cta", "page_type": "home_en"}'>Get a free API key</a>
                            <a href="<?= site_url('docs') ?>" class="btn-ae" style="background: #12b48a; color: #ffffff; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.3); font-weight: 800;">
                               Read the API docs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer_en', ['sinWidgets' => true]) ?>

    <!-- JSON PREVIEW MODAL -->
    <div id="json-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.4); z-index:9999; backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
        <div style="background:#ffffff; width:100%; max-width:640px; border-radius:20px; border:1px solid #e2e8f0; box-shadow:0 30px 60px -12px rgba(15,23,42,0.15); overflow:hidden; position:relative;">
            <div style="background:#f8fafc; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="background:rgba(37,99,235,0.08); color:#2563eb; font-size:10px; font-weight:800; padding:4px 10px; border-radius:6px; letter-spacing:0.05em; text-transform:uppercase;">Response Data</span>
                    <span id="modal-endpoint-name" style="color:#475569; font-family:'Fira Code', monospace; font-size:13px; font-weight:700;">GET /v1/companies</span>
                </div>
                <button onclick="closeJsonModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:24px; line-height:1; transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>
            <div style="padding:32px; max-height:70vh; overflow-y:auto; background:#ffffff;">
                <pre id="modal-json-content" style="margin:0; font-family:'Fira Code', 'Courier New', monospace; font-size:14px; line-height:1.6; color:#1e293b;"></pre>
            </div>
            <div style="background:#f8fafc; padding:16px 24px; text-align:right; border-top:1px solid #f1f5f9;">
                <button onclick="closeJsonModal()" style="background:#ffffff; color:#475569; border:1px solid #e2e8f0; padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">Close window</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const jsonExamples = {
            get_companies: {
                success: true,
                data: {
                    id: 12345,
                    name: "TECH FLOW SOLUTIONS SL",
                    cif: "B12345678",
                    cnae: "6201",
                    cnae_label: "Programación informática",
                    founded: "2024-03-12",
                    province: "MADRID",
                    municipality: "MADRID",
                    address: "CALLE DE LA TECNOLOGIA 42",
                    status: "ACTIVA",
                    status_code: "ACTIVE",
                    status_source: "registry",
                    status_date: "2024-03-12",
                    financials: { size_band: "LT_500K", size_band_label: "Menos de 0,5 M€", last_accounts_year: 2024 },
                    score: 94
                }
            },
            get_filter: { success: true, data: [{ cif: "B12345678", name: "EMPRESA DE EJEMPLO SL", cnae: "6201", cnae_label: "Actividades de programación informática", province: "MADRID", municipality: "MADRID", founded: "2019-03-01", status: "ACTIVA", status_code: "ACTIVE", status_source: "registry", financials: { size_band: "GT_1M", size_band_label: "Más de 1 M€", last_accounts_year: 2023 }, has_phone: true }], meta: { total: 3412, returned: 100, limit: 100, has_more: true, next_cursor: "eyJpZCI6ODgxMjM0NX0", cost: 500, cost_per_row: 5, truncated: false } },
            get_verify: {
                success: true,
                data: {
                    cif: "B12345678", exists: true, name: "EMPRESA DE EJEMPLO SL",
                    status: "ACTIVA", status_code: "ACTIVE", status_source: "registry", status_date: null,
                    checks: {
                        name: { provided: "Empresa de Ejemplo, S.L.", score: 100, match: true },
                        person: { provided: "Juan Pérez García", is_current_admin: true, matched_name: "PEREZ GARCIA JUAN", position: "Adm. Unico", since: "2019-03-01" },
                        vat: { vat_number: "ESB12345678", checked: true, valid: true, source: "VIES", error: null },
                        accounts: { last_accounts_year: 2023 }
                    },
                    flags: [],
                    decision_hint: "pass",
                    checked_at: "2026-09-28T10:30:00+02:00"
                }
            },
            get_watchlist_events: {
                success: true,
                data: [
                    { id: "borme_act:8812345", type: "borme_act", date: "2026-09-22", cif: "B12345678", company_name: "EMPRESA DE EJEMPLO SL", data: { act_types: "Nombramientos, Ceses/Dimisiones", description: "Ceses/Dimisiones. Adm. Unico: ...", url_pdf: "https://www.boe.es/borme/dias/2026/09/22/pdfs/BORME-A-2026-181-28.pdf" } },
                    { id: "risk_level_change:B12345678:2026-09-25", type: "risk_level_change", date: "2026-09-25", cif: "B12345678", company_name: "EMPRESA DE EJEMPLO SL", data: { from: "BAJO", to: "MEDIO", model_change: false } }
                ],
                meta: { since: "2026-09-20", total: 2, page: 1, limit: 100, has_more: false }
            },
            get_search: {
                success: true,
                data: {
                    name: "TECH FLOW SOLUTIONS SL",
                    cif: "B12345678",
                    score: 94,
                    province: "MADRID",
                    status: "ACTIVA"
                }
            },
            post_batch: {
                success: true,
                data: [
                    { name: "INDUSTRIA DE DISENO TEXTIL SA", cif: "A15075062" },
                    { name: "INDITEX LOGISTICA SA", cif: "B00000001" }
                ],
                meta: {
                    requested: 2,
                    found: 2,
                    cost: 2,
                    truncated: false
                }
            },
            get_score: {
                success: true,
                data: {
                    cif: "B12345678",
                    score: 94,
                    priority: "MUY_ALTA",
                    reasons: ["Crecimiento de capital reciente", "Alta actividad en BORME"],
                    last_signal: {
                        type: "AMPLIACION_CAPITAL",
                        date: "2024-05-01"
                    }
                }
            },
            get_signals: {
                success: true,
                data: {
                    cif: "B12345678",
                    signals: [
                        {
                            type: "borme_event",
                            label: "AMPLIACION_CAPITAL",
                            date: "2024-05-01",
                            probability: "MUY_ALTA"
                        }
                    ]
                }
            },
            get_borme: {
                success: true,
                data: {
                    cif: "B12345678",
                    company_name: "EMPRESA DE EJEMPLO SL",
                    events: [
                        {
                            date: "2023-11-01",
                            act_types: "Nombramientos, Ceses",
                            description: "Ceses/Dimisiones. Administrador once: JUAN PEREZ...",
                            url_pdf: "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf"
                        }
                    ]
                }
            },
            get_insights: {
                success: true,
                data: {
                    profile: "SaaS / Fintech / Cloud",
                    summary: "Empresa con alta tracción y necesidad inminente de escalado tecnológico.",
                    needs: ["Infraestructura Cloud", "Ciberseguridad", "Contratación Devs"],
                    conversion_probability: "HIGH",
                    estimated_ticket: "10k-50k€"
                }
            },
            get_radar: {
                success: true,
                meta: {
                    plan: "pro",
                    count: 142,
                    limit: 100
                },
                data: [
                    { name: "NEW CORP SL", cif: "B99887766", founded: "2024-05-05", province: "BARCELONA", score: 88 }
                ]
            },
            get_network: {
                success: true,
                data: {
                    cif: "B12345678",
                    administrators: [
                        {
                            name: "GARCIA LOPEZ JUAN",
                            position: "Administrador Único",
                            linked_companies: [
                                { name: "OTRA EMPRESA SL", cif: "B87654321", status: "ACTIVA" }
                            ]
                        }
                    ]
                }
            },
            get_match: {
                success: true,
                data: {
                    cif: "B12345678",
                    seller_sector: "software",
                    match_score: 85,
                    analysis: {
                        match_level: "Alto",
                        synergy: "Alta sinergia",
                        buyer_needs: ["Digitalización", "CRM"]
                    },
                    sales_pitch: "He visto que están creciendo. Nuestro software puede ayudarles a..."
                }
            },
            get_contact_prep: {
                success: true,
                data: {
                    cif: "B12345678",
                    pitch_angle: "Escalabilidad tecnológica",
                    suggested_mosage: "Hola, he visto que Tech Flow Solutions SL está creciendo...",
                    key_metrics: ["+20% empleados este año", "Última ronda: Series A"]
                }
            },
            get_contracts: {
                success: true,
                data: {
                    cif: "A01001411",
                    company_name: "RHEINMETALL EXPAL MUNITIONS SA",
                    summary: {
                        total_contracts: 32,
                        total_amount: "617746086.47",
                        currency: "EUR"
                    },
                    contracts: [
                        {
                            tender_id: "https://contrataciondelestado.es/sindicacion/licitacionesPerfilContratante/20344437",
                            title: "Suministro de 27.000 granadas de mortero de 81 mm...",
                            contracting_authority: "Jefatura de Asuntos Económicos del Mando de Apoyo Logístico",
                            award_date: "2026-08-25",
                            amount: "4395320.00",
                            currency: "EUR",
                            tender_url: "https://contrataciondelestado.es/wps/poc?uri=deeplink:detalle_licitacion&idEvl=B4EkDEGFvaA%2Bk2oCbDosIw%3D%3D"
                        }
                    ],
                    pagination: {
                        total: 32,
                        page: 1,
                        limit: 20,
                        total_pages: 2,
                        has_more: true
                    }
                }
            },
            get_risk_profile: {
                success: true,
                data: {
                    cif: "A01001411",
                    company_name: "RHEINMETALL EXPAL MUNITIONS SA",
                    risk_score: 62,
                    risk_level: "ALTO",
                    confidence_score: 49,
                    data_quality_score: 70,
                    summary_message: "Atención: Constan indicadores de elevado riesgo financiero o corporativo.",
                    legal_state: "REGISTRY_CLOSURE_GENERICO",
                    data_sources: {
                        borme_status: "CHECKED_WITH_RECORDS",
                        accounts_status: "KNOWN_DELAYED",
                        official_status: "KNOWN"
                    },
                    dimensions: {
                        legal_distress: 60,
                        filing_compliance: 0,
                        governance_volatility: 30,
                        capital_instability: 0,
                        structural_volatility: 0,
                        stabilizing_credit: 0
                    },
                    canonical_events: [
                        {
                            code: "LEGAL_STATE_REGISTRY_CLOSURE_GENERICO",
                            dimension: "legal_distress",
                            severity: "high",
                            description: "Consta publicación registral de cierre sin especificación de causa.",
                            event_date: "2026-08-24",
                            classification_confidence: "LOW"
                        }
                    ],
                    model_version: "2.0.0",
                    calculated_at: "2026-08-24T00:00:00Z"
                }
            },
            post_webhook: {
                success: true,
                message: "Webhook creado correctamente",
                id: 789,
                event: "watchlist.*",
                secret: "3f9c1b2a7d..."
            }
        };

        function showJsonPreview(key) {
            const modal = document.getElementById('json-modal');
            const content = document.getElementById('modal-json-content');
            const endpoint = document.getElementById('modal-endpoint-name');
            
            const names = {
                get_companies: 'GET /companies',
                get_verify: 'GET /companies/verify',
                get_filter: 'GET /companies/filter',
                get_watchlist_events: 'GET /watchlist/events',
                get_search: 'GET /companies/search',
                post_batch: 'POST /companies/batch',
                get_score: 'GET /companies/score',
                get_signals: 'GET /companies/signals',
                get_borme: 'GET /companies/borme',
                get_insights: 'GET /companies/insights',
                get_radar: 'GET /companies/radar',
                get_network: 'GET /companies/network',
                get_match: 'GET /companies/match',
                get_contact_prep: 'GET /companies/contact-prep',
                get_contracts: 'GET /companies/contracts',
                get_risk_profile: 'GET /companies/risk-profile',
                post_webhook: 'POST /webhooks'
            };

            endpoint.textContent = names[key];
            content.innerHTML = syntaxHighlight(jsonExamples[key]);
            modal.style.display = 'flex';

            // Tracking Event
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'view_json_preview',
                'api_endpoint': names[key]
            });
            // Eliminamos overflow hidden para evitar saltos de scroll
            // document.body.style.overflow = 'hidden';
        }

        function closeJsonModal() {
            document.getElementById('json-modal').style.display = 'none';
            // document.body.style.overflow = 'auto';
        }

        function syntaxHighlight(json) {
            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
                var cls = 'color:#d97706;'; // number (Orange)
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'color:#2563eb;'; // key (Blue)
                    } else {
                        cls = 'color:#16a34a;'; // string (Green)
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'color:#9333ea;'; // boolean (Purple)
                } else if (/null/.test(match)) {
                    cls = 'color:#64748b;'; // null (Gray)
                }
                return '<span style="' + cls + ' font-weight: 500;">' + match + '</span>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // FAQ Accordion Logic
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const header = item.querySelector('.faq-header');
                const content = item.querySelector('.faq-content');
                
                header.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Close all others
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            otherItem.querySelector('.faq-content').style.maxHeight = null;
                        }
                    });
                    
                    // Toggle current
                    if (isActive) {
                        item.classList.remove('active');
                        content.style.maxHeight = null;
                    } else {
                        item.classList.add('active');
                        content.style.maxHeight = content.scrollHeight + "px";
                    }
                });
            });
        });

        // CTA Click Tracking
        $(document).on('click', '[data-cta="radar_home"]', function(e) {
            const source = $(this).data('source') || 'home_generic';
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: 'cta_click',
                source: source
            });
        });

        // Language tabs and copy the visible hero example
        (function () {
            var btn = document.getElementById('heroCurlCopy');
            if (!btn) return;
            var lang = 'curl';
            document.querySelectorAll('.hero-curl .hc-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    lang = tab.dataset.lang;
                    document.querySelectorAll('.hero-curl .hc-tab').forEach(function (t) {
                        var on = t === tab;
                        t.classList.toggle('active', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    document.querySelectorAll('.hero-curl pre.hc-code').forEach(function (pre) {
                        pre.hidden = pre.dataset.lang !== lang;
                    });
                    if (window.trackEvent) window.trackEvent('hero_code_tab', { lang: lang, source_block: 'hero', page_type: 'home_en' }, 'heroCodeTab');
                });
            });
            btn.addEventListener('click', function () {
                var pre = document.querySelector('.hero-curl pre.hc-code[data-lang="' + lang + '"]');
                var text = pre ? pre.textContent : '';
                var done = function () {
                    btn.textContent = 'Copied';
                    setTimeout(function () { btn.textContent = 'Copy'; }, 2000);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () {});
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = text; document.body.appendChild(ta); ta.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(ta);
                }
                if (window.trackEvent) window.trackEvent('hero_curl_copy', { lang: lang, source_block: 'hero', page_type: 'home_en' }, 'heroCurlCopy');
            });
        })();

        // Pricing Toggle Home (starts on monthly, like the Spanish home)
        let isAnnualHome = false;
        function togglePricingHome() {
            isAnnualHome = !isAnnualHome;
            const knob = document.getElementById('toggleKnobHome');
            const labelMonthly = document.getElementById('labelMonthlyHome');
            const labelAnnual = document.getElementById('labelAnnualHome');
            const pricePro = document.getElementById('priceProHome');
            const priceBiz = document.getElementById('priceBizHome');

            if(isAnnualHome) {
                knob.style.transform = 'translateX(24px)';
                labelMonthly.style.color = '#94a3b8';
                labelMonthly.style.fontWeight = '600';
                labelAnnual.style.color = '#2563eb';
                labelAnnual.style.fontWeight = '800';
                
                pricePro.textContent = pricePro.dataset.annual;
                priceBiz.textContent = priceBiz.dataset.annual;
                document.querySelectorAll('.per-query-home b').forEach(function (n) { n.textContent = n.dataset.annual; });
            } else {
                knob.style.transform = 'translateX(0px)';
                labelMonthly.style.color = '#2563eb';
                labelMonthly.style.fontWeight = '800';
                labelAnnual.style.color = '#94a3b8';
                labelAnnual.style.fontWeight = '600';
                
                pricePro.textContent = pricePro.dataset.monthly;
                priceBiz.textContent = priceBiz.dataset.monthly;
                document.querySelectorAll('.per-query-home b').forEach(function (n) { n.textContent = n.dataset.monthly; });
            }

            // Paid sign-up links carry the billing period the visitor is looking at
            document.querySelectorAll('a[href*="register?intent=api&plan=pro"], a[href*="register?intent=api&plan=business"]').forEach(function (a) {
                var u = new URL(a.href, window.location.origin);
                u.searchParams.set('period', isAnnualHome ? 'annual' : 'monthly');
                a.href = u.toString();
            });
        }
    </script>
    <script type="application/ld+json">
    <?= json_encode([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(static fn($f) => [
            '@type'          => 'Question',
            'name'           => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $homeFaqs),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>
    </script>
</body>
</html>

