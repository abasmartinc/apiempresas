<!doctype html>
<html lang="es">
<head>
    <head>
        <?=view('partials/head') ?>
        <link rel="stylesheet" href="<?= base_url('public/css/usage.css') ?>" />
    </head>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <!-- ICONO APIEMPRESAS (check limpio, sin triángulo) -->
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Degradado de marca -->
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <!-- Halo del bloque -->
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <!-- Sombra suave del check (no genera triángulos) -->
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <!-- Brillo muy leve arriba-izquierda -->
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <!-- Aro exterior para definir borde en fondos muy claros -->
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <!-- Tarjeta con halo + brillo sutil -->
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <!-- Check principal sin trazo oscuro debajo, con sombra de filtro -->
                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>
            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>
        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="<?=site_url() ?>dashboard">Dashboard</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>billing">Planes y facturación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
        </nav>
        <div class="desktop-only">
            <?php if(!session('logged_in')){ ?>
                <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">
                    <span>Iniciar sesión</span>
                </a>
            <?php } else { ?>
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout">
                    <span>Salir</span>
                </a>
            <?php } ?>
        </div>



    </div>
</header>

<main>
    <!-- HERO PRECIOS -->
    <section class="container" style="padding-top:20px !important; padding-bottom:10px; !important;">
        <h1 class="title">
            <span class="grad">Planes sencillos</span> para empezar hoy
        </h1>
        <p class="subtitle" style="margin-bottom: 0px">
            Sin permanencia. Escala desde un plan Free de pruebas hasta volúmenes de miles de consultas mensuales.
        </p>
    </section>

    <!-- PRICING -->
    <section class="pricing container">
        <div class="tiers">
            <!-- FREE -->
            <div class="tier">
                <div class="badge">Beta</div>
                <h3>Free</h3>
                <p class="muted">Ideal para pruebas, QA y entornos de desarrollo.</p>
                <div class="price">0 €<span style="font-size:16px;font-weight:500;">/mes</span></div>
                <ul class="list">
                    <li><span class="ok"></span><span>100 consultas / mes</span></li>
                    <li><span class="ok"></span><span>Acceso al panel y buscador</span></li>
                    <li><span class="ok"></span><span>Soporte por email estándar</span></li>
                    <li><span class="ok"></span><span>Uso limitado a entornos no productivos</span></li>
                </ul>
                <button class="btn" onclick="window.location.href='/panel'">Seguir con plan Free</button>
            </div>

            <!-- PRO -->
            <div class="tier best">
                <div class="badge">Más usado</div>
                <h3>Pro</h3>
                <p class="muted">Para despachos, consultoras y equipos de compliance.</p>
                <div class="price">39 €<span style="font-size:16px;font-weight:500;">/mes</span></div>
                <ul class="list">
                    <li><span class="ok"></span><span>5.000 consultas / mes incluidas</span></li>
                    <li><span class="ok"></span><span>Límites de rate más amplios</span></li>
                    <li><span class="ok"></span><span>Soporte prioritario por email</span></li>
                    <li><span class="ok"></span><span>Uso en producción permitido</span></li>
                    <li><span class="ok"></span><span>Alertas de consumo (80 % / 100 %)</span></li>
                </ul>
                <button class="btn" onclick="window.location.href='/panel/billing'">
                    Actualizar a Pro
                </button>
            </div>

            <!-- BUSINESS -->
            <div class="tier">
                <div class="badge">A medida</div>
                <h3>Business</h3>
                <p class="muted">Para integraciones de alto volumen o casos especiales.</p>
                <div class="price">Desde 129 €<span style="font-size:16px;font-weight:500;">/mes</span></div>
                <ul class="list">
                    <li><span class="ok"></span><span>+20.000 consultas / mes</span></li>
                    <li><span class="ok"></span><span>IPs dedicadas / whitelists</span></li>
                    <li><span class="ok"></span><span>Soporte técnico directo</span></li>
                    <li><span class="ok"></span><span>Cláusulas específicas de SLA</span></li>
                </ul>
                <button class="btn" onclick="window.location.href='mailto:sales@apiempresas.es'">
                    Hablar con ventas
                </button>
            </div>
        </div>
    </section>
</main>

<?=view('partials/footer') ?>

</body>
</html>

