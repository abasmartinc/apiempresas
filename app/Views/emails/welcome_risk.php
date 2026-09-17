<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a APIEmpresas - Informes de Solvencia</title>
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
                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 22px; font-weight: 800;">¡Hola, {name}!</h2>

                            <!-- Empresa de origen. Se rellena con el CIF que traía el registro;
                                 si no hubiera, llega vacío y el correo sigue leyéndose bien. -->
                            {origin_line}
                            <p style="margin: 0 0 20px; color: #475569; font-size: 15px; line-height: 1.6;">
                                Tu cuenta ya está activa. Dispones de <strong>3 consultas de solvencia y perfil de riesgo 100% gratuitas cada mes</strong> para proteger tu negocio frente a impagos y auditar la salud financiera de cualquier cliente o proveedor en España.
                            </p>
                            
                            <!-- Value Proposition Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 14px; color: #0f172a; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">¿Qué incluye cada informe de riesgo?</p>
                                        
                                        <!-- Feature 1 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">🛡️</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Semáforo y Scoring de Quiebra:</strong> Probabilidad estadística de retrasos en pagos o concurso de acreedores.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Feature 2 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">💶</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Crédito Comercial Recomendado:</strong> Cuantía máxima sugerida para aplazar pagos con tranquilidad.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Feature 3 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">📑</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Historial BORME y Administradores:</strong> Nombramientos, ceses, capital social y vinculaciones societarias.
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Feature 4 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="26" valign="top" style="font-size: 16px; line-height: 1.2;">📊</td>
                                                <td style="padding-left: 8px; color: #334155; font-size: 14px; line-height: 1.5;">
                                                    <strong>Histórico del Registro Mercantil:</strong> nombramientos, ceses, cambios de domicilio, ampliaciones de capital y cualquier otro acto publicado en el BORME.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Highlight Box -->
                            <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 14px 18px; border-radius: 6px; margin-bottom: 25px;">
                                <p style="margin: 0; color: #1e40af; font-size: 13.5px; line-height: 1.5;">
                                    💡 <strong>Sin costes ocultos:</strong> Tus 3 consultas gratuitas se renuevan automáticamente cada mes sin que tengas que introducir tarjeta ni contratar planes.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 25px 0 15px;">
                                <tr>
                                    <td align="center">
                                        <a href="{button_url}" target="_blank" style="display: inline-block; background: #2563eb; color: #ffffff !important; padding: 15px 32px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                                            🔍 Buscar y Analizar Empresas
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
                                ¿Tienes alguna duda sobre cómo interpretar un informe? Responde directamente a este correo y te asesoramos.<br><br>
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
