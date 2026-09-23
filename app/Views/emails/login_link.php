<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu enlace para entrar en APIEmpresas</title>
</head>
<!-- Plantilla con marcadores literales ({login_url}, {minutos}): la siembra
     `php spark db:seed_emails login_link` la copia tal cual a email_templates. -->
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 25px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #2152FF 0%, #10B981 100%); padding: 24px 20px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">APIEmpresas.es</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 35px 35px 20px;">
                            <h2 style="margin: 0 0 14px; color: #0f172a; font-size: 21px; font-weight: 800;">Tu enlace para entrar</h2>
                            <p style="margin: 0 0 22px; color: #475569; font-size: 15px;">
                                Alguien (esperamos que tú) ha pedido entrar en tu cuenta de APIEmpresas con este correo.
                                Pulsa el botón y vuelves justo a donde estabas, sin contraseña.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 8px 0 22px;">
                                <tr>
                                    <td align="center">
                                        <a href="{login_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 15px 34px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px;">
                                            Entrar en mi cuenta
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 10px; color: #64748b; font-size: 13px;">
                                El enlace caduca en {minutos} minutos y solo se puede usar una vez.
                            </p>
                            <p style="margin: 0; color: #64748b; font-size: 13px;">
                                Si no has sido tú, ignora este correo: nadie puede entrar en tu cuenta sin él.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 35px 28px;">
                            <p style="margin: 0; padding-top: 18px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5;">
                                ¿El botón no funciona? Copia esta dirección en tu navegador:<br>
                                <span style="word-break: break-all; color: #64748b;">{login_url}</span><br><br>
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
