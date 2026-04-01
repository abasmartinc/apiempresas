<?php
/**
 * APIEmpresas Professional Integration Test
 * ----------------------------------------
 * Dedicated test for the Professional Plan (99€/month).
 * Step 1: /api/v1/professional/search (Free Suggestions)
 * Step 2: /api/v1/professional/details (Billed Details)
 */

// CONFIGURATION
$apiUrl = 'http://localhost/apiempresas'; // Update to your production URL
$apiKey = 'ce482296366bff9d06b6cb5a7f120289f09a26e45463106b2b0a851c75743d99';

// Handle AJAX requests from this same file
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    $query = $_GET['q'] ?? '';
    $cif = $_GET['cif'] ?? '';

    if ($action === 'search') {
        $url = "$apiUrl/api/v1/professional/search?q=" . urlencode($query);
    } else {
        $url = "$apiUrl/api/v1/professional/details?cif=" . urlencode($cif);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-API-KEY: $apiKey",
        "Accept: application/json"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    http_response_code($httpCode);
    echo $response;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo APIEmpresas - Plan Professional</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --accent: #2563eb;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .wrapper {
            width: 100%;
            max-width: 900px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .badge {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 16px;
            display: inline-block;
        }

        h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin: 0 0 12px;
        }

        .search-area {
            position: relative;
            background: var(--card);
            padding: 24px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }

        .input-group {
            position: relative;
        }

        input {
            width: 100%;
            padding: 18px 24px;
            font-size: 18px;
            font-weight: 600;
            background: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 18px;
            outline: none;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        input:focus {
            background: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1);
        }

        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 18px;
            margin-top: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            border: 1px solid #e2e8f0;
            max-height: 350px;
            overflow-y: auto;
            z-index: 50;
            display: none;
        }

        .dropdown.active {
            display: block;
        }

        .item {
            padding: 16px 24px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .item:hover {
            background: #f8fbff;
        }

        .item .name {
            font-weight: 700;
            font-size: 15px;
        }

        .item .cif {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 800;
            color: var(--accent);
            background: #eff6ff;
            padding: 5px 10px;
            border-radius: 8px;
        }

        .result-section {
            margin-top: 40px;
            display: none;
        }

        .result-section.active {
            display: block;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .res-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .res-title {
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
        }

        .res-status {
            font-size: 12px;
            font-weight: 700;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .res-status::before {
            content: "";
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
        }

        pre {
            background: #0f172a;
            color: #e2e8f0;
            padding: 32px;
            border-radius: 24px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            line-height: 1.7;
            overflow-x: auto;
            margin: 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .billing-note {
            margin-top: 24px;
            padding: 20px;
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .billing-note svg {
            flex-shrink: 0;
            color: #d97706;
        }

        .billing-note p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
            font-weight: 600;
            line-height: 1.5;
        }

        .dot-loader {
            position: absolute;
            right: 20px;
            top: 22px;
            display: none;
        }

        .dot-loader span {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
            animation: bounce 0.6s infinite alternate;
        }

        .dot-loader span:nth-child(2) { animation-delay: 0.2s; }
        .dot-loader span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-8px); }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <span class="badge">Entorno de Pruebas</span>
            <h1>Buscador de Empresas</h1>
            <p style="color: var(--muted); font-weight: 500;">Demostración de búsqueda en tiempo real e integración de datos</p>
        </div>

        <div class="search-area">
            <div class="input-group">
                <input type="text" id="searchInput" placeholder="Escribe el nombre de una empresa..." autocomplete="off">
                <div class="dot-loader" id="loader">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <div class="dropdown" id="dropdown"></div>
        </div>

        <div class="result-section" id="results">
            <div class="res-header">
                <span class="res-title">Respuesta de la API (JSON)</span>
            </div>
            <pre id="jsonCode"></pre>
        </div>
    </div>

    <script>
        const input = document.getElementById('searchInput');
        const dropdown = document.getElementById('dropdown');
        const loader = document.getElementById('loader');
        const results = document.getElementById('results');
        const jsonCode = document.getElementById('jsonCode');
        let timer;

        input.addEventListener('input', (e) => {
            const val = e.target.value.trim();
            clearTimeout(timer);
            
            if (val.length < 3) {
                dropdown.classList.remove('active');
                return;
            }

            timer = setTimeout(() => {
                performSearch(val);
            }, 300);
        });

        async function performSearch(q) {
            loader.style.display = 'block';
            try {
                const response = await fetch(`?action=search&q=${encodeURIComponent(q)}`);
                const data = await response.json();
                
                if (data.success && data.data && data.data.length > 0) {
                    dropdown.innerHTML = data.data.map(item => `
                        <div class="item" onclick="fetchDetails('${item.cif}', '${item.name}')">
                            <span class="name">${item.name}</span>
                            <span class="cif">${item.cif}</span>
                        </div>
                    `).join('');
                    dropdown.classList.add('active');
                } else {
                    dropdown.innerHTML = '<div class="item">No se han encontrado resultados.</div>';
                    dropdown.classList.add('active');
                }
            } catch (err) {
                console.error('Search error:', err);
            } finally {
                loader.style.display = 'none';
            }
        }

        async function fetchDetails(cif, name) {
            dropdown.classList.remove('active');
            input.value = name;
            loader.style.display = 'block';
            results.classList.remove('active');

            try {
                const response = await fetch(`?action=details&cif=${encodeURIComponent(cif)}`);
                const data = await response.json();
                
                jsonCode.textContent = JSON.stringify(data, null, 4);
                results.classList.add('active');
                results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (err) {
                console.error('Details error:', err);
            } finally {
                loader.style.display = 'none';
            }
        }

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>
