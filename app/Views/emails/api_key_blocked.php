<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alerta de Seguridad: API Key Bloqueada</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="display: inline-block; background-color: #fef2f2; color: #ef4444; padding: 15px; border-radius: 50%; width: 50px; height: 50px; line-height: 50px; font-size: 24px;">
                ⚠️
            </div>
        </div>
        
        <h2 style="color: #0f172a; text-align: center; margin-top: 0;">Acción de Seguridad Requerida</h2>
        
        <p style="color: #475569; font-size: 16px; line-height: 1.6;">
            Hola,
        </p>
        
        <p style="color: #475569; font-size: 16px; line-height: 1.6;">
            Nos ponemos en contacto contigo desde el equipo de seguridad de <strong>APIEmpresas</strong> porque hemos detectado un intento de conexión sospechoso a la API usando tu credencial (API Key).
        </p>

        <div style="background-color: #f1f5f9; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #1e293b; font-size: 15px;">
                <strong>Detalles de la anomalía:</strong><br>
                Se han detectado peticiones desde un país no autorizado (<strong><?= esc($countryCode) ?></strong>).
            </p>
        </div>
        
        <p style="color: #475569; font-size: 16px; line-height: 1.6;">
            Como medida preventiva y para proteger tus créditos y tus datos, <strong>hemos bloqueado automáticamente tu API Key actual</strong>. Tus sistemas que dependan de esta clave dejarán de tener acceso a la API (Error 403) temporalmente.
        </p>

        <h3 style="color: #0f172a; margin-top: 30px;">¿Qué debes hacer ahora?</h3>
        <ol style="color: #475569; font-size: 16px; line-height: 1.6;">
            <li>Si tú o tu equipo habéis desplegado la aplicación en servidores ubicados en el país indicado, por favor, contacta con <strong>soporte@apiempresas.es</strong> para que añadamos esa región a tu lista blanca.</li>
            <li>Si no reconoces esta actividad, es probable que tu API Key se haya filtrado (ej. expuesta en GitHub o en el código frontend de tu web). Debes entrar en tu panel, eliminar la clave afectada y <strong>generar una nueva</strong>.</li>
        </ol>

        <div style="text-align: center; margin-top: 35px; margin-bottom: 25px;">
            <a href="<?= site_url('login') ?>" style="background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block;">
                Ir a mi panel de control
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <p style="color: #94a3b8; font-size: 14px; text-align: center; margin: 0;">
            Este es un mensaje automático de seguridad. Por favor, no respondas a este correo. Si necesitas ayuda, escribe a soporte@apiempresas.es.<br>
            © <?= date('Y') ?> APIEmpresas. Todos los derechos reservados.
        </p>
    </div>
</body>
</html>
