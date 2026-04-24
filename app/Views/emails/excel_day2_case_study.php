<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 40px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 20px; }
        .footer { margin-top: 40px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #1e293b;">Cómo otros están consiguiendo clientes 💎</h2>
        <p>Hola <?= esc($name) ?>,</p>
        <p>Muchos de nuestros usuarios empezaron igual que tú: descargando un listado puntual.</p>
        <p>Sin embargo, los que más convierten son los que han integrado el <strong>Radar PRO</strong> en su rutina diaria. En lugar de hacer "puerta fría" masiva, contactan solo con las 5-10 empresas que se han creado esa mañana.</p>
        <p><em>"Desde que usamos el Radar, nuestro ratio de apertura de llamadas ha subido un 30% porque somos los primeros en llamar cuando el cliente tiene la necesidad real."</em></p>
        <div style="text-align: center;">
            <a href="<?= site_url('radar?source=email_day2') ?>" class="btn">Probar el Radar PRO</a>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> APIEmpresas.es
        </div>
    </div>
</body>
</html>
