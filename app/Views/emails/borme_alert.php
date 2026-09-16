<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimiento en el Registro Mercantil - APIEmpresas</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9;">

    <!-- Preheader: el texto que Gmail enseña junto al asunto. Sin esto el cliente
         coge la primera línea del cuerpo ("Hola, X:"), que no dice nada. El bloque
         de espacios que va detrás impide que arrastre texto de más. -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #f1f5f9;">
        {preheader}
    </div>
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #f1f5f9;">
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 25px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #2152FF 0%, #10B981 100%); padding: 28px 20px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">APIEmpresas.es</h1>
                            <p style="margin: 6px 0 0; color: rgba(255,255,255,0.92); font-size: 14px; font-weight: 500;">Vigilancia del Registro Mercantil</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <div style="display: inline-block; background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 14px; border: 1px solid #bfdbfe;">
                                🔔 Movimiento detectado
                            </div>

                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 22px; font-weight: 800;">Hola, {name}:</h2>

                            <p style="margin: 0 0 22px; font-size: 15px; color: #475569;">
                                {intro}
                            </p>

                            <!-- Listado de empresas con novedades -->
                            {companies_html}

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 26px 0 10px;">
                                <tr>
                                    <td align="center">
                                        <a href="{button_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 15px;">
                                            {button_text}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 18px 0 0; font-size: 13px; color: #94a3b8; text-align: center; line-height: 1.5;">
                                {footer_note}
                            </p>
                        </td>
                    </tr>

                    <!-- Disclaimer -->
                    <tr>
                        <td style="padding: 20px 35px 30px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                                Vigilamos las empresas que tú añades a tu lista en APIEmpresas y te avisamos
                                cuando aparece un acto nuevo a su nombre en el Boletín Oficial del Registro
                                Mercantil. La información procede de fuentes públicas oficiales.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
