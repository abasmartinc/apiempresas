<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/login.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>


<div class="auth-wrapper">
    <!-- HEADER -->
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO APIEMPRESAS -->
                <a href="<?=site_url() ?>">
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
                </a>

                <div class="brand-text">
                    <span class="brand-name">Verifica<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación mercantil en segundos</span>
                </div>
            </div>
            <div class="desktop-only">
                <a class="btn btn_header" href="<?=site_url() ?>register">Crear cuenta gratis</a>
            </div>

        </div>
    </header>

    <!-- MAIN -->
    <main class="auth-main ">
        <div class="container auth-center ">
            <?php $showConfetti = (bool) session('message'); ?>

            <?php if ($showConfetti or true): ?>
                <div class="aba-confetti-burst" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="auth-card">
                <h1>Iniciar sesión</h1>
                <p>Accede a tu panel para ver tu API key, consumo y documentación.</p>
                <?php if (session('error')): ?>
                    <div class="auth-alert-error">
                        <?= esc(session('error')) ?>
                    </div>
                <?php endif; ?>
                <?php if (session('message')): ?>
                    <div class="auth-alert-success">
                        <strong><?= esc(session('message')) ?></strong>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?=site_url() ?>login">
                    <?= csrf_field() ?>

                    <div class="">
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
                        <button type="submit" class="btn" id="login-submit">Entrar</button>
                        <p class="auth-muted">
                            ¿No tienes cuenta?
                            <a href="<?=site_url() ?>register">Crear cuenta gratis</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?=view('partials/footer') ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.auth-form');
        const btn  = document.getElementById('login-submit');

        if (form && btn) {
            form.addEventListener('submit', function () {
                // Evitar dobles envíos
                btn.disabled = true;
                btn.textContent = 'Iniciando sesión…';
            });
        }
    });
</script>
<script>
    (function () {
        const container = document.querySelector('.aba-confetti-burst');
        if (!container) return;

        // Paleta “pro”
        const COLORS = ['#2152FF', '#5C7CFF', '#12B48A'];

        // Shapes más serias
        const SHAPES = ['pill', 'diamond', 'line', 'dot'];
        const COUNT  = 48; // un poco menos = más “premium”

        for (let i = 0; i < COUNT; i++) {
            const el = document.createElement('span');
            const shape = SHAPES[Math.floor(Math.random() * SHAPES.length)];
            el.className = `confetti ${shape}`;

            // Color sólido (sin símbolos)
            el.style.background = COLORS[Math.floor(Math.random() * COLORS.length)];

            // Tamaño por forma (controlado, pro)
            if (shape === 'dot') {
                const s = 5 + Math.random() * 3;
                el.style.width = `${s}px`;
                el.style.height = `${s}px`;
                el.style.borderRadius = '999px';
                el.style.setProperty('--spinBase', '0deg');
            } else if (shape === 'pill') {
                const w = 12 + Math.random() * 10;
                const h = 5 + Math.random() * 3;
                el.style.width = `${w}px`;
                el.style.height = `${h}px`;
                el.style.borderRadius = '999px';
                el.style.setProperty('--spinBase', `${Math.floor(Math.random() * 180)}deg`);
            } else if (shape === 'line') {
                const w = 14 + Math.random() * 14;
                const h = 3 + Math.random() * 2;
                el.style.width = `${w}px`;
                el.style.height = `${h}px`;
                el.style.borderRadius = '999px';
                el.style.opacity = '0.85';
                el.style.setProperty('--spinBase', `${Math.floor(Math.random() * 180)}deg`);
            } else if (shape === 'diamond') {
                const s = 8 + Math.random() * 6;
                el.style.width = `${s}px`;
                el.style.height = `${s}px`;
                el.style.borderRadius = '3px';
                // Rombo: rotación base fija a 45deg + variación ligera
                el.style.setProperty('--spinBase', `${45 + Math.floor(Math.random() * 60) - 30}deg`);
            }

            // Micro dispersión en el origen (tubo detrás)
            el.style.transform = `translate(${(-22 + Math.random() * 44).toFixed(1)}px, ${(-14 + Math.random() * 28).toFixed(1)}px)`;

            // Dirección mayormente hacia ARRIBA
            const angle = (-Math.PI / 2) + (Math.random() - 0.5) * (Math.PI / 1.35);

            // Distancia
            const distance = 210 + Math.random() * 230;

            const x = Math.cos(angle) * distance;
            const y = Math.sin(angle) * distance;

            el.style.setProperty('--x', `${x.toFixed(1)}px`);
            el.style.setProperty('--y', `${y.toFixed(1)}px`);

            // Timing
            el.style.animationDelay = `${(Math.random() * 0.22).toFixed(3)}s`;

            container.appendChild(el);
        }

        setTimeout(() => {
            container.innerHTML = '';
        }, 3000);
    })();
</script>



</body>
</html>
