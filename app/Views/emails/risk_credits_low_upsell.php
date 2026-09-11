<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus créditos de auditoría de solvencia - APIEmpresas</title>
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
                            <p style="margin: 6px 0 0; color: rgba(255,255,255,0.92); font-size: 14px; font-weight: 500;">Informes de Solvencia, Riesgo y Datos Mercantiles Oficiales</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <div style="display: inline-block; background: #fffbeb; color: #b45309; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 14px; border: 1px solid #fde68a;">
                                ⚠️ Saldo de Auditorías: {remaining_credits_text}
                            </div>

                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 22px; font-weight: 800;">Hola, {name}:</h2>
                            <p style="margin: 0 0 18px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Te informamos de que {credits_status_phrase} de tu pack de auditorías de solvencia mercantil.
                            </p>
                            <p style="margin: 0 0 22px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Si tienes previsto analizar más clientes o proveedores este mes para prevenir impagos y comprobar su scoring oficial, te interesará dar el salto a <strong>Solvencia Pro</strong>:
                            </p>

                            <!-- Featured Upgrade Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eff6ff; border: 2px solid #2563eb; border-radius: 14px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <span style="background: #2563eb; color: #ffffff; font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em;">RECOMENDADO</span>
                                        <h3 style="margin: 10px 0 4px; color: #0f172a; font-size: 18px; font-weight: 800;">Solvencia Pro (Tarifa Plana Ilimitada)</h3>
                                        <div style="font-size: 24px; font-weight: 900; color: #1d4ed8; margin-bottom: 12px;">29 € <span style="font-size: 13px; color: #64748b; font-weight: 600;">/ mes</span></div>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
                                            <tr>
                                                <td width="22" valign="top" style="color: #2563eb; font-weight: 800; font-size: 14px;">✓</td>
                                                <td style="color: #334155; font-size: 13.5px; line-height: 1.4; padding-bottom: 6px;">
                                                    <strong>Consultas y auditorías 100% ilimitadas</strong> de cualquier empresa de España.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="22" valign="top" style="color: #2563eb; font-weight: 800; font-size: 14px;">✓</td>
                                                <td style="color: #334155; font-size: 13.5px; line-height: 1.4; padding-bottom: 6px;">
                                                    <strong>Descarga de dictámenes oficiales en PDF</strong> con sello de solvencia.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="22" valign="top" style="color: #2563eb; font-weight: 800; font-size: 14px;">✓</td>
                                                <td style="color: #334155; font-size: 13.5px; line-height: 1.4; padding-bottom: 6px;">
                                                    <strong>Sin permanencia:</strong> Úsalo los meses que tengas operaciones y cancela en 1 clic cuando quieras.
                                                </td>
                                            </tr>
                                        </table>

                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center">
                                                    <a href="{button_url}" target="_blank" style="display: block; text-align: center; background: #2563eb; color: #ffffff !important; padding: 13px 24px; border-radius: 8px; text-decoration: none; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);">
                                                        ⚡ Pasar a Solvencia Pro (29 € / mes)
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternative Pack Reload -->
                            <p style="margin: 0 0 20px; color: #64748b; font-size: 13.5px; text-align: center;">
                                ¿Prefieres seguir con recargas puntuales sin suscripción mensual?<br>
                                <a href="{pack_url}" style="color: #0f766e; font-weight: 700; text-decoration: underline;">Recargar otro Pack de 5 Auditorías por 9,90 € + IVA</a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 35px 30px;">
                            <p style="margin: 0; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5;">
                                APIEmpresas.es te ayuda a prevenir impagos y auditar a tus clientes antes de conceder crédito comercial.<br>
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
