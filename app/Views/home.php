<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --ae-blue: #2563EB;
            --ae-blue-vibrant: #3B82F6;
            --ae-teal: #10B981;
            --ae-dark: #0F172A;
            --ae-slate: #64748B;
            --ae-border: #E2E8F0;
            --ae-bg-light: #F8FAFC;
            --font-ae: 'Inter', sans-serif;
            --ae-glass: rgba(255, 255, 255, 0.8);
            --ae-shadow-premium: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes subtlePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .reveal {
            opacity: 0;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        body {
            font-family: var(--font-ae);
            background-color: #ffffff;
            color: var(--ae-dark);
            margin: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* --- SECCIÓN HERO --- */
        .hero {
            padding: 80px 0 60px;
            background: 
                radial-gradient(circle at 0% 0%, rgba(19, 58, 130, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(18, 180, 138, 0.05) 0%, transparent 40%);
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 28px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            color: var(--ae-dark);
        }

        .hero h1 span.gradient-text {
            display: block;
            background: linear-gradient(90deg, #1D4ED8 0%, #10B981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding-bottom: 8px;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--ae-slate);
            max-width: 720px;
            margin: 0 auto 32px;
            line-height: 1.6;
            font-weight: 500;
        }

        .hero .trust-tag {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--ae-slate);
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-top: 32px;
            display: block;
            opacity: 0.6;
        }

        /* --- BOTONES CORPORATIVOS --- */
        .btn-ae {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 18px 36px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
        }

        .btn-ae-primary {
            background: var(--ae-blue);
            color: #ffffff;
            box-shadow: 0 10px 20px -5px rgba(19, 58, 130, 0.3);
        }

        .btn-ae-primary:hover {
            background: var(--ae-blue-vibrant);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(19, 58, 130, 0.4);
        }

        .btn-ae-outline {
            background: #ffffff;
            color: var(--ae-blue);
            border: 2px solid var(--ae-border);
        }

        .btn-ae-outline:hover {
            border-color: var(--ae-blue);
            background: var(--ae-bg-light);
            transform: translateY(-2px);
        }

        /* --- BLOQUE BUSCADOR (ESTILO PANEL) --- */
        .search-section {
            padding: 20px 0 40px;
            position: relative;
            background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.03) 0%, transparent 50%);
        }

        .search-panel {
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 
                0 40px 120px -20px rgba(15, 23, 42, 0.08), 
                0 0 0 1px rgba(15, 23, 42, 0.01);
            padding: 40px 60px;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 1);
        }

        /* Decorative background elements inside the panel */
        .search-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
        }

        .search-panel::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -100px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
        }

        .search-panel h2 {
            font-size: 2.8rem;
            font-weight: 950;
            margin: 0 0 12px 0;
            color: var(--ae-dark);
            text-align: center;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .search-panel h2 span.highlight {
            background: linear-gradient(120deg, rgba(37, 99, 235, 0.12) 0%, rgba(37, 99, 235, 0.05) 100%);
            padding: 2px 14px;
            border-radius: 12px;
            color: var(--ae-blue);
            display: inline-block;
            transition: all 0.3s ease;
            cursor: default;
        }

        .search-panel h2 span.highlight:hover {
            background: linear-gradient(120deg, rgba(37, 99, 235, 0.18) 0%, rgba(37, 99, 235, 0.08) 100%);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px -10px rgba(37, 99, 235, 0.2);
        }

        .search-panel .badge-intro {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #F1F5F9;
            color: #475569;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .search-panel .badge-intro .dot-live {
            width: 8px;
            height: 8px;
            background: var(--ae-blue);
            border-radius: 50%;
            animation: pulse-blue 2s infinite;
        }

        /* Hide the decorative line from global styles */
        section.container .search-panel h2::after,
        .search-panel h2::before,
        .search-panel h2::after {
            display: none !important;
            content: none !important;
            height: 0 !important;
            width: 0 !important;
        }

        .search-panel p.subtitle {
            text-align: center;
            color: var(--ae-slate);
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .search-form-wrapper {
            position: relative;
            max-width: 850px;
            margin: 0 auto;
        }

        .search-form {
            display: flex;
            gap: 12px;
            background: #ffffff;
            padding: 12px;
            border-radius: 20px;
            border: 1px solid var(--ae-border);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 50;
            pointer-events: auto;
        }

        .search-form:focus-within {
            border-color: var(--ae-blue);
            box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.2);
            transform: translateY(-2px);
        }

        .search-input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0 28px;
            font-size: 1.25rem;
            font-weight: 600;
            height: 72px;
            outline: none;
            color: var(--ae-dark);
        }

        .search-input::placeholder {
            color: #94A3B8;
            font-weight: 500;
        }

        .search-benefits {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .benefit-tag {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 16px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ae-slate);
            transition: all 0.3s ease;
            padding: 20px;
            border-radius: 16px;
        }

        .benefit-tag:hover {
            background: #f8fafc;
            color: var(--ae-dark);
            transform: translateY(-4px);
        }

        .benefit-icon {
            width: 48px;
            height: 48px;
            background: #f0fdf4;
            color: #16a34a;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.1);
        }

        /* --- SECCIONES DE CONTENIDO --- */
        .band {
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .band-light { background: var(--ae-bg-light); }

        /* Abstract Background Decoration */
        .band::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(19, 58, 130, 0.03) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            z-index: -1;
        }

        .band-header {
            max-width: 800px;
            margin-bottom: 60px;
        }

        .band-header .tag {
            display: inline-block;
            background: linear-gradient(135deg, rgba(19, 58, 130, 0.1) 0%, rgba(18, 180, 138, 0.1) 100%);
            color: var(--ae-blue);
            padding: 8px 20px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 24px;
            border: 1px solid rgba(19, 58, 130, 0.1);
        }

        .band-header h2 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 24px;
            color: var(--ae-dark);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .feature-card {
            padding: 40px 32px;
            background: #ffffff;
            border: 1px solid var(--ae-border);
            border-radius: 32px;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.02);
        }

        .feature-card:hover {
            transform: translateY(-16px);
            border-color: transparent;
            box-shadow: 0 40px 80px -20px rgba(11, 26, 54, 0.15);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 32px;
            padding: 2px;
            background: linear-gradient(135deg, transparent 0%, transparent 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            transition: background 0.5s ease;
        }

        .feature-card:hover::before {
            background: linear-gradient(135deg, var(--ae-blue) 0%, var(--ae-teal) 100%);
        }

        .feature-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            transition: all 0.4s ease;
        }

        .feature-card .icon-box::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: currentColor;
            opacity: 0.1;
            z-index: -1;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover .icon-box {
            transform: scale(1.1) rotate(8deg);
            color: #ffffff !important;
        }

        .feature-card:hover .icon-box::after {
            opacity: 1;
            background: linear-gradient(135deg, currentColor 0%, #10B981 100%);
        }

        .feature-card h4 {
            font-size: 1.2rem;
            font-weight: 850;
            margin-bottom: 0;
            color: var(--ae-dark);
            letter-spacing: -0.01em;
        }

        .feature-card p {
            font-size: 1.1rem;
            color: var(--ae-slate);
            margin: 0;
            line-height: 1.7;
        }

        /* Card-specific accents */
        .card-blue .icon-box { color: #2563EB; }
        .card-teal .icon-box { color: #059669; }
        .card-indigo .icon-box { color: #4F46E5; }

        /* --- DOS FORMAS (CONTRASTE) --- */
        .dual-path {
            background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%);
            color: #ffffff;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .dual-path::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .dual-path .band-header h2 { color: #ffffff; font-size: 3.5rem; }
        .dual-path .band-header p { color: rgba(255,255,255,0.7); font-size: 1.25rem; }

        .path-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            position: relative;
            z-index: 2;
        }

        .path-card {
            padding: 60px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            transition: all 0.4s ease;
        }

        .path-card:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-10px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 30px 60px -10px rgba(0,0,0,0.3);
        }

        .path-card h3 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }

        .path-card p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .path-list {
            list-style: none;
            padding: 0;
            margin: 0 0 48px 0;
        }

        .path-list li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .path-list li svg {
            color: var(--ae-teal);
        }

        /* --- PRODUCT SECTIONS --- */
        .product-flex {
            display: flex;
            align-items: center;
            gap: 60px;
            padding: 40px 0;
        }

        .product-info { flex: 1.1; }
        .product-visual { flex: 1.3; position: relative; }

        .product-info h2 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 24px;
            line-height: 1.1;
            color: var(--ae-dark);
            letter-spacing: -0.03em;
        }

        .product-info p {
            font-size: 1.2rem;
            color: var(--ae-slate);
            line-height: 1.6;
            margin-bottom: 40px;
        }

        /* Clean Feature Grid */
        .feature-grid-simple {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 48px;
        }

        .feature-tag {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: var(--ae-dark);
            font-size: 1rem;
        }

        .feature-tag svg {
            color: var(--ae-teal);
            flex-shrink: 0;
        }

        /* Clean Mockup Frame & Abstract UI */
        .mockup-container {
            position: relative;
            z-index: 2;
        }

        /* Premium Light Dashboard UI */
        .mockup-browser {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 40px 100px -20px rgba(11, 26, 54, 0.15), 0 0 0 1px rgba(255, 255, 255, 1) inset;
            overflow: hidden;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.5s ease;
            position: relative;
        }

        .mockup-browser:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 50px 120px -20px rgba(11, 26, 54, 0.2), 0 0 40px rgba(37, 99, 235, 0.05);
        }

        .mockup-header {
            background: #F8FAFC;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot:nth-child(1) { background: #ED6A5E; }
        .dot:nth-child(2) { background: #F4BF4F; }
        .dot:nth-child(3) { background: #61C554; }

        .abstract-dashboard {
            display: flex;
            height: 440px;
            background: #F1F5F9; /* Light grey background like dashboard */
            position: relative;
            overflow: hidden;
        }

        /* Subtle mesh gradient background */
        .dashboard-bg-glow {
            position: absolute;
            top: -50%;
            left: 50%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
        }

        .dashboard-sidebar {
            width: 70px;
            background: #0F172A; /* Navy dark like screenshot */
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            z-index: 2;
        }
        
        .db-icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(255,255,255,0.1); transition: all 0.3s ease; }
        .db-icon.active { background: var(--ae-blue); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); transform: scale(1.1); }
        .db-icon:hover:not(.active) { background: rgba(255,255,255,0.2); }

        .dashboard-main {
            flex: 1;
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            z-index: 2;
        }

        /* Top Nav */
        .db-top { display: flex; justify-content: space-between; align-items: center; }
        .db-search { 
            width: 240px; height: 36px; 
            background: #ffffff; 
            border: 1px solid rgba(0, 0, 0, 0.08); 
            border-radius: 100px; 
            display: flex; align-items: center; padding: 0 12px; gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) inset;
        }
        .db-search::before { content: ''; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #94A3B8; border-bottom-color: transparent; transform: rotate(45deg); }
        .db-user { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal)); box-shadow: 0 4px 10px rgba(37,99,235,0.2); }

        /* Stats Row */
        .db-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .db-stat-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .db-stat-card::after {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, rgba(37,99,235,0.1), transparent);
        }
        .db-stat-label { font-size: 0.75rem; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; margin-bottom: 8px; }
        .db-stat-val { font-size: 1.75rem; font-weight: 900; color: #0F172A; display: flex; align-items: baseline; gap: 8px; }
        .db-stat-val span { font-size: 0.85rem; color: #10B981; font-weight: 700; background: rgba(16,185,129,0.1); padding: 2px 6px; border-radius: 100px; }
        
        /* Data Table */
        .db-table {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.02);
        }
        .db-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .db-table-title { font-size: 1rem; color: #0F172A; font-weight: 900; display: flex; align-items: center; gap: 8px; }
        .db-live-badge { width: 8px; height: 8px; background: #10B981; border-radius: 50%; box-shadow: 0 0 10px #10B981; animation: pulse-green 2s infinite; }
        
        .db-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; background: #ffffff;
            border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .db-row:hover { transform: translateY(-2px); border-color: var(--ae-blue); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); }
        
        .db-col-main { display: flex; flex-direction: column; gap: 2px; }
        .db-company { font-size: 1rem; color: #0F172A; font-weight: 900; }
        .db-meta { font-size: 0.8rem; color: #64748B; font-weight: 500; }
        
        .db-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.02em; text-transform: uppercase; }
        .badge-nuevo { background: #DCFCE7; color: #166534; }
        .badge-contactar { background: #E0F2FE; color: #0369A1; }
        .badge-premium { background: #FEF3C7; color: #92400E; }
        
        .db-btn-action {
            background: var(--ae-blue);
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
        }
        
        .db-action { width: 32px; height: 32px; border-radius: 8px; background: #ffffff; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: background 0.2s; }
        .db-row:hover .db-action { background: #F1F5F9; border-color: rgba(37, 99, 235, 0.2); }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Floating Wow Element */
        .floating-alert {
            position: absolute;
            right: -40px;
            bottom: 60px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px #ffffff inset;
            z-index: 20;
            animation: float-alert 6s ease-in-out infinite;
        }
        
        @keyframes float-alert {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .alert-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 16px rgba(37,99,235,0.2);
        }
        .alert-text h6 { margin: 0; font-size: 0.9rem; color: #0F172A; font-weight: 900; margin-bottom: 2px; }
        .alert-text p { margin: 0; font-size: 0.8rem; color: #64748B; font-weight: 500; }

        /* API Mockup Premium Design */
        .api-mockup-wrapper {
            position: relative;
            z-index: 2;
        }

        .api-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
            filter: blur(40px);
            z-index: -1;
            pointer-events: none;
        }

        .floating-badge-api {
            position: absolute;
            top: -20px;
            right: -20px;
            background: #10B981;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 100px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            z-index: 10;
            animation: float-alert 5s ease-in-out infinite reverse;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 10px #ffffff;
            animation: pulse-white 2s infinite;
        }

        @keyframes pulse-white {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        .code-editor-window {
            background: #0F172A;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 40px 100px -20px rgba(11, 26, 54, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            overflow: hidden;
            transition: transform 0.4s ease;
        }

        .code-editor-window:hover {
            transform: translateY(-8px) rotate(1deg);
            box-shadow: 0 50px 120px -20px rgba(11, 26, 54, 0.5), 0 0 40px rgba(16, 185, 129, 0.1);
        }

        .editor-header {
            background: #1E293B;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .editor-header .dots {
            display: flex;
            gap: 6px;
        }

        .editor-header .dot.red { background: #ED6A5E; }
        .editor-header .dot.yellow { background: #F4BF4F; }
        .editor-header .dot.green { background: #61C554; }

        .editor-header .tab {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            color: #94A3B8;
            font-family: monospace;
            font-size: 0.85rem;
            background: #0F172A;
            padding: 4px 16px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .editor-body {
            padding: 24px;
            font-family: 'Fira Code', Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
            font-size: 0.95rem;
            line-height: 1.6;
            overflow-x: auto;
        }

        /* Syntax Highlighting */
        .token.punctuation { color: #89DDFF; }
        .token.property { color: #F07178; }
        .token.boolean { color: #FFCB6B; }
        .token.string { color: #C3E88D; }
        .token.number { color: #F78C6C; }

        .visual-decoration {
            position: absolute;
            width: 120%;
            height: 120%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
            top: -10%;
            left: -10%;
            z-index: -1;
            filter: blur(40px);
        }

        .product-info p {
            font-size: 1.2rem;
            color: var(--ae-slate);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        /* --- COMPARATIVA --- */
        .comp-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            position: relative;
        }

        .comp-card {
            padding: 60px;
            background: #ffffff;
            border: 1px solid var(--ae-border);
            border-radius: 32px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .comp-card:hover {
            transform: translateY(-12px);
            border-color: var(--ae-blue);
            box-shadow: 0 40px 80px -20px rgba(11, 26, 54, 0.15);
        }

        .comp-card .tag {
            color: var(--ae-blue);
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 24px;
            display: block;
        }

        .comp-card h3 {
            font-size: 2.25rem;
            font-weight: 900;
            margin-bottom: 20px;
            color: var(--ae-dark);
            letter-spacing: -0.02em;
        }

        .comp-card p {
            font-size: 1.15rem;
            color: var(--ae-slate);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* --- PRICING --- */
        .tier-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .tier {
            padding: 40px;
            border-radius: 12px;
            text-align: left;
            transition: all 0.4s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .tier-free {
            background: #5c6370;
            color: #ffffff;
        }

        .tier-pro {
            background: #4f46e5;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 40px 100px -20px rgba(79, 70, 229, 0.4);
            z-index: 10;
        }

        .tier-biz {
            background: #4b9a69;
            color: #ffffff;
        }

        .tier h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: #ffffff;
        }

        .tier-subtitle {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }

        .tier-desc {
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 24px;
            color: rgba(255, 255, 255, 0.9);
            min-height: 70px;
        }

        .tier .price {
            font-size: 3.5rem;
            font-weight: 950;
            margin-bottom: 24px;
            color: #ffffff;
            letter-spacing: -0.04em;
        }

        .tier .price span {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            letter-spacing: 0;
        }

        .tier-features {
            list-style: none;
            padding: 0;
            margin: 0 0 40px 0;
            flex-grow: 1;
        }

        .tier-features li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .tier-features li:last-child {
            border-bottom: none;
        }

        .tier-features li svg { color: #ffffff; }

        .tier-tag {
            position: absolute;
            top: 24px;
            right: 24px;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .tier-free .tier-tag { background: #e2e8f0; color: #475569; }
        .tier-pro .tier-tag { background: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); }
        .tier-biz .tier-tag { background: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); }

        .btn-tier {
            display: inline-block;
            width: 100%;
            padding: 16px;
            background: #ffffff;
            color: #0F172A;
            border-radius: 8px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .btn-tier:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .tier-pro .btn-tier { color: #4f46e5; }
        .tier-biz .btn-tier { color: #4b9a69; }

        /* --- COMPARISON SECTION (PREMIUM) --- */
        .comp-grid-premium {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-top: 48px;
        }

        .comp-card-premium {
            border-radius: 24px;
            padding: 40px 32px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 16px;
            z-index: 1;
        }

        .comp-card-premium:hover {
            transform: translateY(-8px);
        }

        .comp-card-premium::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -2;
            transition: opacity 0.4s ease;
        }

        .comp-bg-icon {
            position: absolute;
            right: -5%;
            bottom: -5%;
            width: 240px;
            height: 240px;
            opacity: 0.05;
            z-index: -1;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .comp-card-premium:hover .comp-bg-icon {
            transform: scale(1.1) rotate(-10deg);
            opacity: 0.1;
        }

        .comp-card-premium h3 {
            font-size: 2.2rem;
            font-weight: 950;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .comp-card-premium p {
            font-size: 1.15rem;
            line-height: 1.6;
            margin: 0 0 24px 0;
            flex: 1;
        }

        /* Radar Card (Light/Vibrant) */
        .card-radar {
            background: #ffffff;
            border: 1px solid rgba(37, 99, 235, 0.1);
            box-shadow: 0 20px 40px -10px rgba(11, 26, 54, 0.05);
        }
        .card-radar::before {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.8) 0%, rgba(255, 255, 255, 0) 100%);
        }
        .card-radar:hover {
            box-shadow: 0 30px 60px -15px rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.3);
        }
        .card-radar .tag-premium {
            background: rgba(37, 99, 235, 0.1);
            color: var(--ae-blue);
            align-self: flex-start;
        }
        .card-radar h3 { color: var(--ae-dark); }
        .card-radar p { color: var(--ae-slate); }
        .card-radar .comp-bg-icon { color: var(--ae-blue); }

        /* API Card (Dark/Tech) */
        .card-api {
            background: linear-gradient(135deg, #0A2540 0%, #1e3a8a 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px -10px rgba(10, 37, 64, 0.6);
        }
        .card-api::before {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(0, 0, 0, 0) 100%);
        }
        .card-api:hover {
            box-shadow: 0 30px 60px -15px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.3);
        }
        .card-api .tag-premium {
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
            align-self: flex-start;
        }
        .card-api h3 { color: #ffffff; }
        .card-api p { color: #94A3B8; }
        .card-api .comp-bg-icon { color: #10B981; }

        .tag-premium {
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }

        /* --- FAQ Accordion (WOW Effect) --- */
        .faq-accordion { max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; perspective: 1000px; }
        .faq-item {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 15px -5px rgba(0, 0, 0, 0.03);
            position: relative;
            border: 1px solid var(--ae-border);
            transform-origin: center left;
        }
        
        .faq-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0; width: 4px;
            background: linear-gradient(to bottom, var(--ae-blue), var(--ae-teal));
            opacity: 0;
            transition: opacity 0.4s ease, transform 0.4s ease;
            transform: scaleY(0);
            transform-origin: center;
        }

        .faq-item:hover {
            transform: translateX(8px) rotateY(-1deg);
            box-shadow: 0 15px 30px -10px rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.15);
        }
        
        .faq-item:hover::before {
            opacity: 0.6;
            transform: scaleY(0.5);
        }

        .faq-item.active {
            border-color: rgba(37, 99, 235, 0.3);
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.15);
            transform: scale(1.01);
            z-index: 10;
        }

        .faq-item.active::before {
            opacity: 1;
            transform: scaleY(1);
        }

        .faq-header {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            background: transparent;
            transition: background 0.4s ease;
        }
        
        .faq-item.active .faq-header {
            background: linear-gradient(90deg, rgba(37,99,235,0.02) 0%, transparent 100%);
        }

        .faq-header h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ae-dark);
            padding-right: 16px;
            transition: color 0.3s ease;
        }
        
        .faq-item.active .faq-header h4 {
            color: var(--ae-blue);
        }

        .faq-icon {
            width: 32px;
            height: 32px;
            position: relative;
            background: #F8FAFC;
            border-radius: 10px;
            color: var(--ae-slate);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .faq-icon::after {
            content: '';
            width: 8px;
            height: 8px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-1px);
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .faq-item:hover .faq-icon {
            background: #ffffff;
            color: var(--ae-blue);
            box-shadow: 0 4px 12px -4px rgba(37, 99, 235, 0.1);
            transform: scale(1.05);
        }
        
        .faq-item.active .faq-icon { 
            background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal));
            color: #ffffff; 
            border-color: transparent;
            box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.3);
            transform: rotate(180deg);
        }

        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .faq-body {
            padding: 0 24px 24px 24px;
            color: var(--ae-slate);
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: 0s;
        }

        .faq-item.active .faq-body {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.1s;
        }
        
        .faq-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 64px;
            align-items: start;
        }

        /* Integration Icons */
        .integration-icon {
            position: absolute;
            width: 48px;
            height: 48px;
            background: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid var(--ae-border);
            z-index: 5;
            animation: float-slow 8s ease-in-out infinite;
        }

        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(10px, -20px); }
        }

        .icon-sf { top: 10%; right: 5%; animation-delay: 1s; }
        .icon-hs { bottom: 15%; right: 15%; animation-delay: 3s; }
        .icon-sl { top: 30%; left: -5%; animation-delay: 2s; }

        /* Grid Background Pattern */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(var(--ae-border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.3;
            mask-image: linear-gradient(to bottom, transparent, black, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black, transparent);
            pointer-events: none;
        }

        /* Abstract Blob */
        .blob-bg {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            filter: blur(80px);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .hero h1 { font-size: 2.5rem; }
            .grid-3, .path-grid, .tier-grid, .comp-grid, .comp-grid-premium, .faq-grid { grid-template-columns: 1fr; }
            .product-flex { flex-direction: column !important; text-align: center; gap: 40px; padding: 20px 0; }
            .product-info h2 { font-size: 2.2rem; }
            .feature-card { padding: 30px 24px; }
            .feature-card .icon-box { margin-bottom: 16px; }
            .product-visual { display: none; } /* Ocultar mockup en móvil por legibilidad */
            .stat-1, .stat-2, .integration-icon { display: none; }
            .search-form { flex-direction: column; gap: 12px; }
            .search-panel { padding: 32px 20px; border-radius: 24px; }
            .search-panel h2 { font-size: 2.1rem; margin-bottom: 16px; line-height: 1.2; }
            .search-panel h2 span.highlight { padding: 2px 10px; }
            .search-panel .subtitle { font-size: 1.1rem; margin-bottom: 32px; line-height: 1.5; }
            .search-input { height: 64px !important; text-align: center; font-size: 1.1rem !important; border-radius: 14px !important; }
            #btnBuscar { height: 64px !important; width: 100% !important; padding: 0 !important; border-radius: 14px !important; }
            .search-benefits { display: flex; flex-direction: column; gap: 12px; align-items: center; margin-top: 32px; }
            .benefit-tag { background: #f8fafc; width: 100%; justify-content: center; padding: 14px; border-radius: 14px; border: 1px solid #e2e8f0; }
        }

        @media (max-width: 600px) {
            .hero h1 { font-size: 2.2rem; }
            .product-info h2 { font-size: 1.8rem; }
            .db-sidebar { width: 50px; }
            .db-main { padding: 15px; }
            .floating-alert { display: none; }
        }

        /* --- OPTIMIZACIÓN RADAR B2B --- */
        .radar-extra,
        .radar-trigger-second {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 24px;
            border-radius: 12px;
            margin-top: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .radar-extra h4,
        .radar-trigger-second h4 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
            color: #0f172a;
            font-weight: 800;
        }

        .radar-extra p,
        .radar-trigger-second p {
            margin: 0 0 16px 0;
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
        }

        .btn-radar {
            background: #12b48a;
            color: white !important;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-weight: 700;
            transition: all 0.2s;
        }

        .btn-radar:hover {
            background: #0e906e;
            transform: translateY(-1px);
        }

        .radar-badge {
            background: #f0fdf4;
            color: #16a34a;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 12px;
        }

        .radar-context-text {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 12px;
            display: block;
            font-style: italic;
        }
        .pro-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 24px;
            border: 1px solid transparent;
        }

        .pro-badge svg {
            width: 14px;
            height: 14px;
        }

        .pro-badge-blue {
            background: rgba(37, 99, 235, 0.08);
            color: var(--ae-blue);
            border-color: rgba(37, 99, 235, 0.15);
        }

        .pro-badge-green {
            background: rgba(16, 185, 129, 0.08);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.15);
        }
        .hero-btns {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 32px;
        }

        @media (max-width: 768px) {
            .hero-btns {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            .hero-btns .btn-ae {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>

    <?= view('partials/header') ?>

    <main>

        <!-- 1. HERO PROFESIONAL -->
        <section class="hero container">
            <h1 class="reveal">
                Valida CIF, consulta empresas y 
                <span class="gradient-text">detecta oportunidades comerciales</span>
            </h1>
            <p class="reveal delay-1">Consulta datos oficiales de empresas españolas y accede a nuevas oportunidades de negocio con información basada en Registro Mercantil y BORME.<br>También puedes detectar empresas nuevas en España listas para prospección con Radar B2B.</p>
            
            <div class="hero-btns reveal delay-3">
                <a href="#buscar" class="btn-ae btn-ae-primary">Validar CIF gratis</a>
                <a href="<?= getRadarRedirect('home_hero') ?>" class="btn-ae btn-ae-outline" data-cta="radar_home" data-source="home_hero">Ver Radar en acción</a>
            </div>
            
            <span class="trust-tag reveal delay-3">Datos empresariales oficiales · Registro Mercantil · BORME</span>
        </section>

        <!-- 2. BLOQUE DE BÚSQUEDA -->
        <section id="buscar" class="search-section container">
            <div class="search-panel reveal delay-3">
                <div style="text-align: center;">
                    <div class="badge-intro">
                        <div class="dot-live"></div>
                        Acceso a Base de Datos Oficial
                    </div>
                </div>
                <h2>Validador de CIF y <span class="highlight">Buscador Oficial</span></h2>
                <p class="subtitle">Valide datos en segundos con conexión directa al Registro Mercantil y BORME.</p>
                
                <div class="search-form-wrapper">
                    <div class="search-form">
                        <input type="text" id="q" class="search-input" placeholder="Ej: B12345678 o Nombre de Empresa" aria-label="Buscador de empresas">
                        <button id="btnBuscar" class="btn-ae btn-ae-primary" style="height: 72px; padding: 0 48px; border-radius: 14px; font-size: 1.15rem;">Validar ahora</button>
                    </div>
                </div>

                <p style="text-align: center; font-size: 0.9rem; color: #64748b; margin-top: 8px; font-weight: 500;">¿Prefieres ver oportunidades directamente? → <a href="<?= getRadarRedirect('home_search') ?>" style="color: var(--ae-blue); text-decoration: none; font-weight: 800;" data-cta="radar_home" data-source="home_search">Ver Radar B2B</a></p>

                <div id="resultado_container" style="display:none; margin-top: 24px;">
                    <div id="resultado"></div>
                </div>

                <div class="search-benefits">
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Validación rápida de CIF</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Consulta en tiempo real</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Datos para prospección</span>
                    </div>
                </div>
                <p style="text-align: center; font-size: 1rem; color: var(--ae-slate); margin-top: 16px; font-weight: 600;">¿Buscas oportunidades para prospectar? <a href="<?= getRadarRedirect('home_search') ?>" style="color: var(--ae-blue); font-weight: 800; text-decoration: none; border-bottom: 2px solid rgba(37, 99, 235, 0.2); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--ae-blue)'" onmouseout="this.style.borderColor='rgba(37, 99, 235, 0.2)'" data-cta="radar_home" data-source="home_search">Ver Radar B2B.</a></p>
            </div>
        </section>

        <!-- 3. BLOQUE DE AUTORIDAD -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-blue reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Soluciones integrales
                    </div>
                    <h2 class="reveal delay-1">Datos empresariales para <br><span class="gradient-text">validar, integrar y vender mejor</span></h2>
                    <p class="reveal delay-2">Acceda a información veraz para mejorar sus procesos de validación de clientes o para potenciar sus equipos comerciales con datos frescos.</p>
                </div>

                <div class="grid-3">
                    <div class="feature-card card-blue reveal delay-1">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <h4 style="margin-bottom: 0;">Validación y consulta</h4>
                        </div>
                        <p>Compruebe la existencia de sociedades, verifique CIFs y acceda a los datos de registro básicos con total rapidez.</p>
                    </div>
                    <div class="feature-card card-teal reveal delay-2">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="22" y1="12" x2="18" y2="12"></line><line x1="6" y1="12" x2="2" y2="12"></line><line x1="12" y1="6" x2="12" y2="2"></line><line x1="12" y1="22" x2="12" y2="18"></line></svg>
                            </div>
                            <h4 style="margin-bottom: 0;">Prospección comercial</h4>
                        </div>
                        <p>Identifique empresas recién creadas antes que su competencia y priorice sus esfuerzos comerciales con precisión.</p>
                    </div>
                    <div class="feature-card card-indigo reveal delay-3">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            <h4 style="margin-bottom: 0;">Integración vía API</h4>
                        </div>
                        <p>Automatice sus flujos internos conectando su CRM o ERP directamente a nuestra base de datos oficial.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. DOS FORMAS DE CRECER -->
        <section class="dual-path">
            <div class="container">
                <div class="band-header" style="text-align: center; margin-left: auto; margin-right: auto;">
                    <h2 class="reveal">Una plataforma, <br><span style="color: var(--ae-teal);">dos formas de crecer</span></h2>
                    <p class="reveal delay-1">Elige el camino que mejor se adapte a tu operativa diaria.<br>Radar está pensado para equipos comerciales. La API está diseñada para integración y automatización.</p>
                </div>

                <div class="path-grid">
                    <div class="path-card reveal delay-1">
                        <h3 style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(37,99,235,0.1); padding: 10px; border-radius: 10px; color: #3B82F6; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            Equipos comerciales
                        </h3>
                        <p>Utilice Radar B2B para detectar oportunidades de negocio de forma visual y sin necesidad de programación.</p>
                        <ul class="path-list">
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Detección de empresas recién creadas
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Filtrado por actividad y provincia
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Exportación de leads comerciales
                            </li>
                        </ul>
                        <a href="<?= getRadarRedirect('home_dual_block') ?>" class="btn-ae" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0F172A; font-weight: 800; border: none; width: 100%; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);" data-cta="radar_home" data-source="home_dual_block">Ver Radar</a>
                    </div>
                    <div class="path-card reveal delay-2">
                        <h3 style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(16,185,129,0.1); padding: 10px; border-radius: 10px; color: #10B981; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            Equipos técnicos
                        </h3>
                        <p>Integre nuestra API para automatizar la validación de empresas y el enriquecimiento de datos en sus sistemas.</p>
                        <ul class="path-list">
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Endpoints REST de alta disponibilidad
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Documentación técnica detallada
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Escalabilidad para grandes volúmenes
                            </li>
                        </ul>
                        <a href="<?= site_url('documentation') ?>" class="btn-ae" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0F172A; font-weight: 800; border: none; width: 100%; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);">Documentación API</a>
                    </div>
                </div>
                <p style="text-align: center; margin-top: 40px; color: var(--ae-slate); font-weight: 600;">Si tu objetivo es vender o prospectar directamente, utiliza Radar B2B. <br>Si necesitas integrar datos en tu sistema, utiliza la API.</p>
            </div>
        </section>

        <!-- 5. BLOQUE RADAR -->
        <section class="band">
            <div class="container product-flex">
                <div class="product-info">
                    <div class="pro-badge pro-badge-blue reveal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                        Inteligencia de Ventas
                    </div>
                    <h2 class="reveal delay-1">Radar B2B para detectar nuevas empresas <br><span class="gradient-text">con potencial comercial</span></h2>
                    <p class="reveal delay-1">Accede a sociedades recién creadas, filtra por actividad y ubicación, y trabaja oportunidades antes de que el mercado se sature. <br>La mayoría de empresas nuevas solo son una oportunidad real durante sus primeros días de actividad.</p>
                    
                    <div class="feature-grid-simple reveal delay-2">
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Alertas diarias
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Filtros avanzados
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Exportación CSV
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Datos oficiales
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 20px;" class="reveal delay-3">
                        <a href="<?= getRadarRedirect('home_product') ?>" class="btn-ae btn-ae-primary" data-cta="radar_home" data-source="home_product">Ver oportunidades</a>
                        <a href="<?= site_url('leads-empresas-nuevas') ?>" style="color: var(--ae-slate); font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.3s ease;" onmouseover="this.style.color='var(--ae-blue)'; this.style.borderColor='var(--ae-blue)';" onmouseout="this.style.color='var(--ae-slate)'; this.style.borderColor='transparent';" data-cta="radar_home" data-location="radar_block">Cómo funciona</a>
                    </div>
                </div>

                <div class="product-visual reveal delay-2">
                    <div class="visual-decoration"></div>
                    <div class="mockup-container">
                        <!-- Floating Glassmorphism Alert -->
                        <div class="floating-alert">
                            <div class="alert-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div class="alert-text">
                                <h6>Match de ICP Alto</h6>
                                <p>TechVision S.L. ha sido añadida</p>
                            </div>
                        </div>

                        <div class="mockup-browser">
                            <div class="mockup-header">
                                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                            </div>
                            
                            <!-- Premium Dark Dashboard UI -->
                            <div class="abstract-dashboard">
                                <div class="dashboard-bg-glow"></div>
                                
                                <div class="dashboard-sidebar">
                                    <div class="db-icon active" style="background: #2563EB; display: flex; align-items: center; justify-content: center;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    </div>
                                    <div class="db-icon"></div>
                                    <div class="db-icon"></div>
                                    <div class="db-icon" style="margin-top: auto; opacity: 0.5;"></div>
                                </div>
                                
                                <div class="dashboard-main">
                                    <div class="db-top">
                                        <div class="db-search">
                                            <span style="color: #64748B; font-size: 0.8rem;">Buscar empresas, CIF...</span>
                                        </div>
                                        <div class="db-user"></div>
                                    </div>
                                    
                                    <div class="db-stats">
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Nuevas Hoy</div>
                                            <div class="db-stat-val">412 <span style="background: rgba(16, 185, 129, 0.1); color: #10B981;">+12%</span></div>
                                        </div>
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Oportunidades</div>
                                            <div class="db-stat-val">89 <span style="background: rgba(37, 99, 235, 0.1); color: var(--ae-blue);">Activas</span></div>
                                        </div>
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Ahorro Tiempo</div>
                                            <div class="db-stat-val">85% <span style="background: #F1F5F9; color: #64748B;">IA</span></div>
                                        </div>
                                    </div>
                                    
                                    <div class="db-table" style="background: transparent; border: none; box-shadow: none; padding: 0;">
                                        <div class="db-table-header" style="margin-bottom: 12px; padding: 0 4px;">
                                            <div class="db-table-title" style="font-size: 1.1rem;">
                                                Radar de Prospectos <div class="db-live-badge" style="margin-left: 8px;"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="db-row" style="margin-bottom: 8px;">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    TechVision Global S.L.
                                                    <span class="db-badge" style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">NUEVO</span>
                                                </div>
                                                <div class="db-meta">Madrid • Hace 2h</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                        
                                        <div class="db-row" style="margin-bottom: 8px;">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    Quantum Finance
                                                    <span class="db-badge" style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">INTERÉS</span>
                                                </div>
                                                <div class="db-meta">Barcelona • Hace 5h</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                        
                                        <div class="db-row">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    EcoLogistics Sur
                                                    <span class="db-badge" style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">PREMIUM</span>
                                                </div>
                                                <div class="db-meta">Sevilla • Hace 1d</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Premium Dashboard UI -->

                    </div>
                </div>
            </div>
        </section>

        <!-- 6. BLOQUE API -->
        <section class="band band-light">
            <div class="container product-flex" style="flex-direction: row-reverse;">
                <div class="product-info">
                    <div class="pro-badge pro-badge-green reveal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        Para Desarrolladores
                    </div>
                    <h2 class="reveal delay-1">API de empresas para validar, <br><span class="gradient-text">consultar e integrar datos</span></h2>
                    <p class="reveal delay-1">Incorpore información empresarial oficial directamente en sus procesos de registro, formularios o aplicaciones internas.</p>
                    <ul class="path-list reveal delay-2" style="margin-bottom: 48px;">
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Validación automática de CIF en tiempo real
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Enriquecimiento de datos de prospectos
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Fácil integración vía JSON / REST
                        </li>
                    </ul>
                    <a href="<?= site_url('api-empresas') ?>" class="btn-ae btn-ae-primary reveal delay-3" style="background: #10B981; border-color: #10B981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">Explorar API</a>
                </div>
                <div class="product-visual reveal delay-2">
                    
                    <div class="api-mockup-wrapper">
                        <div class="api-glow"></div>
                        
                        <div class="floating-badge-api">
                            <span class="pulse-dot"></span>
                            200 OK — 45ms
                        </div>

                        <div class="code-editor-window">
                            <div class="editor-header">
                                <div class="dots">
                                    <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                                </div>
                                <div class="tab">GET /api/v1/companies?cif=B12345678</div>
                            </div>
                            <div class="editor-body">
<pre style="margin: 0;"><code><span class="token punctuation">{</span>
  <span class="token property">"success"</span><span class="token punctuation">:</span> <span class="token boolean">true</span><span class="token punctuation">,</span>
  <span class="token property">"data"</span><span class="token punctuation">:</span> <span class="token punctuation">{</span>
    <span class="token property">"cif"</span><span class="token punctuation">:</span> <span class="token string">"B12345678"</span><span class="token punctuation">,</span>
    <span class="token property">"name"</span><span class="token punctuation">:</span> <span class="token string">"EMPRESA DE EJEMPLO SL"</span><span class="token punctuation">,</span>
    <span class="token property">"status"</span><span class="token punctuation">:</span> <span class="token string">"ACTIVA"</span><span class="token punctuation">,</span>
    <span class="token property">"province"</span><span class="token punctuation">:</span> <span class="token string">"MADRID"</span><span class="token punctuation">,</span>
    <span class="token property">"cnae"</span><span class="token punctuation">:</span> <span class="token string">"6201"</span><span class="token punctuation">,</span>
    <span class="token property">"cnae_label"</span><span class="token punctuation">:</span> <span class="token string">"Actividades de programación informática"</span>
  <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 7. COMPARATIVA -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="text-align: center; margin-left: auto; margin-right: auto; max-width: 600px;">
                    <div class="pro-badge pro-badge-blue reveal" style="background: rgba(37,99,235,0.05); border: none; margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        Diferencias clave
                    </div>
                    <h2 class="reveal delay-1">¿Radar o API? Elige según cómo trabajas</h2>
                    <p class="reveal delay-2">Dos formas de acceder a nuestra base de datos. Selecciona la herramienta que mejor se adapte a las capacidades de tu equipo. <br>Radar está orientado a acción comercial. La API está orientada a integración y automatización.</p>
                </div>
                <div class="comp-grid-premium">
                    <div class="comp-card-premium card-radar reveal delay-1">
                        <svg class="comp-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                        <div class="tag-premium">Para Equipos Comerciales</div>
                        <h3>Radar B2B</h3>
                        <p>Interfaz web lista para usar. Filtra, descubre y exporta nuevas oportunidades comerciales diariamente sin necesidad de programación.</p>
                        <a href="<?= getRadarRedirect('home_product') ?>" class="btn-ae btn-ae-primary" style="width: 100%; padding: 20px; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(37,99,235,0.2);" data-cta="radar_home" data-source="home_product">Ver oportunidades</a>
                    </div>
                    <div class="comp-card-premium card-api reveal delay-2">
                        <svg class="comp-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        <div class="tag-premium">Para Desarrolladores</div>
                        <h3>API REST</h3>
                        <p>Endpoints JSON para automatizar la validación de CIFs e integrar datos de empresas directamente en tu propio software o CRM.</p>
                        <a href="<?= site_url('documentation') ?>" class="btn-ae" style="width: 100%; padding: 20px; font-size: 1.1rem; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">Ver Documentación API</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. PRICING -->
        <section id="precios" class="band band-light">
            <div class="container">
                <div class="band-header" style="text-align: left; max-width: 800px;">
                    <h2 class="reveal delay-1">Planes transparentes para cualquier volumen</h2>
                    <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #4f46e5, #4b9a69); margin-top: 16px; margin-bottom: 24px;"></div>
                    <p class="reveal delay-2" style="font-size: 1.1rem; color: var(--ae-slate);">Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business con control de consumo y trazabilidad. Sin permanencias, sin costes ocultos.<br><br><strong style="color: var(--ae-dark);">Los planes siguientes corresponden al acceso a la API. Radar B2B dispone de una suscripción independiente orientada a equipos comerciales.</strong></p>
                </div>
                
                <div class="tier-grid" style="margin-top: 48px;">
                    <!-- FREE -->
                    <div class="tier tier-free reveal delay-1">
                        <div class="tier-tag">TESTING</div>
                        <h3>Free</h3>
                        <div class="tier-subtitle">Para probar la API</div>
                        <div class="tier-desc">Prueba la API con datos reales y valida resultados antes de pasar a producción.</div>
                        <div class="price">0€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 100 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Acceso al mismo motor de validación</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Datos oficiales para comprobar resultados</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Sin tarjeta de crédito</li>
                        </ul>
                        <a href="<?= site_url('register?plan=free') ?>" class="btn-tier">Empezar gratis</a>
                    </div>
                    
                    <!-- PRO -->
                    <div class="tier tier-pro reveal delay-2">
                        <div class="tier-tag">MÁS ELEGIDO</div>
                        <h3>Pro</h3>
                        <div class="tier-subtitle">Para automatizar validaciones</div>
                        <div class="tier-desc">La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.</div>
                        <div class="price">19€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 3.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Verificación completa y actualizada</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Tiempo real para automatización</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Ideal para facturación y scoring</li>
                        </ul>
                        <a href="<?= site_url('register?plan=pro') ?>" class="btn-tier">Empezar con Pro</a>
                    </div>
                    
                    <!-- BUSINESS -->
                    <div class="tier tier-biz reveal delay-3">
                        <div class="tier-tag">ESCALA</div>
                        <h3>Business</h3>
                        <div class="tier-subtitle">Para equipos y alto volumen</div>
                        <div class="tier-desc">Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.</div>
                        <div class="price">49€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 10.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Infraestructura preparada para alta carga</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> SLA y alta disponibilidad</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Soporte prioritario</li>
                        </ul>
                        <a href="<?= site_url('register?plan=business') ?>" class="btn-tier">Empezar con Business</a>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 48px;">
                    <a href="<?= getRadarRedirect('home_pricing') ?>" class="btn-ae" style="background: #12b48a; color: white; padding: 18px 36px; font-size: 1.1rem; border-radius: 14px; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.2); font-weight: 800;" data-cta="radar_home" data-source="home_pricing">Ver Radar B2B (79€/mes)</a>
                </div>
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
                        <span class="tag reveal" style="background: rgba(37,99,235,0.1); color: var(--ae-blue); border: none; font-weight: 800; padding: 6px 16px; border-radius: 100px; display: inline-block; margin-bottom: 8px;">Soporte Técnico</span>
                        <h2 class="reveal delay-1" style="font-size: 3rem; font-weight: 950; margin-top: 16px; margin-bottom: 24px; text-align: left; line-height: 1.1; letter-spacing: -0.03em;">Resolvemos <br><span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">tus dudas</span></h2>
                        <p class="reveal delay-2" style="color: var(--ae-slate); font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; font-weight: 500;">Si no encuentras la respuesta que buscas, nuestro equipo de expertos está disponible para ayudarte a integrar la API o configurar tu Radar B2B al máximo nivel.</p>
                        
                        <!-- Avatar group & trust -->
                        <div class="reveal delay-2" style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding: 12px 20px; background: #ffffff; border-radius: 16px; border: 1px solid var(--ae-border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); max-width: max-content;">
                            <div style="display: flex;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #fca5a5, #ef4444); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; z-index: 3; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">ES</div>
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #93c5fd, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; margin-left: -12px; z-index: 2; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">AG</div>
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #6ee7b7, #10b981); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; margin-left: -12px; z-index: 1; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">MJ</div>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--ae-dark); font-size: 0.95rem;">Soporte en España</div>
                                <div style="color: var(--ae-slate); font-size: 0.8rem; font-weight: 500;">Tiempo de respuesta &lt; 2h</div>
                            </div>
                        </div>

                        <a href="mailto:soporte@apiempresas.es" class="btn-ae reveal delay-3" style="background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal)); color: #ffffff; border-radius: 14px; box-shadow: 0 10px 20px -5px rgba(37,99,235,0.4); padding: 16px 32px; font-size: 1.05rem; display: inline-flex; align-items: center; gap: 12px; transition: all 0.4s ease; border: none; font-weight: 700;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 15px 30px -5px rgba(37, 99, 235, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(37,99,235,0.4)';">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Contactar equipo
                        </a>
                    </div>

                    <!-- Right Column: Accordion -->
                    <div class="faq-accordion" style="width: 100%; margin: 0;">
                        <!-- Q1 -->
                        <div class="faq-item reveal delay-1">
                            <div class="faq-header">
                                <h4>¿Qué datos devuelve la API?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Devuelve la razón social oficial, el estado de actividad (activa, extinguida, etc.), la fecha de constitución, la provincia, y la actividad principal (CNAE) obtenida directamente del Registro Mercantil.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q2 -->
                        <div class="faq-item reveal delay-1">
                            <div class="faq-header">
                                <h4>¿Puedo probarla gratis?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Sí, al registrarte obtienes un plan Free con 100 consultas gratuitas al mes para que puedas hacer pruebas en nuestro entorno Sandbox o en producción sin ningún tipo de compromiso.
                                </div>
                            </div>
                        </div>

                        <!-- Q3 -->
                        <div class="faq-item reveal delay-2">
                            <div class="faq-header">
                                <h4>¿La información es oficial?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Absolutamente. Todos nuestros datos provienen de fuentes oficiales del Estado, como el Registro Mercantil Central y el BORME, garantizando su validez y actualización constante.
                                </div>
                            </div>
                        </div>

                        <!-- Q4 -->
                        <div class="faq-item reveal delay-2">
                            <div class="faq-header">
                                <h4>¿Qué diferencia hay entre API y Radar?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Radar B2B es una plataforma visual lista para usar, ideal para equipos comerciales que buscan prospectos. La API es un servicio técnico (endpoints JSON) pensado para que los desarrolladores integren los datos directamente en su propio software (CRM, ERP, procesos de alta).
                                </div>
                            </div>
                        </div>

                        <!-- Q5 -->
                        <div class="faq-item reveal delay-3">
                            <div class="faq-header">
                                <h4>¿Cuánto se tarda en integrar?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Nuestra API REST está diseñada con estándares modernos y es extremadamente sencilla. Un desarrollador promedio puede completar la integración y validar su primera empresa en menos de una hora. Dispones de documentación detallada para guiarte.
                                </div>
                            </div>
                        </div>

                        <!-- Q6 -->
                        <div class="faq-item reveal delay-3">
                            <div class="faq-header">
                                <h4>¿Sirve para prospección B2B?</h4>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Sí, especialmente a través de nuestro producto Radar B2B. Podrás detectar diariamente qué nuevas empresas se han creado en España y filtrarlas por sector o provincia para llegar a ellas antes que tu competencia.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- 10. CTA FINAL -->
        <section class="band" style="background: #ffffff;">
            <div class="container">
                <div style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); border-radius: 32px; padding: 56px 32px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.4);">
                    <!-- Decorative Glows -->
                    <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(96, 165, 250, 0.4) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    <div style="position: absolute; bottom: -50%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    
                    <!-- Content -->
                    <div style="position: relative; z-index: 1;">
                        <h2 style="font-size: 2.8rem; font-weight: 950; margin-bottom: 24px; color: #ffffff; letter-spacing: -0.02em; line-height: 1.1;">Empieza hoy a validar empresas o encontrar nuevos clientes</h2>
                        <p style="font-size: 1.25rem; margin-bottom: 48px; color: #E2E8F0; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">Consulta datos empresariales, intégralos en tu sistema o trabaja oportunidades con Radar B2B.</p>
                        
                        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                            <a href="#buscar" class="btn-ae" style="background: #ffffff; color: #0F172A; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); font-weight: 800;">Validar CIF gratis</a>
                            <a href="<?= getRadarRedirect('home_final') ?>" class="btn-ae" style="background: #12b48a; color: #ffffff; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.3); font-weight: 800;" data-cta="radar_home" data-source="home_final">Ver oportunidades</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer') ?>

    <script>
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
            if (typeof trackRadarEvent === 'function') {
                trackRadarEvent({ event_type: 'cta_click', source: source });
            } else {
                $.post('<?= site_url("api/tracking/event") ?>', {
                    event_type: 'cta_click',
                    source: source
                });
            }
        });
    </script>
</body>

</html>
