<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <style>
        .test-card {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .price-tag {
            font-size: 3.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .info-text {
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .env-badge {
            background: #eff6ff;
            color: #1e40af;
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid #bfdbfe;
        }
        .btn-stripe {
            width: 100%;
            justify-content: center;
            padding: 14px;
            font-size: 1.1rem;
            background: #635bff; /* Color nativo de Stripe */
            color: white;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        .btn-stripe:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 91, 255, 0.3);
        }
    </style>
</head>
<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 60px 0;">
        <div style="margin-bottom: 3rem; text-align: center;">
            <h1 class="title">Prueba de Suscripción Stripe</h1>
            <p class="subtitle">Creación de suscripción dinámica de 1€ para validación de webhooks y flujo en producción.</p>
        </div>

        <div class="test-card">
            <div class="env-badge">
                <svg style="width: 14px; height: 14px; display: inline; vertical-align: text-bottom; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Petición Real hacia Stripe
            </div>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem;">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <h2 style="margin: 0; font-size: 1.5rem; color: #334155; font-weight: 600;"><?= esc($plan->name) ?></h2>
            <div class="price-tag">
                <?= esc($plan->price_monthly) ?> € <span style="font-size: 1rem; color: #94a3b8; font-weight: 500;">/mes</span>
            </div>
            
            <p class="info-text">
                Al hacer clic en el botón inferior, serás redirigido a Stripe para iniciar una suscripción real.
                Como usamos <strong>price_data</strong>, el producto y plan se autogeneran en Stripe dinámicamente.
            </p>

            <form action="<?= site_url('admin/stripe-test/checkout') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="btn-stripe">
                    <svg style="width: 22px; height: 22px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Pagar y Suscribirse (1€)
                </button>
            </form>
            
            <div style="margin-top: 20px; font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                Esta acción generará un cargo real si usas una tarjeta real.
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
