<?php
try {
    $sendEmail = true;
    $errorMsg = 'No se pudo obtener el detalle del error en la vista.';
    
    if (isset($exception) && $exception instanceof \Throwable) {
        $errorMsg = "Mensaje: " . $exception->getMessage() . "\n" . 
                    "Archivo: " . $exception->getFile() . "\n" .
                    "Línea: " . $exception->getLine();
                    
        // No enviar correos por errores 400 (Bad Request) o 404 (Not Found)
        if ($exception instanceof \CodeIgniter\HTTP\Exceptions\BadRequestException ||
            $exception instanceof \CodeIgniter\Exceptions\PageNotFoundException) {
            $sendEmail = false;
        }
    }
    
    if ($sendEmail) {
        $email = \Config\Services::email();
        $email->setTo('soporte@apiempresas.es');
        $email->setSubject('🚨 Alerta Crítica: Error 500 en Producción');
        
        $message = "Se ha producido un error 500 en la plataforma.\n\n";
        $message .= "URL: " . current_url() . "\n";
        $message .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $message .= "IP Cliente: " . \Config\Services::request()->getIPAddress() . "\n";
        $message .= "User Agent: " . \Config\Services::request()->getUserAgent()->getAgentString() . "\n\n";
        $message .= "--- DETALLES DEL ERROR ---\n" . $errorMsg . "\n";
        
        $email->setMessage($message);
        $email->send();
    }
} catch (\Throwable $e) {
    // Ignorar si el envío de correo falla para evitar un bucle de errores
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Página no disponible - API Empresas</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }
        body { 
            margin: 0; 
            padding: 0; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #0f172a; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            position: relative;
            overflow: hidden;
        }
        /* Decoraciones de fondo premium */
        .bg-shape-1 {
            position: absolute; width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(33, 82, 255, 0.04) 0%, rgba(33, 82, 255, 0) 70%);
            top: -200px; left: -200px; z-index: 0;
        }
        .bg-shape-2 {
            position: absolute; width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, rgba(16, 185, 129, 0) 70%);
            bottom: -150px; right: -150px; z-index: 0;
        }
        
        .error-container { 
            position: relative;
            z-index: 10;
            max-width: 520px; 
            width: 100%;
            text-align: center; 
            background: #ffffff; 
            padding: 64px 48px; 
            border-radius: 32px; 
            box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.8); 
            margin: 20px;
        }
        
        .icon-wrapper {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-bg {
            position: absolute;
            inset: 0;
            background: #eff6ff;
            border-radius: 28px;
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }
        
        .icon-inner {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            background: #2152ff;
            color: #ffffff;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px -6px rgba(33, 82, 255, 0.3), inset 0 2px 4px rgba(255, 255, 255, 0.2);
            transform: rotate(4deg);
        }
        
        .icon-inner svg { width: 44px; height: 44px; }
        
        h1 { 
            font-size: 2rem; 
            font-weight: 900; 
            margin: 0 0 16px; 
            color: #0f172a; 
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        
        p { 
            font-size: 1.05rem; 
            color: #64748b; 
            margin: 0 0 40px; 
            line-height: 1.65; 
            font-weight: 500;
        }
        
        .actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }
        
        .btn {
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px; 
            text-decoration: none; 
            font-weight: 700; 
            padding: 16px 32px; 
            border-radius: 16px; 
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #2152ff; 
            color: #ffffff; 
            box-shadow: 0 4px 12px rgba(33, 82, 255, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.1); 
        }
        
        .btn-primary:hover { 
            background: #1b44d3; 
            transform: translateY(-2px); 
            box-shadow: 0 12px 24px -6px rgba(33, 82, 255, 0.35); 
        }
        
        .btn-secondary {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .support-text {
            margin-top: 32px;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .support-text a {
            color: #2152ff;
            text-decoration: none;
            font-weight: 600;
        }
        .support-text a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            .error-container { padding: 48px 32px; }
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="bg-shape-1"></div>
    <div class="bg-shape-2"></div>
    
    <div class="error-container">
        
        <div class="icon-wrapper">
            <div class="icon-bg"></div>
            <div class="icon-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
        </div>
        
        <h1>Estamos realizando tareas de mantenimiento</h1>
        
        <p>Nuestro sistema ha encontrado un problema temporal. No te preocupes, el equipo de ingeniería <strong>ya ha sido alertado automáticamente</strong> y estamos trabajando para restablecer el servicio.</p>
        
        <div class="actions">
            <a href="/" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Volver al inicio
            </a>
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Reintentar
            </button>
        </div>
        
        <div class="support-text">
            Si el problema persiste, contacta con nuestro <a href="/contacto">Soporte Técnico</a>
        </div>
    </div>
</body>
</html>
