<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas de solvencia disponibles - APIEmpresas</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 25px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #2152FF 0%, #10B981 100%); padding: 28px 20px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">APIEmpresas.es</h1>
                            <p style="margin: 6px 0 0; color: rgba(255,255,255,0.92); font-size: 14px; font-weight: 500;">Informes de Solvencia y Riesgo Empresarial</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 22px; font-weight: 800;">Hola, {name}:</h2>
                            <p style="margin: 0 0 20px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Te recordamos que en tu cuenta aún dispones de <strong>{remaining_credits} consultas de solvencia y perfil de riesgo 100% gratuitas</strong> para aprovechar durante este mes.
                            </p>
                            
                            <!-- Value Proposition Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 14px; color: #0f172a; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Antes de firmar o conceder crédito comercial:</p>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">🛡️</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Semáforo de solvencia y scoring:</strong> Prevé impagos, concurso de acreedores o retrasos habituales.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">💶</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Límite de crédito comercial:</strong> Conoce el importe máximo seguro para aplazar pagos a ese cliente.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">📑</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Actos mercantiles recientes:</strong> Embargos, cambios de administradores y publicaciones en el BORME.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 14px 18px; border-radius: 6px; margin-bottom: 25px;">
                                <p style="margin: 0; color: #854d0e; font-size: 13.5px; line-height: 1.5;">
                                    ⏳ <strong>No acumulable:</strong> Los 3 créditos mensuales gratuitos no se acumulan para el mes siguiente. ¡Aprovéchalos antes del cierre de mes!
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 25px 0 15px;">
                                <tr>
                                    <td align="center">
                                        <a href="{button_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 15px 32px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                                            🔍 Auditar Empresa Ahora
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 35px 30px;">
                            <p style="margin: 0; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5;">
                                Si no deseas recibir avisos de tus créditos, puedes responder a este correo o gestionar tus preferencias.<br><br>
                                &copy; <?= date('Y') ?> APIEmpresas España.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
