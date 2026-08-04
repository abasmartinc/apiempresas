<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pago completado! Descargando tu informe...</title>
    <!-- Auto-download trigger -->
    <meta http-equiv="refresh" content="3;url=<?= site_url('empresa/download-premium-pdf?uuid=' . esc($order['uuid'])) ?>">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #0f172a;
        }
        .success-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .icon-check {
            width: 80px;
            height: 80px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
        }
        h1 { margin: 0 0 15px 0; font-size: 1.8rem; font-weight: 800; }
        p { color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px; }
        .btn-primary {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.2s;
            box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            display: inline-block;
            background: transparent;
            color: #64748b;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 15px;
            transition: color 0.2s;
        }
        .btn-secondary:hover {
            color: #0f172a;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(37,99,235,0.2);
            border-radius: 50%;
            border-top-color: #2563eb;
            animation: spin 1s ease-in-out infinite;
            vertical-align: middle;
            margin-right: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .edit-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #64748b;
        }
        .btn-edit {
            display: inline-block;
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #475569;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-edit:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15,23,42,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            text-align: left;
            position: relative;
        }
        .close-btn { position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 24px; color: #94a3b8; border: none; background: transparent; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        .form-group input[type="text"], .form-group input[type="color"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        .form-group input[type="color"] { height: 50px; padding: 5px; }
        .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="icon-check">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h1>¡Pago completado!</h1>
        <p>Tu informe premium Marca Blanca se está generando y <br><strong>la descarga comenzará automáticamente</strong> en unos segundos.</p>
        
        <div id="status-container" style="margin-bottom: 30px; color: #64748b; font-weight: 500;">
            <span class="spinner"></span> Generando PDF...
        </div>

        <script>
            // Después de 3.5 segundos (cuando salta el meta refresh de la descarga), actualizamos la interfaz
            setTimeout(function() {
                var container = document.getElementById('status-container');
                if(container) {
                    container.innerHTML = '<span style="color: #16a34a; font-weight: bold;">✓ ¡Descarga iniciada con éxito!</span><br><small style="font-weight: normal; font-size: 0.9em; margin-top: 5px; display: inline-block;">Revisa la carpeta de descargas de tu navegador.</small>';
                }
                var manualContainer = document.getElementById('manual-download-container');
                if(manualContainer) {
                    manualContainer.style.display = 'block';
                }
            }, 3500);
        </script>

        <div id="manual-download-container" style="display: none;">
            <a href="<?= site_url('empresa/download-premium-pdf?uuid=' . esc($order['uuid'])) ?>" class="btn-primary" download>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px; margin-top: -3px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Descargar manualmente
            </a>
        </div>
        <div>
            <a href="<?= site_url('empresa/' . $companyId) ?>" class="btn-secondary">Volver a la ficha de la empresa</a>
        </div>
        
        <div class="edit-info">
            ¿Te has equivocado de color o logo? Puedes guardar el enlace de esta página (te lo hemos enviado por email) para volver en cualquier momento y editar el diseño de tu PDF gratis.
            <br>
            <button class="btn-edit" onclick="document.getElementById('edit-modal').style.display='flex'">Editar Diseño del PDF</button>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="document.getElementById('edit-modal').style.display='none'">&times;</button>
            <h2 style="margin-top: 0; font-size: 1.5rem;">Editar Diseño</h2>
            <p style="font-size: 0.95rem; color: #64748b; margin-bottom: 25px;">Modifica los datos y regenera tu informe gratis.</p>
            <form action="<?= site_url('empresa/update-premium-pdf') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="uuid" value="<?= esc($order['uuid']) ?>">
                
                <div class="form-group">
                    <label>Nombre de la Agencia / Consultoría</label>
                    <input type="text" name="agency_name" value="<?= esc($order['agency_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Color Corporativo Principal</label>
                    <input type="color" name="brand_color" value="<?= esc($order['brand_color']) ?>">
                </div>

                <div class="form-group">
                    <label>Texto del Pie de Página (Opcional)</label>
                    <input type="text" name="footer_text" value="<?= esc($order['footer_text']) ?>" placeholder="Ej: Documento generado por MiAgencia SL">
                </div>

                <div class="form-group">
                    <label>Logo de tu Agencia (Opcional)</label>
                    <input type="file" name="logo" accept="image/png, image/jpeg">
                    <small style="display:block; color:#94a3b8; margin-top:5px;">Si subes uno nuevo, reemplazará al anterior.</small>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; text-align: center; border: none; cursor: pointer;">Guardar y Regenerar PDF</button>
            </form>
        </div>
    </div>
</body>
</html>
