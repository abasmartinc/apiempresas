<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro de Usuario</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #334155; margin: 0; padding: 0; background-color: #f1f5f9; }
        .wrapper { padding: 40px 20px; }
        .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background-color: #0f172a; padding: 30px; text-align: center; color: #f8fafc; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 30px; }
        .content h2 { color: #1e293b; font-size: 18px; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
        .data-list { margin-bottom: 30px; }
        .data-item { margin-bottom: 12px; display: flex; border-bottom: 1px solid #f8fafc; padding-bottom: 10px; }
        .label { font-weight: 600; color: #64748b; width: 120px; flex-shrink: 0; }
        .value { color: #0f172a; font-weight: 500; }
        .badge { display: inline-block; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .cta-container { text-align: center; margin-top: 40px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 15px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Notificación de Sistema</h1>
            </div>
            <div class="content">
                <h2>Nuevo Registro de Usuario</h2>
                <p>Se ha completado un nuevo registro en la plataforma <strong>APIEmpresas.es</strong>. Aquí tienes los detalles:</p>
                
                <div class="data-list">
                    <div class="data-item">
                        <span class="label">Nombre:</span>
                        <span class="value"><?= esc($name) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="label">Empresa:</span>
                        <span class="value"><?= esc($company ?: 'No especificada') ?></span>
                    </div>
                    <div class="data-item">
                        <span class="label">Email:</span>
                        <span class="value"><?= esc($email) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="label">User ID:</span>
                        <span class="value"><span class="badge">#<?= esc($user_id) ?></span></span>
                    </div>
                    <div class="data-item">
                        <span class="label">Fecha:</span>
                        <span class="value"><?= date('Y-m-d H:i:s') ?></span>
                    </div>
                </div>

                <div class="cta-container">
                    <a href="<?= site_url('admin/users') ?>" class="btn">Gestionar en Panel Admin</a>
                </div>
            </div>
            <div class="footer">
                <p>Este es un mensaje automático generado por el sistema de APIEmpresas.es</p>
            </div>
        </div>
    </div>
</body>
</html>
