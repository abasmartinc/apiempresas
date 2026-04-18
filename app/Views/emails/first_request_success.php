<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ya estás usando la API</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #059669; padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .alert-box { background: #fffbeb; border: 1px solid #fef3c7; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #f59e0b; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 16px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Enhorabuena, <?= esc($name) ?>!</h1>
        </div>
        <div class="content">
            <h2>Acabas de realizar tu primera consulta exitosa ⚡</h2>
            <p>Es el primer paso para una integración potente. Ya has comprobado lo sencillo que es obtener datos mercantiles estructurados y fiables.</p>
            
            <div class="alert-box">
                <strong>⚡ Consejo para producción:</strong>
                <p style="margin: 10px 0 0; font-size: 14px;">Si vas a lanzar tu aplicación a producción o esperas recibir tráfico real, te recomendamos activar el <strong>Plan Pro</strong> hoy mismo. Evitarás bloqueos por límite de cuota y tendrás monitorización avanzada.</p>
            </div>

            <div class="cta-container">
                <a href="<?= site_url('billing') ?>" class="btn">👉 Activar Pro ahora</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                Estamos aquí para que tu integración sea un éxito. ¡Seguimos!
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Datos para decisiones inteligentes.</p>
        </div>
    </div>
</body>
</html>
