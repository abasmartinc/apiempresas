<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject) ?></title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #133A82; padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2152ff; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>APIEmpresas.es</h1>
        </div>
        <div class="content">
            <p>Hola <strong><?= esc($user->name) ?></strong>,</p>
            
            <div style="margin: 20px 0; color: #1f2937;">
                <?= nl2br(esc($content)) ?>
            </div>

            <p style="margin-top: 30px;">
                Atentamente,<br>
                El equipo de APIEmpresas.es
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas.es. Todos los derechos reservados.</p>
            <p>Si no deseas recibir estos correos, contacta con soporte.</p>
        </div>
    </div>
</body>
</html>
