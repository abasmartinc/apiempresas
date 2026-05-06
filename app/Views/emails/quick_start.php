<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configura tu integración con APIEmpresas en 1 minuto 🚀</title>
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
            <p>Sabemos que registrarse es solo el primer paso. El momento clave es cuando recibes tu primer objeto de datos real en tu propio sistema.</p>
            
            <p>Para facilitarte el trabajo, aquí tienes un ejemplo de la respuesta que obtendrás al validar un CIF con tu <strong>Plan Free</strong>:</p>

            <pre style="background: #0f172a; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.5; overflow-x: auto;">{
  "success": true,
  "data": {
    "name": "TECH FLOW SOLUTIONS SL",
    "cif": "B12345678",
    "status": "ACTIVA",
    "founded": "2024-03-12",
    "province": "MADRID",
    "cnae": "6201",
    "address": "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]",
    "corporate_purpose": "La prestacion de servicios de consultoria informatica..."
  }
}</pre>

            <p style="margin-top: 25px;">Solo tienes que lanzar una petición <code>GET</code> a nuestro endpoint e incluir tu <strong>X-API-KEY</strong> en las cabeceras.</p>

            <div class="cta-container">
                <a href="<?= site_url('dashboard') ?>" class="btn">🛠️ Ver Documentación y Claves</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                <strong>Nota:</strong> El Plan Free enmascara automáticamente algunos campos. Una vez confirmes que la integración funciona, puedes activar el Plan Pro para desbloquear todos los metadatos sin cambiar ni una sola línea de tu código.
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Datos oficiales para desarrolladores.</p>
        </div>
    </div>
</body>
</html>
