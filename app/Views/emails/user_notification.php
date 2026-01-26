<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject) ?></title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #334155; margin: 0; padding: 0; background-color: #f1f5f9; }
        .wrapper { padding: 40px 20px; }
        .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background-color: #0f172a; padding: 30px; text-align: center; color: #f8fafc; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 30px; }
        .content h2 { color: #1e293b; font-size: 18px; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
        .message-content { color: #475569; font-size: 16px; line-height: 1.6; }
        .cta-container { text-align: center; margin-top: 40px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 15px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Notificación de APIEmpresas.es</h1>
            </div>
            <div class="content">
                <h2><?= esc($subject) ?></h2>
                
                <div class="message-content">
                    <?= $content ?>
                </div>

                <div class="cta-container">
                    <a href="<?= site_url('enter') ?>" class="btn">Ir a mi Panel</a>
                </div>
            </div>
            <div class="footer">
                <p>Este mensaje fue enviado a <?= esc($user->email) ?>.</p>
                <p>&copy; <?= date('Y') ?> APIEmpresas.es</p>
            </div>
        </div>
    </div>
</body>
</html>
