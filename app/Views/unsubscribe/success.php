<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10B981; /* Verde para éxito */
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
            background: #ecfdf5;
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
            background-color: #f1f5f9;
            color: var(--text-main);
        }

        .btn:hover {
            background-color: #e2e8f0;
            transform: translateY(-1px);
        }

        .logo {
            font-weight: 800;
            color: #2152FF;
            font-size: 20px;
            margin-bottom: 40px;
            display: inline-block;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="<?= site_url() ?>" class="logo">APIEmpresas.es</a>
        
        <div class="icon-container">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h1>Baja procesada con éxito</h1>
        <p>Hemos actualizado tus preferencias. No recibirás más correos automáticos ni manuales de nuestra parte a partir de ahora.</p>

        <a href="<?= site_url() ?>" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
