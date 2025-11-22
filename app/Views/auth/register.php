<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear cuenta gratuita · VerificaEmpresas.es</title>
    <meta name="description" content="Crea tu cuenta gratuita en VerificaEmpresas.es y consigue tu API key para validar CIF y empresas españolas en segundos." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />

    <!-- EXTRA mínimo para la maquetación del registro (puedes moverlo a styles.css) -->
    <style>
        .auth-wrapper{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-main{
            flex: 1;
            display: flex;
            align-items: center;
        }
        .auth-grid{
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 32px;
            align-items: center;
            width: 100%;
            margin: 40px auto 60px;
        }
        .auth-copy-eyebrow{
            display:inline-block;
            font:700 11px/1 Inter, system-ui, sans-serif;
            letter-spacing:.14em;
            text-transform:uppercase;
            color:#1e2a55;
            background:#eef2ff;
            border:1px solid #dfe6ff;
            padding:6px 10px;
            border-radius:999px;
            margin-bottom:10px;
        }
        .auth-copy h1{
            font-size: clamp(24px, 3vw, 30px);
            line-height: 1.2;
            margin: 6px 0 12px;
            font-weight: 900;
            letter-spacing: -.02em;
        }
        .auth-copy p{
            margin: 0 0 12px;
            color:#4b5563;
        }
        .auth-bullets{
            margin-top: 14px;
            padding-left: 0;
            list-style: none;
        }
        .auth-bullets li{
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
            color:#4b5563;
            font-size: 14px;
        }
        .auth-bullets li span.bullet-dot{
            margin-top: 4px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg,#2152ff,#12b48a);
            box-shadow: 0 0 0 4px rgba(33,82,255,.12);
            flex-shrink: 0;
        }
        .auth-card{
            background:#ffffff;
            border-radius: 18px;
            border:1px solid #e6ecf8;
            box-shadow: 0 18px 40px rgba(15,23,42,.12);
            padding: 24px 24px 26px;
        }
        .auth-card h2{
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing:-.02em;
        }
        .auth-card p{
            margin: 0 0 18px;
            color:#4b5563;
            font-size:14px;
        }
        .auth-form{
            display: grid;
            gap: 12px;
        }
        .auth-form label{
            display:block;
            font-size: 13px;
            font-weight: 600;
            color:#111827;
            margin-bottom: 4px;
        }
        .auth-form .input{
            width: 100%;
        }
        .auth-row-inline{
            display:flex;
            gap:12px;
        }
        .auth-checkbox{
            display:flex;
            align-items:flex-start;
            gap:8px;
            font-size: 13px;
            color:#4b5563;
            margin: 6px 0 4px;
        }
        .auth-checkbox input[type="checkbox"]{
            margin-top: 3px;
        }
        .auth-submit-row{
            display:flex;
            flex-direction:column;
            gap:8px;
            margin-top: 8px;
        }
        .auth-submit-row .btn{
            justify-content: center;
            width: 100%;
        }
        .auth-muted{
            font-size: 13px;
            color:#6b7280;
        }
        .auth-muted a{
            color:#2152ff;
            font-weight:600;
        }

        @media (max-width: 980px){
            .auth-grid{
                grid-template-columns: 1fr;
                margin: 32px auto 40px;
            }
        }
        @media (max-width: 640px){
            .auth-card{
                padding: 20px 18px 22px;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <!-- HEADER reutilizando tu marca -->
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO VERIFICAEMPRESAS -->
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
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
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
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
                <a class="minor" href="/#precios">Precios</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="/#docs">Docs</a>
            </nav>

            <div class="desktop-only">
                <a class="btn btn_header" href="/login">Iniciar sesión</a>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="auth-main">
        <section class="container">
            <div class="auth-grid">
                <!-- COLUMNA IZQUIERDA: COPY -->
                <div class="auth-copy">
                    <span class="auth-copy-eyebrow">Crear cuenta gratuita</span>
                    <h1>Empieza a validar empresas españolas en minutos.</h1>
                    <p>
                        Crea tu cuenta, consigue tu <strong>API key</strong> y conéctate al
                        registro mercantil, AEAT, INE y VIES desde tu propio producto.
                    </p>
                    <p>
                        Pensado para <strong>developers</strong>, gestorías y
                        <strong>departamentos de riesgo/compliance</strong> que necesitan datos fiables,
                        en tiempo real y con buena documentación.
                    </p>

                    <ul class="auth-bullets">
                        <li>
                            <span class="bullet-dot"></span>
                            <span><strong>Plan Free</strong> con 2.000 consultas/mes para desarrollo y pruebas.</span>
                        </li>
                        <li>
                            <span class="bullet-dot"></span>
                            <span><strong>API REST</strong> sencilla, JSON limpio y ejemplos listos para copiar.</span>
                        </li>
                        <li>
                            <span class="bullet-dot"></span>
                            <span>Datos procedentes de <strong>BOE/BORME, AEAT, INE y VIES</strong> con enlace a fuente oficial.</span>
                        </li>
                    </ul>
                </div>

                <!-- COLUMNA DERECHA: FORMULARIO -->
                <div class="auth-card">
                    <h2>Crea tu cuenta gratis</h2>
                    <p>Sin tarjeta, sin permanencias. Solo necesitas un correo profesional.</p>

                    <form class="auth-form" method="post" action="/signup">
                        <!-- Nombre + Empresa -->
                        <div class="auth-row-inline">
                            <div style="flex:1;">
                                <label for="name">Nombre y apellidos</label>
                                <input
                                    class="input"
                                    type="text"
                                    id="name"
                                    name="name"
                                    autocomplete="name"
                                    required
                                    placeholder="Ej. Ana García López"
                                />
                            </div>
                            <div style="flex:1;">
                                <label for="company">Empresa (opcional)</label>
                                <input
                                    class="input"
                                    type="text"
                                    id="company"
                                    name="company"
                                    autocomplete="organization"
                                    placeholder="Ej. Gestoría Centro SL"
                                />
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email">Correo electrónico</label>
                            <input
                                class="input"
                                type="email"
                                id="email"
                                name="email"
                                autocomplete="email"
                                required
                                placeholder="tu@empresa.com"
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password">Contraseña</label>
                            <input
                                class="input"
                                type="password"
                                id="password"
                                name="password"
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder="Mínimo 8 caracteres"
                            />
                        </div>

                        <!-- Checkbox legal -->
                        <div class="auth-checkbox">
                            <input type="checkbox" id="terms" name="terms" required />
                            <label for="terms" style="margin:0; font-weight:400;">
                                Acepto la <a href="/privacidad" target="_blank" rel="noopener">Política de privacidad</a>
                                y los <a href="/terminos" target="_blank" rel="noopener">Términos de servicio</a>.
                            </label>
                        </div>

                        <!-- CTA + texto pequeño -->
                        <div class="auth-submit-row">
                            <button type="submit" class="btn">
                                Crear cuenta gratis
                            </button>
                            <p class="auth-muted">
                                Al crear tu cuenta activamos automáticamente el <strong>plan Free</strong>
                                con 2.000 consultas/mes para que integres la API sin compromiso.
                            </p>
                            <p class="auth-muted">
                                ¿Ya tienes cuenta?
                                <a href="/login">Inicia sesión</a>.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER simple -->
    <footer>
        <div class="container">
            <div class="foot-grid">
                <div>
                    <div class="brand">
                        <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="ve-g2" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#2152FF"/>
                                    <stop offset=".65" stop-color="#5C7CFF"/>
                                    <stop offset="1" stop-color="#12B48A"/>
                                </linearGradient>
                                <filter id="ve-cardShadow2" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                                </filter>
                                <filter id="ve-checkShadow2" x="-30%" y="-30%" width="160%" height="160%">
                                    <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                                </filter>
                                <radialGradient id="ve-gloss2" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                                gradientTransform="translate(20 16) rotate(45) scale(28)">
                                    <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                    <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                                </radialGradient>
                                <linearGradient id="ve-rim2" x1="12" y1="52" x2="52" y2="12">
                                    <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                    <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                                </linearGradient>
                            </defs>
                            <g filter="url(#ve-cardShadow2)">
                                <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g2)"/>
                                <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss2)"/>
                                <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim2)"/>
                            </g>
                            <path d="M18 33 L28 43 L46 22"
                                  stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                                  fill="none" filter="url(#ve-checkShadow2)"/>
                        </svg>
                        <div class="brand-text">
                            <span class="brand-name">Verifica<span class="grad">Empresas</span>.es</span>
                            <span class="brand-tag">Verificación mercantil en segundos</span>
                        </div>
                    </div>
                    <p class="muted">
                        Datos procedentes de fuentes públicas: BOE/BORME, AEAT, INE, VIES. No somos servicio oficial del BOE.
                    </p>
                </div>
                <div>
                    <h4 style="margin:0 0 8px">Producto</h4>
                    <a class="minor" href="/">Landing</a><br />
                    <a class="minor" href="/#docs">Documentación</a><br />
                    <a class="minor" href="/#precios">Precios</a>
                </div>
                <div>
                    <h4 style="margin:0 0 8px">Legal</h4>
                    <a class="minor" href="/privacidad">Privacidad</a><br />
                    <a class="minor" href="/terminos">Términos</a><br />
                    <a class="minor" href="/contacto">Contacto</a>
                </div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
