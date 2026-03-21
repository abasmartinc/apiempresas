<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establece tu contraseña - APIEmpresas.es</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #2152FF 0%, #12B48A 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .cta-container { text-align: center; margin: 35px 0; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: background 0.2s; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #64748b; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>APIEmpresas.es</h1>
        </div>
        <div class="content">
            <h2>¡Hola!</h2>
            <p>Gracias por registrarte en <strong>APIEmpresas.es</strong>. Hemos creado tu cuenta correctamente para que puedas continuar con tu descarga o activación de radar.</p>
            
            <p>Por seguridad, necesitamos que establezcas una contraseña para que puedas acceder a tu panel de control siempre que lo necesites.</p>

            <div class="cta-container">
                <a href="<?= site_url('reset-password/' . $token) ?>" class="btn">Establecer mi contraseña</a>
            </div>
            
            <p>Este enlace es válido durante las próximas 48 horas. Si no has solicitado este registro, puedes ignorar este mensaje.</p>

            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                Si tienes alguna duda, responde a este correo y nuestro equipo te ayudará.
            </p>
        </div>
        <div class="footer">
            <p>Has recibido este correo porque te has registrado en <a href="https://apiempresas.es">APIEmpresas.es</a>.</p>
            <p>&copy; <?= date('Y') ?> APIEmpresas - Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
