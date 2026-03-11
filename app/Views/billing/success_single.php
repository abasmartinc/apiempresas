<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => '¡Pago completado! - APIEmpresas',
        'excerptText' => 'Tu descarga está lista.',
    ]) ?>
    <style>
        .success-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 40px;
            padding: 0 20px;
        }
        @media (max-width: 950px) {
            .success-wrapper { grid-template-columns: 1fr; }
        }
        .success-card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .check-icon {
            width: 64px;
            height: 64px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 20px 40px;
            background: #0f172a;
            color: white;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 900;
            font-size: 1.25rem;
            margin-top: 24px;
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3);
            transition: transform 0.2s;
        }
        .btn-download:hover { transform: translateY(-2px); }
        
        .upsell-card {
            background: linear-gradient(135deg, #334155, #1e293b);
            border-radius: 24px;
            padding: 40px;
            color: white;
            text-align: left;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .upsell-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(33, 82, 255, 0.2) 0%, transparent 70%);
            pointer-events: none;
        }
        
        /* CONFETTI BURST (detrás del card) */
        .aba-confetti-burst {
            position: absolute;
            inset: -48px;
            pointer-events: none;
            overflow: visible;
            z-index: 1;
        }
        .aba-confetti-burst .confetti {
            position: absolute;
            top: 52%;
            left: 50%;
            width: 8px;
            height: 8px;
            opacity: 0.92;
            transform-origin: center;
            animation: confetti-burst 2.7s cubic-bezier(.16,.84,.22,1) forwards;
            will-change: transform, opacity;
            filter: blur(.12px);
        }
        @keyframes confetti-burst {
            0% {
                transform: translate(0, 0) scale(0.55) rotate(var(--spinBase, 0deg));
                opacity: 0;
            }
            10% { opacity: 1; }
            60% { opacity: 1; }
            100% {
                transform: translate(var(--x), var(--y)) scale(1) rotate(calc(var(--spinBase, 0deg) + 520deg));
                opacity: 0;
            }
        }
        .aba-confetti-burst .confetti.dot { width: 6px; height: 6px; border-radius: 999px; }
        .aba-confetti-burst .confetti.pill { width: 14px; height: 6px; border-radius: 999px; }
        .aba-confetti-burst .confetti.diamond { width: 10px; height: 10px; border-radius: 3px; transform: rotate(45deg); }
        .aba-confetti-burst .confetti.line { width: 18px; height: 3px; border-radius: 999px; opacity: 0.85; }
        @media (prefers-reduced-motion: reduce) { .aba-confetti-burst { display:none; } }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="success-wrapper">
            <!-- IZQUIERDA: PAGO Y DESCARGA -->
            <div style="position: relative;">
                <div class="aba-confetti-burst" aria-hidden="true"></div>
                <div class="success-card" style="position: relative; z-index: 2;">
                    <div class="check-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>

                    <h1 style="font-size: 2.5rem; font-weight: 900; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.02em;">¡Pago completado!</h1>
                    <p style="color: #64748b; font-size: 1.1rem; line-height: 1.5; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Tu listado oficial ha sido procesado correctamente. Ya puedes descargarlo y empezar a trabajar con los datos.
                    </p>

                    <a href="<?= esc($download_url) ?>" class="btn-download">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar Listado (.xlsx)
                    </a>

                    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600;">
                        REF: #<?= esc($order_ref) ?> | Formato: Microsoft Excel / CSV
                    </div>
                </div>
            </div>

            <!-- DERECHA: UPSELL RADAR -->
            <div class="upsell-card">
                <div style="display: inline-block; background: #2152ff; color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.05em; align-self: flex-start;">
                    Acceso Ilimitado
                </div>
                
                <h3 style="font-size: 1.75rem; font-weight: 900; margin-bottom: 16px; line-height: 1.2;">Mientras tanto puedes probar Radar gratis</h3>
                
                <p style="color: #cbd5e1; line-height: 1.6; margin-bottom: 28px; font-size: 1rem;">
                    No esperes a comprar listados uno a uno. Con Radar B2B recibes alertas diarias de nuevas empresas y descargas ilimitadas de cualquier sector.
                </p>
                
                <ul style="list-style: none; padding: 0; margin: 0 0 32px 0;">
                    <?php foreach (['Descargas ilimitadas', 'Alertas diarias por email', 'Filtros avanzados'] as $feat): ?>
                    <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: #f8fafc; font-weight: 600; font-size: 0.95rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $feat ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= site_url('radar') ?>" style="display: inline-flex; justify-content: center; align-items: center; gap: 10px; background: radial-gradient(circle at 0% 0%, #fefce8 0, #facc15 35%, #f97316 100%); color: #0f172a; font-weight: 800; text-decoration: none; font-size: 1.15rem; padding: 14px 24px; border-radius: 12px; box-shadow: 0 12px 30px rgba(249, 115, 22, 0.4); transition: transform 0.2s, box-shadow 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 16px 40px rgba(249, 115, 22, 0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 12px 30px rgba(249, 115, 22, 0.4)';">
                    Probar Radar gratis <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>

    <script>
        (function () {
            const container = document.querySelector('.aba-confetti-burst');
            if (!container) return;

            const COLORS = ['#2152FF', '#5C7CFF', '#12B48A', '#facc15', '#f97316'];
            const SHAPES = ['pill', 'diamond', 'line', 'dot'];
            const COUNT  = 60;

            for (let i = 0; i < COUNT; i++) {
                const el = document.createElement('span');
                const shape = SHAPES[Math.floor(Math.random() * SHAPES.length)];
                el.className = `confetti ${shape}`;

                el.style.background = COLORS[Math.floor(Math.random() * COLORS.length)];

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
                    el.style.setProperty('--spinBase', `${45 + Math.floor(Math.random() * 60) - 30}deg`);
                }

                el.style.transform = `translate(${(-20 + Math.random() * 40).toFixed(1)}px, ${(-12 + Math.random() * 24).toFixed(1)}px)`;

                const angle = (-Math.PI / 2) + (Math.random() - 0.5) * (Math.PI / 1.3);
                const distance = 220 + Math.random() * 240;

                const x = Math.cos(angle) * distance;
                const y = Math.sin(angle) * distance;

                el.style.setProperty('--x', `${x.toFixed(1)}px`);
                el.style.setProperty('--y', `${y.toFixed(1)}px`);

                el.style.animationDelay = `${(Math.random() * 0.22).toFixed(3)}s`;

                container.appendChild(el);
            }

            setTimeout(() => {
                container.innerHTML = '';
            }, 3200);
        })();
    </script>
</body>
</html>
