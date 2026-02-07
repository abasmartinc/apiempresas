<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Empresas - Sugerencias en Tiempo Real</title>
    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #3b82f6;
            --bg-color: #f8fafc;
            --text-color: #334155;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            padding-top: 5rem;
            margin: 0;
        }

        .search-container {
            width: 100%;
            max-width: 600px;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .input-group {
            position: relative;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box; /* Important for padding */
        }

        input[type="text"]:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10;
            max-height: 400px;
            overflow-y: auto;
            display: none; /* Hidden by default */
            margin-top: 4px;
        }

        .suggestions-dropdown.active {
            display: block;
        }

        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.15s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background-color: #f1f5f9;
        }

        .company-name {
            font-weight: 600;
            color: var(--primary-color);
            display: block;
        }

        .company-meta {
            font-size: 0.85rem;
            color: #64748b;
        }

        .company-cif {
            font-family: monospace;
            background: #e0e7ff;
            color: #3730a3;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .spinner {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--accent-color);
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        .no-results {
            padding: 1rem;
            color: #64748b;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="search-container">
    <h1>Búsqueda de Empresas</h1>
    <div class="input-group">
        <input type="text" id="company-search" placeholder="Escribe nombre o CIF..." autocomplete="off">
        <div class="spinner" id="loading-spinner"></div>
        <div class="suggestions-dropdown" id="suggestions">
            <!-- Results will be injected here -->
        </div>
    </div>
    <pre id="json-result" style="margin-top: 1rem; background: #f1f5f9; padding: 1rem; border-radius: 0.5rem; white-space: pre-wrap; display: none;"></pre>
</div>

<script>
    const input = document.getElementById('company-search');
    const dropdown = document.getElementById('suggestions');
    const spinner = document.getElementById('loading-spinner');
    const jsonResult = document.getElementById('json-result');
    let debounceTimer;
    
    // Inject global base URL from PHP
    const baseUrl = '<?= site_url() ?>';

    // Helper to format results
    const renderItem = (company) => {
        // Store the full object as a stringified data attribute
        const jsonString = encodeURIComponent(JSON.stringify(company));
        return `
            <div class="suggestion-item" data-json="${jsonString}">
                <div>
                    <span class="company-name">${company.name}</span>
                    <span class="company-meta">${company.province || 'España'}</span>
                </div>
                <span class="company-cif">${company.cif}</span>
            </div>
        `;
    };

    const fetchSuggestions = async (query) => {
        if (!query || query.length < 3) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        spinner.style.display = 'block';

        try {
            // Remove trailing slash if present to avoid double slash
            const cleanBase = baseUrl.replace(/\/$/, '');
            const response = await fetch(`${cleanBase}/company-suggestions/get?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            spinner.style.display = 'none';
            dropdown.innerHTML = '';

            if (result.success && result.data && result.data.length > 0) {
                const html = result.data.map(renderItem).join('');
                dropdown.innerHTML = html;
                dropdown.classList.add('active');
            } else {
                dropdown.innerHTML = '<div class="no-results">No se encontraron coincidencias.</div>';
                dropdown.classList.add('active');
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            spinner.style.display = 'none';
            dropdown.innerHTML = '<div class="no-results">Error de conexión.</div>';
            dropdown.classList.add('active');
        }
    };

    input.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (value.length === 0) {
            dropdown.classList.remove('active');
            spinner.style.display = 'none';
            jsonResult.style.display = 'none'; // Hide JSON when clearing
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSuggestions(value);
        }, 300); // 300ms debounce
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Select item logic
    dropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const name = item.querySelector('.company-name').textContent;
            input.value = name;
            dropdown.classList.remove('active');
            
            // Display JSON
            const jsonString = decodeURIComponent(item.dataset.json);
            try {
                const companyData = JSON.parse(jsonString);
                jsonResult.textContent = JSON.stringify(companyData, null, 4);
                jsonResult.style.display = 'block';
            } catch (err) {
                console.error('Error parsing JSON:', err);
                jsonResult.textContent = 'Error displaying data.';
                jsonResult.style.display = 'block';
            }
        }
    });
</script>

</body>
</html>
