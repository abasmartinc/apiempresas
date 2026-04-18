<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estás cerca del límite</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #b91c1c; padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .warning-box { background: #fef2f2; border: 1px solid #fee2e2; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #ef4444; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #ef4444; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 16px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Atención: Límite de consumo</h1>
        </div>
        <div class="content">
            <h2>Hola, <?= esc($name) ?>.</h2>
            <p>Te informamos de que has superado el <strong><?= esc($percent) ?>%</strong> de tu cuota mensual de consultas en APIEmpresas.</p>
            
            <div class="warning-box">
                <strong>¿Qué significa esto?</strong>
                <p style="margin: 10px 0 0; font-size: 14px;">Si tu consumo continúa al ritmo actual, es muy probable que tu integración se detenga al alcanzar el 100%. Esto podría afectar al funcionamiento de tu aplicación o servicio.</p>
            </div>

            <p>Para garantizar la continuidad de tu servicio y eliminar estas limitaciones, te recomendamos subir al plan Pro de inmediato.</p>

            <div class="cta-container">
                <a href="<?= site_url('billing') ?>" class="btn">Actualizar a Plan PRO</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                Evita interrupciones inesperadas. El cambio a Pro es instantáneo.
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Servicio de datos mercantiles de alta disponibilidad.</p>
        </div>
    </div>
</body>
</html>
