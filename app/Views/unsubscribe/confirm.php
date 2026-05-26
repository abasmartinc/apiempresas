<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2152FF;
            --primary-hover: #1a41cc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: var(--bg-card);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            text-align: center;
        }

        .icon-container {
            width: 64px;
            height: 64px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.025em;
        }

        p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .email-display {
            font-weight: 600;
            color: var(--text-main);
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            margin-bottom: 12px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid #e2e8f0;
        }

        .btn-outline:hover {
            background-color: #f8fafc;
            color: var(--text-main);
        }

        .logo {
            font-weight: 800;
            color: var(--primary);
            font-size: 20px;
            margin-bottom: 40px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="<?= site_url() ?>" class="logo">APIEmpresas.es</a>
        
        <div class="icon-container">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>

        <h1>¿Dejar de recibir correos?</h1>
        <p>Lamentamos que quieras irte. Si confirmas, dejaremos de enviar notificaciones y actualizaciones a <span class="email-display"><?= esc($email) ?></span>.</p>

        <form action="<?= site_url('unsubscribe/confirm') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= esc($email) ?>">
            <input type="hidden" name="hash" value="<?= esc($hash) ?>">
            
            <button type="submit" class="btn btn-primary">Sí, darme de baja</button>
            <a href="<?= site_url('dashboard') ?>" class="btn btn-outline">He cambiado de opinión</a>
        </form>
    </div>
</body>
</html>
