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

<main class="usage-main">
    <div class="container">
        <!-- CABECERA -->
        <div class="usage-header">
            <div>
                <h1>Uso de la API</h1>
                <p>Revisa tus consultas y controla cuánto estás consumiendo en cada periodo.</p>
            </div>
            <form class="usage-filters">
                <div>
                    <label for="range">Rango</label><br>
                    <select id="range" name="range">
                        <option value="30">Últimos 30 días</option>
                        <option value="7">Últimos 7 días</option>
                        <option value="today">Hoy</option>
                        <option value="custom">Personalizado…</option>
                    </select>
                </div>
                <div>
                    <label for="from">Desde</label><br>
                    <input type="date" id="from" name="from">
                </div>
                <div>
                    <label for="to">Hasta</label><br>
                    <input type="date" id="to" name="to">
                </div>
                <div>
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn secondary">Actualizar</button>
                </div>
            </form>
        </div>

        <!-- FRANJA DE KPIs (usa clases ve-stat-strip ya definidas en tu CSS) -->
        <div class="ve-stat-strip">
            <div class="ve-stat">
                <div class="ve-stat__label">Consultas en este mes</div>
                <div class="ve-stat__value">12.430</div>
            </div>
            <div class="ve-stat__divider"></div>
            <div class="ve-stat">
                <div class="ve-stat__label">Consultas hoy</div>
                <div class="ve-stat__value">312</div>
            </div>
            <div class="ve-stat__divider"></div>
            <div class="ve-stat ve-stat--sources">
                <div class="ve-stat__label">Porcentaje de tu límite</div>
                <div class="ve-stat__value">62 % del plan actual</div>
            </div>
        </div>

        <!-- LAYOUT PRINCIPAL -->
        <div class="usage-layout">
            <!-- IZQUIERDA: gráfico + desglose -->
            <section class="usage-card">
                <h2>Consultas por día</h2>
                <p>Distribución del número de consultas realizadas en el periodo seleccionado.</p>

                <div class="chart-wrapper">
                    <!-- Aquí luego montas Chart.js o similar -->
                    <div class="chart-placeholder">
                        (Gráfico de líneas — consultas por día)
                    </div>
                </div>

                <div class="usage-table-wrapper">
                    <table class="usage-table">
                        <thead>
                        <tr>
                            <th>Tipo de consulta</th>
                            <th>Últimos 30 días</th>
                            <th>Porcentaje</th>
                            <th>Hoy</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <span class="usage-pill">Ficha mercantil básica</span>
                            </td>
                            <td>8.920</td>
                            <td>71%</td>
                            <td>210</td>
                        </tr>
                        <tr>
                            <td>
                                <span class="usage-pill">Directivos &amp; administradores</span>
                            </td>
                            <td>2.140</td>
                            <td>17%</td>
                            <td>63</td>
                        </tr>
                        <tr>
                            <td>
                                <span class="usage-pill">Riesgos / incidencias</span>
                            </td>
                            <td>870</td>
                            <td>7%</td>
                            <td>24</td>
                        </tr>
                        <tr>
                            <td>
                                <span class="usage-pill">Otras (demo, sandbox)</span>
                            </td>
                            <td>500</td>
                            <td>5%</td>
                            <td>15</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- DERECHA: límite, alertas, call to action -->
            <section class="usage-card">
                <h2>Límites de tu plan</h2>
                <p>Monitoriza cómo vas respecto al límite de consultas incluido en tu suscripción.</p>

                <div class="limit-block">
                    <h3>Uso del plan actual</h3>
                    <p style="margin-bottom:4px;">
                        Plan <strong>Pro 50k</strong> — 50.000 consultas/mes incluidas.
                    </p>
                    <div class="limit-bar">
                        <div class="limit-bar-inner"></div>
                    </div>
                    <div class="limit-meta">
                        <span>31.200 usadas este mes</span>
                        <span>Quedan 18.800 consultas</span>
                    </div>
                </div>

                <div class="limit-block" style="margin-top:14px;">
                    <h3>Alertas</h3>
                    <ul class="alert-list">
                        <li>
                            <span class="badge-dot"></span>
                            <div>
                                <strong>Has superado el 60% de tu límite mensual.</strong><br>
                                Te avisaremos por email al pasar el 80% para que puedas ampliar antes de llegar al tope.
                            </div>
                        </li>
                        <li class="info">
                            <span class="badge-dot"></span>
                            <div>
                                <strong>No tienes alertas críticas.</strong><br>
                                Si llegas al 100% de tu cuota, las nuevas consultas se cobrarán a precio por exceso.
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="limit-block" style="margin-top:14px;">
                    <h3>Subir de plan</h3>
                    <p>
                        Si sueles estar por encima del 70–80% de tu cuota, suele salir más rentable subir al siguiente
                        plan en lugar de pagar exceso por consulta.
                    </p>
                    <a href="<?=site_url() ?>prices" class="btn secondary" style="margin-top:6px;">
                        Ver planes disponibles
                    </a>
                </div>
            </section>
        </div>
    </div>
</main>

<?=view('partials/footer') ?>

</body>
</html>

