<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Errors.pageNotFound') ?> | APIEmpresas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --bg-dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 40%);
            font-family: 'Inter', sans-serif;
            color: #f8fafc;
            overflow-x: hidden;
            padding: 2rem 0;
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
        }

        /* Glassmorphism Card */
        .error-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 3rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-size: 6rem;
            font-weight: 850;
            line-height: 1;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -4px;
            filter: drop-shadow(0 10px 10px rgba(37, 99, 235, 0.3));
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        /* Suggestion Links */
        .suggestions {
            margin-top: 2rem;
            text-align: left;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .suggestions-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            display: block;
        }

        .links-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.938rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(37, 99, 235, 0.3);
            color: #ffffff;
            transform: translateX(4px);
        }

        .nav-link svg {
            width: 18px;
            height: 18px;
            color: var(--primary-light);
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            margin-top: 2rem;
            padding: 0.875rem 2rem;
            background: #ffffff;
            color: #0f172a;
            text-decoration: none;
            font-weight: 600;
            border-radius: 100px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
            background: #f8fafc;
        }

        /* Background elements */
        .circle {
            position: absolute;
            border-radius: 50%;
            z-index: 1;
            filter: blur(60px);
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            background: rgba(37, 99, 235, 0.1);
            top: -150px;
            left: -150px;
        }

        .circle-2 {
            width: 250px;
            height: 250px;
            background: rgba(16, 185, 129, 0.1);
            bottom: -100px;
            right: -100px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .error-code { font-size: 4.5rem; }
            h1 { font-size: 1.5rem; }
            .links-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="container">
        <div class="error-card">
            <div class="error-code">404</div>
            <h1>¡Vaya! No encontramos esa página</h1>
            
            <p>
                Parece que el enlace que has seguido ya no existe o la dirección es incorrecta. 
                ¿Quizás buscabas alguna de estas secciones?
            </p>

            <div class="suggestions">
                <span class="suggestions-title">Enlaces de interés</span>
                <div class="links-grid">
                    <a href="<?= site_url('search') ?>" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        Buscar empresas
                    </a>
                    <a href="<?= site_url('empresas-nuevas') ?>" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                        Radar Hub
                    </a>
                    <a href="<?= site_url('directorio') ?>" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path><path d="M8 7h6"></path><path d="M8 11h8"></path></svg>
                        Directorio empresas
                    </a>
                    <a href="<?= site_url('blog') ?>" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4Z"></path></svg>
                        Blog y noticias
                    </a>
                </div>
            </div>

            <a href="<?= site_url() ?>" class="btn-home">
                Ir a la Home
            </a>
        </div>
    </div>
</body>
</html>
