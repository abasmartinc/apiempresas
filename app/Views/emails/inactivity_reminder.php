<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Algo te ha frenado con la API?</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #f1f5f9; padding: 30px 20px; text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #0f172a; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; }
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
            
            <p>Hace unos días creaste tu cuenta y tu API Key todavía no ha hecho ninguna llamada.</p>

            <p>Si algo te ha frenado (la autenticación, el formato del CIF o el lenguaje de tu proyecto), <strong>responde a este correo y cuéntanos qué estás construyendo</strong>: te mandamos el código listo para tu caso.</p>

            <p>Y si solo te faltaba tiempo, esta es la llamada completa. El CIF es real, así que te devolverá datos de verdad:</p>

            <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px; margin-bottom: 10px; font-family: monospace;"># Ejemplo de petición rápida (curl)</p>
                <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                    curl -X GET "https://apiempresas.es/api/v1/companies?cif=A15075062" \<br>
                    &nbsp;&nbsp;-H "X-API-KEY: TU_CLAVE_API"
                </code>
            </div>

            <p>Recuerda que con tu <strong>Plan Free</strong> recibes la estructura completa pero con algunos metadatos enmascarados:</p>

            <pre style="background: #f1f5f9; color: #334155; padding: 15px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 12px; border: 1px solid #e2e8f0;">{
  "success": true,
  "data": {
    "name": "INDUSTRIA DE DISENO TEXTIL SA",
    "cif": "A15075062",
    "status": "ACTIVA",
    "founded": "1985-06-12",
    "province": "A CORUÑA",
    "cnae": "4642",
    "address": "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]",
    "corporate_purpose": "COMERCIO AL POR MAYOR Y MENOR DE TODA CLASE DE PRENDAS DE VESTIR."
  }
}</pre>

            <div class="cta-container">
                <a href="<?= site_url('dashboard') ?>" class="btn">Ver mi API Key</a>
            </div>

        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Datos oficiales para desarrolladores.</p>
        </div>
    </div>
</body>
</html>
