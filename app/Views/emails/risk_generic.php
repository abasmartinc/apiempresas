<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{asunto}</title>
</head>
<!-- Plantilla común de los correos de Solvencia que no tienen diseño propio
     (lista de vigilancia llena, resumen mensual, pago sin terminar, consultas
     renovadas...). El asunto también es una variable: {asunto}.
     Marcadores literales: `php spark db:seed_emails risk_generic` la copia tal cual. -->
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9;">
    <div style="display: none; max-height: 0; overflow: hidden;">{preheader}</div>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 25px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #2152FF 0%, #10B981 100%); padding: 24px 20px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">APIEmpresas.es</h1>
                            <p style="margin: 6px 0 0; color: rgba(255,255,255,0.92); font-size: 14px; font-weight: 500;">Solvencia y vigilancia del Registro Mercantil</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px 35px 10px;">
                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 20px; font-weight: 800;">Hola, {name}:</h2>
                            <div style="color: #475569; font-size: 15px; line-height: 1.6;">
                                {content}
                            </div>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 24px 0 10px;">
                                <tr>
                                    <td align="center">
                                        <a href="{button_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px;">
                                            {button_text}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 35px 28px;">
                            <p style="margin: 0; padding-top: 18px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5;">
                                ¿Alguna duda? Responde a este correo y te contestamos.<br>
                                &copy; APIEmpresas España.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
