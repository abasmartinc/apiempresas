<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>403 Forbidden</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f8fafc; color: #334155; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); max-width: 500px; text-align: center; }
        h1 { color: #ef4444; margin-top: 0; }
        p { line-height: 1.5; }
        .btn { display: inline-block; margin-top: 1.5rem; padding: 0.75rem 1.5rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Acceso Denegado (403)</h1>
        <p><?= esc($message ?? 'Tu IP ha sido bloqueada temporalmente por seguridad.') ?></p>
        <p>Si crees que esto es un error, por favor contacta con soporte.</p>
        <a href="/" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
