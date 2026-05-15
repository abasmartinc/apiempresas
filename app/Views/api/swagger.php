<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>API Empresas - Documentación Oficial</title>

    <!-- Carga de assets globales (CSS y JS) del sitio -->
    <?= view('partials/head') ?>

    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { 
            margin:0; 
            background: #f4f8ff; 
        }

        /* --- Custom Look and Feel --- */
        .swagger-ui {
            font-family: 'Inter', sans-serif;
            margin-top: 0;
            padding-top: 0;
        }
        
        /* Ocultar el Topbar original de Swagger */
        .swagger-ui .topbar {
            display: none !important;
        }

        /* Títulos e Información (Ocultos) */
        .swagger-ui .info {
            display: none !important;
        }

        /* Compactar sección de esquemas y servidor */
        .swagger-ui .schemes-server-container {
            padding: 10px 20px;
            background: transparent;
            box-shadow: none;
            border: none;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        /* Ocultar selector de servidores y cualquier título residual */
        .swagger-ui .servers, 
        .swagger-ui .servers-title,
        .swagger-ui .schemes-server-container > label {
            display: none !important;
        }

        /* Botón Authorize Compacto y Premium */
        .swagger-ui .btn.authorize {
            background-color: #2152ff;
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(33, 82, 255, 0.25);
            transition: all 0.3s ease;
        }
        .swagger-ui .btn.authorize:hover {
            background-color: #1a41cc;
            box-shadow: 0 6px 16px rgba(33, 82, 255, 0.35);
        }

        /* Grupos de Tags (Planes) */
        .swagger-ui .opblock-tag {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 6px solid #2152ff;
            border-radius: 12px;
            padding: 18px 24px;
            margin-bottom: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            color: #0f172a;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .swagger-ui .opblock-tag:hover {
            box-shadow: 0 12px 30px rgba(33, 82, 255, 0.1);
            border-color: #cbd5e1;
        }
        .swagger-ui .opblock-tag small {
            display: none !important;
        }

        /* Bloques de Operaciones (Endpoints) */
        .swagger-ui .opblock {
            border-radius: 12px;
            border: 1px solid transparent;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            margin-bottom: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .swagger-ui .opblock:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        /* Estilo para los métodos */
        .swagger-ui .opblock .opblock-summary {
            padding: 12px 16px;
        }
        .swagger-ui .opblock .opblock-summary-method {
            border-radius: 8px;
            font-weight: 700;
            text-shadow: none;
            padding: 8px 16px;
            min-width: 90px;
            text-align: center;
        }

        /* Colores por Método */
        .swagger-ui .opblock.opblock-get { background: #ffffff; border-color: #e0f2fe; }
        .swagger-ui .opblock.opblock-get .opblock-summary-method { background: #0ea5e9; }
        
        .swagger-ui .opblock.opblock-post { background: #ffffff; border-color: #dcfce7; }
        .swagger-ui .opblock.opblock-post .opblock-summary-method { background: #10b981; }

        .swagger-ui .opblock.opblock-delete { background: #ffffff; border-color: #fee2e2; }
        .swagger-ui .opblock.opblock-delete .opblock-summary-method { background: #ef4444; }
    </style>
</head>
<body>

<?= view('partials/header') ?>

<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"> </script>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"> </script>
<script>
window.onload = function() {
  const ui = SwaggerUIBundle({
    url: "<?= base_url('api/docs/openapi.json') ?>",
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    layout: "StandaloneLayout",
    tagsSorter: "alpha",
    validatorUrl: null,
    onComplete: function() {
        // Traducción inicial
        translateSwagger();
    }
  });

  window.ui = ui;

  // Función para traducir elementos de la interfaz
  function translateSwagger() {
      const dictionary = {
          "Authorize": "Autorizar",
          "Logout": "Cerrar sesión",
          "Cancel": "Cancelar",
          "Close": "Cerrar",
          "Available authorizations": "Autorizaciones disponibles",
          "Value:": "Valor:",
          "Please correct the following validation errors and try again.": "Por favor, corrige los errores de validación e inténtalo de nuevo.",
          "Required field is not provided": "Este campo es obligatorio.",
          "Servers": "Servidores",
          "Execute": "Ejecutar",
          "Clear": "Limpiar",
          "Try it out": "Probar",
          "Parameters": "Parámetros",
          "Responses": "Respuestas",
          "Code": "Código",
          "Description": "Descripción"
      };

      // Traducir nodos de texto
      const walk = document.createTreeWalker(document.getElementById('swagger-ui'), NodeFilter.SHOW_TEXT, null, false);
      let node;
      while (node = walk.nextNode()) {
          const text = node.nodeValue.trim();
          if (dictionary[text]) {
              node.nodeValue = dictionary[text];
          }
      }

      // Traducir botones y placeholders
      document.querySelectorAll('.btn.authorize span').forEach(el => {
          if (el.textContent === 'Authorize') el.textContent = 'Autorizar';
      });
  }

  // Observador para capturar cambios dinámicos (como errores de validación)
  const observer = new MutationObserver((mutations) => {
      translateSwagger();
  });

  observer.observe(document.getElementById('swagger-ui'), {
      childList: true,
      subtree: true
  });
}
</script>
</body>
</html>
