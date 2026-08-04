<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing-success.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url('listados/empresas-nuevas') ?>">
                    <div style="background: linear-gradient(135deg, #9333ea, #d946ef); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        ✨
                    </div>
                </a>
                <div class="brand-text">
                    <span class="brand-name">Copiloto de <span style="color: #9333ea;">Ventas</span></span>
                    <span class="brand-tag">Account-Based Selling Inteligente</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?=site_url('listados/empresas-nuevas') ?>">Buscador de Empresas</a>
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

                    <div class="kicker animate__animated animate__fadeIn" style="animation-delay: 0.2s; color: #9333ea;">SUSCRIPCIÓN ACTIVADA</div>

                    <div class="title-row animate__animated animate__fadeIn" style="animation-delay: 0.4s; justify-content: center;">
                        <h1 style="font-size: 2.5rem; max-width: 800px; margin: 0 auto 20px;">Acceso activado — ya puedes empezar a generar guiones de ventas hiper-personalizados</h1>
                    </div>

                    <p class="sub animate__animated animate__fadeIn" style="animation-delay: 0.6s; font-size: 1.1rem; max-width: 600px; margin: 0 auto 32px; color: #475569;">
                        Has desbloqueado el uso ilimitado del Copiloto AI. Te estamos redirigiendo para que no pierdas ni un segundo.
                    </p>

                    <div class="hero-actions animate__animated animate__fadeIn" style="animation-delay: 0.8s; flex-direction: column; gap: 15px;">
                        <a class="btn btn_primary" href="<?=site_url('/')?>" style="background: linear-gradient(135deg, #9333ea, #d946ef); padding: 20px 40px; font-size: 1.2rem; font-weight: 900; border-radius: 18px; box-shadow: 0 12px 30px rgba(147,51,234,0.4); border: none;">Ir al Panel de Control</a>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <p style="margin: 0; font-size: 0.9rem; color: #9333ea; font-weight: 700;">✨ Multiplica tu tasa de respuesta al instante</p>
                            <p id="redirect-timer" style="margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 600;">Te estamos llevando a tu cuenta en 5...</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let seconds = 5;
                    const timerEl = document.getElementById('redirect-timer');
                    
                    const interval = setInterval(() => {
                        seconds--;
                        if (seconds > 0) {
                            timerEl.textContent = `Te estamos llevando a tu cuenta en ${seconds}...`;
                        } else {
                            timerEl.textContent = 'Redirigiendo...';
                            clearInterval(interval);
                            window.location.href = '<?= site_url('/') ?>';
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
