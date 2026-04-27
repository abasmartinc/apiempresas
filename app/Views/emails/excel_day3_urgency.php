<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 40px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fffaf0; }
        .btn { display: inline-block; background: #ef4444; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 20px; }
        .footer { margin-top: 40px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #991b1b;">Estás perdiendo oportunidades ⚠️</h2>
        <p>Hola <?= esc($name) ?>,</p>
        <p>Te escribo porque he visto que aún no has activado tu acceso al Radar PRO tras tu compra del listado.</p>
        <p>Cada hora que pasa, nuevas empresas en tu sector están siendo contactadas por competidores que ya usan nuestra tecnología. El listado que compraste ya tiene 3 días... y en el mundo B2B, 3 días es mucho tiempo.</p>
        <p><strong>No dejes escapar las oportunidades.</strong> Activa el Radar ahora y recupera el control de tu prospección.</p>
        <div style="text-align: center;">
            <a href="<?= site_url('radar?source=email_day3') ?>" class="btn">Activar Radar PRO ahora</a>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> APIEmpresas.es
        </div>
    </div>
</body>
</html>
