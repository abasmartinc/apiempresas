<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a APIEmpresas.es</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #2152FF 0%, #12B48A 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .features { background: #f1f5f9; padding: 20px; border-radius: 8px; margin: 25px 0; }
        .features ul { list-style: none; padding: 0; margin: 0; }
        .features li { margin-bottom: 10px; display: flex; align-items: flex-start; }
        .features li::before { content: '✓'; color: #12B48A; font-weight: bold; margin-right: 10px; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: background 0.2s; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #64748b; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>APIEmpresas.es</h1>
        </div>
        <div class="content">
            <h2>¡Hola, <?= esc($name) ?>!</h2>
            <p>Es un placer tenerte con nosotros. Te damos la bienvenida oficial a <strong>APIEmpresas.es</strong>, la plataforma líder en verificación mercantil en tiempo real.</p>
            
            <p>Tu cuenta ha sido creada correctamente. Ahora tienes acceso a herramientas potentes para automatizar tus procesos de validación empresarial.</p>

            <div class="features">
                <strong>¿Qué puedes hacer ahora?</strong>
                <ul>
                    <li>Consultar datos mercantiles por CIF/NIF.</li>
                    <li>Buscar empresas por razón social.</li>
                    <li>Acceder a documentación técnica completa.</li>
                    <li>Gestionar tus API Keys desde el panel.</li>
                </ul>
            </div>

            <p>Para empezar, te recomendamos explorar tu panel de control y revisar la documentación técnica para realizar tu primera integración.</p>

            <div class="cta-container">
                <a href="<?= site_url('enter') ?>" class="btn">Entrar al Dashboard</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                Si tienes alguna duda, nuestro equipo de soporte está aquí para ayudarte. Solo responde a este correo.
            </p>
        </div>
        <div class="footer">
            <p>Has recibido este correo porque te has registrado en <a href="https://apiempresas.es">APIEmpresas.es</a>.</p>
            <p>&copy; <?= date('Y') ?> APIEmpresas - Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
