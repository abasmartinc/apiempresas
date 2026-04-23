<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Paso Final: Tu Email - APIEmpresas',
        'excerptText' => 'Introduce tu email para completar la compra de tu listado.',
    ]) ?>
    <style>
        .register-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            border: 1px solid #e0e7ff;
        }
        .form-group {
            text-align: left;
            margin-bottom: 24px;
        }
        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 2px solid var(--primary);
            box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1);
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
        }
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(33, 82, 255, 0.4);
            filter: brightness(1.1);
        }
        .btn-primary:active {
            transform: scale(0.98);
        }

        /* Preview Cards */
        .preview-list {
            position: relative;
            margin-bottom: 24px;
            cursor: pointer;
        }
        .preview-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 12px;
            margin-bottom: 6px;
            text-align: left;
            opacity: 0.6;
            filter: blur(0.5px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-card-info { flex: 1; }
        .preview-card-name { font-weight: 800; color: #0f172a; font-size: 0.8rem; margin-bottom: 0px; }
        .preview-card-meta { font-size: 0.65rem; color: #64748b; font-weight: 600; display: flex; gap: 6px; }
        .preview-card-score { font-size: 0.65rem; color: #059669; font-weight: 700; margin-top: 2px; }
        .preview-card-btn { background: #F1F5F9; color: #94A3B8; padding: 4px 10px; border-radius: 6px; font-size: 0.6rem; font-weight: 800; }
        
        .preview-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.4), rgba(255,255,255,0.95));
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 12px;
        }
        .preview-overlay-text {
            background: #ffffff;
            color: #0F172A;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 800;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="register-container">
            <?php if (isset($redirect) && strpos($redirect, 'radar') !== false): ?>
                <!-- HEADER COMPACTO PARA RADAR -->
                <div style="display: flex; align-items: center; gap: 20px; text-align: left; margin-bottom: 20px;">
                    <div style="width: 56px; height: 56px; background: #eef2ff; color: var(--primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e0e7ff; font-size: 1.75rem;">
                        ⚡
                    </div>
                    <div>
                        <h1 style="font-size: 1.8rem; font-weight: 950; color: #0f172a; margin-bottom: 4px; letter-spacing: -0.04em; line-height: 1.1;">
                            Accede a empresas listas para ser contactadas antes que tu competencia
                        </h1>
                        <p style="color: #475569; line-height: 1.4; font-weight: 500; font-size: 0.95rem; margin: 0;">
                            Empresas recién creadas que necesitan proveedores en sus primeros días
                        </p>
                    </div>
                </div>

                <div style="background: #FFF7ED; border: 1px solid #FFEDD5; padding: 10px; border-radius: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px; color: #9A3412; font-weight: 800; font-size: 0.85rem;">
                        <span style="display: block; width: 6px; height: 6px; background: #EA580C; border-radius: 50%; box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1);"></span>
                        +94 empresas detectadas HOY — otras empresas ya están contactando estas oportunidades
                    </div>
                </div>
                <!-- Preview Visual Section (Compact) -->
                <div class="preview-list" style="margin-bottom: 16px;" onclick="document.getElementById('email_input').focus()">
                    <div class="preview-card">
                        <div class="preview-card-info">
                            <div class="preview-card-name">TECNO SOLUCIONES SL</div>
                            <div class="preview-card-meta">
                                <span>Hace 2h</span>
                                <span>• 5.000€ – 12.000€</span>
                            </div>
                            <div class="preview-card-score">Alta probabilidad de compra</div>
                        </div>
                        <div class="preview-card-btn">Contactar ahora</div>
                    </div>
                    <div class="preview-card">
                        <div class="preview-card-info">
                            <div class="preview-card-name">DISTRIBUCIONES QUANTUM</div>
                            <div class="preview-card-meta">
                                <span>Hace 5h</span>
                                <span>• 8.000€ – 25.000€</span>
                            </div>
                            <div class="preview-card-score">Alta probabilidad de compra</div>
                        </div>
                        <div class="preview-card-btn">Contactar ahora</div>
                    </div>
                    
                    <div class="preview-overlay">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <div class="preview-overlay-text" style="font-size: 0.8rem; padding: 10px 24px;">Otras empresas ya están trabajando estas oportunidades</div>
                            <div style="font-size: 0.65rem; color: #64748B; font-weight: 700; background: rgba(255,255,255,0.8); padding: 2px 8px; border-radius: 4px;">Accede ahora o llegarás tarde</div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- VERSION PASO FINAL / DESCARGA -->
                <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.01em;">Paso Final</h1>
                <p style="color: #64748b; margin-bottom: 32px; line-height: 1.6;">
                    Introduce tu correo electrónico para procesar el pago y recibir el enlace de descarga de tu listado.
                </p>
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 600;">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('register/quick_store') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect" value="<?= esc($redirect ?? '') ?>">
                <div class="form-group">
                    <label class="form-label">
                        <?= (isset($redirect) && strpos($redirect, 'radar') !== false) ? '' : 'Tu correo electrónico' ?>
                    </label>
                    <input type="email" name="email" id="email_input" class="form-control" placeholder="ejemplo@empresa.com" required autofocus>
                </div>

                <button type="submit" class="btn-primary" style="<?= (isset($redirect) && strpos($redirect, 'radar') !== false) ? 'background: #2563EB;' : '' ?>">
                    <?= (isset($redirect) && strpos($redirect, 'radar') !== false) ? 'Acceder a estas empresas ahora' : 'Continuar al Pago' ?>
                </button>
            </form>

            <?php if (isset($redirect) && strpos($redirect, 'radar') !== false): ?>
                <p style="margin-top: 12px; font-size: 0.85rem; color: #EA580C; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    🚀 Hoy hay +94 empresas esperando ser contactadas
                </p>
                <div style="display: flex; justify-content: center; gap: 16px; margin-top: 16px; font-size: 0.75rem; font-weight: 700; color: #64748B;">
                    <div style="display: flex; align-items: center; gap: 4px;"><span>✔</span> Acceso inmediato</div>
                    <div style="display: flex; align-items: center; gap: 4px;"><span>✔</span> Sin tarjeta</div>
                    <div style="display: flex; align-items: center; gap: 4px;"><span>✔</span> Sin permanencia</div>
                </div>
                <p style="margin-top: 24px; font-size: 0.9rem; color: #1E293B; font-weight: 800;">
                    "Las primeras empresas en contactar son las que se llevan el cliente"
                </p>
<?php else: ?>
                <p style="margin-top: 24px; font-size: 0.85rem; color: #94a3b8; line-height: 1.5;">
                    Recibirás una clave de acceso temporal para descargar el archivo siempre que lo necesites.
                </p>
            <?php endif; ?>
        </div>
    </main>

    <?= view('partials/footer') ?>

    <script>
        // TRACKING: Quick register view
        trackEvent('quick_register_view', { 
            context: '<?= (isset($redirect) && strpos($redirect, 'radar') !== false) ? 'radar' : 'checkout' ?>' 
        });

        document.querySelector('form').addEventListener('submit', (e) => {
            const email = document.getElementById('email_input').value;
            const domain = email.split('@')[1] || 'unknown';
            
            // TRACKING: Quick register submit
            trackEvent('quick_register_submit', { 
                email_domain: domain,
                context: '<?= (isset($redirect) && strpos($redirect, 'radar') !== false) ? 'radar' : 'checkout' ?>'
            });
        });
    </script>
</body>
</html>
