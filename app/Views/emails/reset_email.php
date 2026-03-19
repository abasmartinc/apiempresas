<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña - APIEmpresas.es</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #2152FF; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 0.8rem; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>APIEmpresas.es</h2>
        </div>
        <p>Hola,</p>
        <p>Has recibido este correo porque hemos recibido una solicitud para restablecer la contraseña de tu cuenta en APIEmpresas.es.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('reset-password/' . $token) ?>" class="btn">Restablecer contraseña</a>
        </p>
        <p>Este enlace de restablecimiento de contraseña caducará en 1 hora.</p>
        <p>Si no has solicitado este cambio, puedes ignorar este mensaje.</p>
        <p>Gracias,<br>El equipo de APIEmpresas.es</p>
        
        <div class="footer">
            <p>Si tienes problemas para hacer clic en el botón "Restablecer contraseña", copia y pega la siguiente URL en tu navegador: <?= site_url('reset-password/' . $token) ?></p>
        </div>
    </div>
</body>
</html>
