<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu auditoría en APIEmpresas - Solvencia y Perfil de Riesgo</title>
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
                            <div style="display: inline-block; background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 14px; border: 1px solid #bfdbfe;">
                                🛡️ Seguimiento de Auditoría
                            </div>

                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 22px; font-weight: 800;">Hola, {name}:</h2>
                            <p style="margin: 0 0 18px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Vemos que recientemente consultaste el perfil de solvencia y riesgo de <strong>{company_name}</strong> en APIEmpresas.
                            </p>
                            <p style="margin: 0 0 20px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Conocer el scoring predictivo de impago, los actos mercantiles del BORME y la capacidad de crédito recomendada es el paso fundamental para <strong>proteger tu tesorería antes de conceder pagos aplazados o firmar contratos</strong>.
                            </p>
                            
                            <!-- Remaining Credits Callout -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0fdf4; border-radius: 12px; border: 1.5px solid #86efac; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                            <span style="font-size: 20px;">🎁</span>
                                            <strong style="color: #065f46; font-size: 15px;">Aún dispones de 2 consultas gratuitas este mes</strong>
                                        </div>
                                        <p style="margin: 0; color: #047857; font-size: 13.5px; line-height: 1.5;">
                                            Tus créditos mensuales no son acumulables para el mes siguiente. Te recomendamos aprovecharlos hoy mismo para auditar:
                                        </p>
                                        <ul style="margin: 10px 0 0; padding-left: 20px; color: #166534; font-size: 13.5px; line-height: 1.6;">
                                            <li><strong>Tu cliente con mayor riesgo de impago:</strong> verifica si tiene anotaciones de embargo o preconcurso.</li>
                                            <li><strong>Un nuevo proveedor clave:</strong> asegúrate de que no tenga problemas de suministro antes de pagar anticipos.</li>
                                            <li><strong>Tu propia sociedad o competencia directa:</strong> comprueba qué calificación económico-financiera tienes asignada.</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 25px 0 15px;">
                                <tr>
                                    <td align="center">
                                        <a href="{button_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 15px 32px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                                            🔍 Auditar a Otro Cliente Gratis
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0; color: #64748b; font-size: 13px; text-align: center;">
                                Solo necesitas introducir el CIF o razón social en tu panel para generar el informe oficial al instante.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 35px 30px;">
                            <p style="margin: 0; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5;">
                                Estás recibiendo este correo como parte del servicio de alertas de solvencia de APIEmpresas.<br>
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
