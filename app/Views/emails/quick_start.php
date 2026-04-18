<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba la API en menos de 30 segundos</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #2152FF 0%, #12B48A 100%); padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>APIEmpresas.es</h1>
        </div>
        <div class="content">
            <h2>¡Hola, <?= esc($name) ?>!</h2>
            <p>Hemos visto que ya tienes tu cuenta lista, pero aún no has realizado tu primera consulta.</p>
            
            <p>Hemos diseñado un <strong>probador interactivo</strong> en tu panel para que puedas validar una empresa real en menos de 30 segundos, sin escribir una sola línea de código.</p>

            <div class="cta-container">
                <a href="<?= site_url('dashboard') ?>" class="btn">👉 Probar API ahora</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                Es la forma más rápida de ver cómo la API puede ayudarte a automatizar tus procesos. ¡Te esperamos en el dashboard!
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Verificación mercantil en tiempo real.</p>
        </div>
    </div>
</body>
</html>
