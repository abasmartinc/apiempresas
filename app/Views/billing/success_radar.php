<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing-success.css') ?>" />
    <style>
        .radar-accent { color: #f59e0b !important; }
        .step-orb--radar { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }
    </style>
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url('radar') ?>">
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
                        </defs>
                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        </g>
                        <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#ve-checkShadow)"/>
                    </svg>
                </a>
                <div class="brand-text">
                    <span class="brand-name">Radar<span class="grad">PRO</span></span>
                    <span class="brand-tag">Captación B2B inteligente</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?=site_url('radar') ?>">Ir al Radar</a>
                <span class="nav-dot">•</span>
                <a class="minor" href="<?=site_url('billing/invoices') ?>">Mis Facturas</a>
            </nav>

            <div class="desktop-only">
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout"><span>Salir</span></a>
            </div>
        </div>
    </header>

    <main class="success-main">
        <div class="container">
            <!-- HERO -->
            <div class="success-hero" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; flex-direction: column;">
                <div class="success-hero__center">
                    <!-- Animación de Check -->
                    <div class="animate__animated animate__fadeInDown" style="margin-bottom: 30px;">
                        <div style="width: 80px; height: 80px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>

                    <div class="kicker animate__animated animate__fadeIn" style="animation-delay: 0.2s;">Suscripción Activada</div>

                    <div class="title-row animate__animated animate__fadeIn" style="animation-delay: 0.4s; justify-content: center;">
                        <h1 style="font-size: 2.5rem; max-width: 800px; margin: 0 auto 20px;">Acceso activado — ya puedes empezar a contactar empresas ahora mismo</h1>
                    </div>

                    <p class="sub animate__animated animate__fadeIn" style="animation-delay: 0.6s; font-size: 1.1rem; max-width: 600px; margin: 0 auto 32px; color: #475569;">
                        Has desbloqueado el acceso completo. Te estamos redirigiendo para que no pierdas ni un segundo.
                    </p>

                    <div class="hero-actions animate__animated animate__fadeIn" style="animation-delay: 0.8s; flex-direction: column; gap: 15px;">
                        <a class="btn btn_primary" href="<?=site_url('radar')?>" style="background: #2563eb; padding: 20px 40px; font-size: 1.2rem; font-weight: 900; border-radius: 18px; box-shadow: 0 12px 30px rgba(37,99,235,0.4);">Ver oportunidades activas ahora</a>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <p style="margin: 0; font-size: 0.9rem; color: #e11d48; font-weight: 700;">⚡ Algunas de estas empresas pueden estar siendo contactadas ahora mismo</p>
                            <p id="redirect-timer" style="margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 600;">Te estamos llevando a tus oportunidades en 3...</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let seconds = 3;
                    const timerEl = document.getElementById('redirect-timer');
                    
                    const interval = setInterval(() => {
                        seconds--;
                        if (seconds > 0) {
                            timerEl.textContent = `Te estamos llevando a tus oportunidades en ${seconds}...`;
                        } else {
                            timerEl.textContent = 'Redirigiendo...';
                            clearInterval(interval);
                            window.location.href = '<?= site_url('radar') ?>';
                        }
                    }, 1000);
                });
            </script>

            <style>
                .success-hero__center h1 {
                    line-height: 1.1;
                    letter-spacing: -0.02em;
                }
                @media (max-width: 768px) {
                    .success-hero__center h1 { font-size: 1.8rem; }
                }
            </style>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>
</body>
</html>
