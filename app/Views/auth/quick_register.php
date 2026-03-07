<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Paso Final: Tu Email - APIEmpresas',
        'excerptText' => 'Introduce tu email para completar la compra de tu listado.',
    ]) ?>
    <style>
        .register-container {
            max-width: 450px;
            margin: 80px auto;
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
            border: 1px solid #cbd5e1;
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1);
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
            transition: transform 0.2s;
        }
        .btn-primary:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="register-container">
            <div class="icon-circle">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            </div>
            
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.01em;">Paso Final</h1>
            <p style="color: #64748b; margin-bottom: 32px; line-height: 1.6;">
                Introduce tu correo electrónico para procesar el pago y recibir el enlace de descarga de tu listado.
            </p>

            <?php if (session()->has('error')): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 600;">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('register/quick_store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Tu correo electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="ejemplo@empresa.com" required autofocus>
                </div>

                <button type="submit" class="btn-primary">
                    Continuar al Pago
                </button>
            </form>

            <p style="margin-top: 24px; font-size: 0.85rem; color: #94a3b8; line-height: 1.5;">
                Recibirás una clave de acceso temporal para descargar el archivo siempre que lo necesites.
            </p>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
