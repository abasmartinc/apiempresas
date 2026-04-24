<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 40px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 20px; }
        .footer { margin-top: 40px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #1e293b; margin: 0;">Nuevas empresas detectadas hoy ⚡</h1>
        </div>
        <p>Hola <?= esc($name) ?>,</p>
        <p>Ayer descargaste un listado de empresas, pero en las últimas 24 horas el mercado ha seguido moviéndose.</p>
        <p>Nuestro radar ha detectado <strong>nuevas constituciones</strong> que no estaban en tu archivo Excel. El 40% de las empresas cierran sus primeros acuerdos comerciales en su primera semana de vida. No esperes a que tu competencia las encuentre.</p>
        <div style="text-align: center;">
            <a href="<?= site_url('radar?source=email_day1') ?>" class="btn">Ver nuevas oportunidades ahora</a>
        </div>
        <p style="margin-top: 30px;">Si llegas el primero, tienes la mitad de la venta hecha.</p>
        <div class="footer">
            &copy; <?= date('Y') ?> APIEmpresas.es - Inteligencia de Mercado en Tiempo Real
        </div>
    </div>
</body>
</html>
