<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión · VerificaEmpresas.es</title>
    <meta name="description" content="Accede a tu panel de VerificaEmpresas.es para consultar tu API key, consumo mensual y documentación técnica." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />

    <style>
        .auth-wrapper{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-main{
            flex:1;
            display:flex;
            align-items:center;
        }
        .auth-center{
            max-width:420px;
            margin: 0 auto;
            width:100%;
        }
        .auth-card{
            background:#ffffff;
            border-radius:18px;
            border:1px solid #e6ecf8;
            box-shadow:0 18px 40px rgba(15,23,42,.12);
            padding: 26px;
        }
        .auth-card h1{
            margin:0 0 6px;
            font-size:24px;
            font-weight:800;
            letter-spacing:-.02em;
        }
        .auth-card p{
            margin:0 0 20px;
            color:#4b5563;
            font-size:14px;
        }
        .auth-form{
            display:grid;
            gap:14px;
        }
        .auth-form label{
            font-size:13px;
            font-weight:600;
            margin-bottom:4px;
            display:block;
        }
        .auth-form .input{
            width:100%;
        }
        .auth-submit-row{
            display:flex;
            flex-direction:column;
            gap:10px;
            margin-top:10px;
        }
        .auth-submit-row .btn{
            justify-content:center;
            width:100%;
        }
        .auth-muted{
            font-size:13px;
            color:#6b7280;
            text-align:center;
        }
        .auth-muted a{
            font-weight:600;
            color:#2152ff;
        }
        @media (max-width:640px){
            .auth-card{
                padding:20px 18px 22px;
                border-radius:16px;
            }
        }
        /* ===== Ajuste de ancho para login / registro ===== */

        /* Centrar verticalmente y no tan pegado arriba */
        .auth-main{
            display:flex;
            align-items:center;
            justify-content:center;
            padding:60px 0;
        }

        /* Card más estrecha y cómoda de leer */
        .auth-center{
            max-width: 520px;      /* antes heredaba el container completo */
            width: 100%;
            margin: 0 auto;
        }

        .auth-card{
            width:100%;
            max-width: 520px;      /* forza que el bloque blanco no pase de aquí */
            margin: 0 auto;
        }

        /* En pantallas grandes lo hacemos aún un pelín más compacto */
        @media (min-width: 1280px){
            .auth-center,
            .auth-card{
                max-width: 480px;
            }
        }

    </style>
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <!-- HEADER -->
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO VERIFICAEMPRESAS -->
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10">
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
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1"
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

                <div class="brand-text">
                    <span class="brand-name">Verifica<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación mercantil en segundos</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="/">Volver al inicio</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="/registro">Crear cuenta</a>
            </nav>
        </div>
    </header>

    <!-- MAIN -->
    <main class="auth-main">
        <div class="container auth-center">
            <div class="auth-card">
                <h1>Iniciar sesión</h1>
                <p>Accede a tu panel para ver tu API key, consumo y documentación.</p>

                <form class="auth-form" method="post" action="/login">
                    <div>
                        <label for="email">Correo electrónico</label>
                        <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="input"
                                placeholder="tu@empresa.com"
                        />
                    </div>

                    <div>
                        <label for="password">Contraseña</label>
                        <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="input"
                                placeholder="Tu contraseña"
                        />
                    </div>

                    <div class="auth-submit-row">
                        <button type="submit" class="btn">Entrar</button>
                        <p class="auth-muted">
                            ¿No tienes cuenta?
                            <a href="/registro">Crear cuenta gratis</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="foot-grid">
                <div>
                    <p class="muted">© VerificaEmpresas.es</p>
                    <p class="muted">Datos del BOE/BORME, AEAT, INE, VIES.</p>
                </div>
                <div>
                    <h4 style="margin:0 0 8px">Legal</h4>
                    <a class="minor" href="/privacidad">Privacidad</a><br />
                    <a class="minor" href="/terminos">Términos</a>
                </div>
            </div>
        </div>
    </footer>
</div>

</body>
</html>
